<?php

/**
 * The common functionality for all controllers.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class Controller {
    /**
     * The model object to render.
     */
    protected $model;

    /**
     * The main template to render.
     */
    protected $view;

    /**
     * The klein Request object for the current page request.
     */
    protected $request;

    /**
     * The klein Response object for the current page request.
     */
    protected $response;

    /**
     * The format of the output.
     */
    protected $output_format;

    /**
     * The Content-Type to send for this controller's output if none is
     * supplied by the model.
     */
    protected $content_type;

    /**
     * Initialise this controller.
     *
     * @param object $request  The Request object from klein.
     * @param object $response The Response object from klein.
     */
    public function __construct($request, $response) {
        if (!($request instanceof \Klein\Request)) {
            throw new Exception("Bad request object provided.");
        }
        if (!($response instanceof \Klein\Response)) {
            throw new Exception("Bad response object provided.");
        }

        $this->request = $request;
        $this->response = $response;
        $this->output_format = null;
        $this->content_type = 'text/html';
        $this->view = join(
            "_",
            array_map(
                "strtolower",
                array_filter(
                    preg_split("/(?=[A-Z])/", $this->name())
                )
            )
        );

        NewRelic::nameTransaction($this->newRelicTransaction());
    }

    /**
     * Handle a GET request.
     *
     * @return void
     */
    public function get() {
        $content = $this->content();
        $this->setHeaders(strlen($content));
        $this->response->body($content);
        return $this->response;
    }

    /**
     * Render the contents of the model.
     *
     * @return string The rendered view of the model.
     */
    protected function content() {
        $model = $this->model();
        $view = $this->view();

        $key = 'rendered_content_' . $view;
        if ($model) {
            $key .= $model->eTag();
        }

        return Cache::run(
            $key,
            86400 + rand(0, 3600),
            function () use ($view, $model) {
                return Mustache::render($view, $model);
            }
        );
    }

    /**
     * Get the model for the current request.
     *
     * @return object The model for the current request.
     */
    protected function model() {
        if ($this->model !== null) {
            $this->model->uri($this->request->uri());
        }
        return $this->model;
    }

    /**
     * Fetch the main template for the action in question.
     *
     * @return string The name of the template to render.
     */
    public function view() {
        if ($this->output_format) {
            return $this->view . '_' . $this->output_format;
        } else {
            return $this->view;
        }
    }

    /**
     * Get the name of this controller without the trailing "Controller".
     *
     * @return string The name of this controller.
     */
    protected function name() {
        return preg_replace('/Controller$/', '', get_class($this));
    }

    /**
     * Set all the headers that we can.
     *
     * @param int $content_length The number of bytes which will be output.
     *
     * @return void
     */
    protected function setHeaders($content_length = null) {
        $this->setCacheHeaders();
        if ($content_length !== null) {
            $this->response->header('Content-Length', $content_length);
        }

        $model = $this->model();

        // Content-Type
        if (method_exists($model, 'mimeType')) {
            $content_type = $model->mimeType();
            if ($content_type) {
                $this->content_type = $content_type;
            }
        }
        if ($this->content_type === 'text/html') {
            $this->content_type .= '; charset=utf-8';
        }
        $this->response->header('Content-Type', $this->content_type);

        // Other headers
        $headers = array(
            'ETag' => 'eTag',
            'Last-Modified' => 'lastModified',
        );
        foreach ($headers as $header => $method) {
            if (method_exists($model, $method)) {
                $value = $model->$method();
                if ($value) {
                    $this->response->header($header, $value);
                }
            }
        }
    }

    /**
     * Set the Cache-Control and Expires headers.
     *
     * @return void
     */
    protected function setCacheHeaders() {
        $headers = array();
        $cache_control = $this->cacheControl();

        // Make sure that max-age exists and is less than 1 year from now.
        if (!array_key_exists('max-age', $cache_control)) {
            $cache_control['max-age'] = 0;
        }
        $cache_control['max-age'] = min($cache_control['max-age'], 31536000);

        // Format the headers
        $headers['Expires'] = Time::http(time() + $cache_control['max-age']);
        $headers['Cache-Control'] = '';
        foreach ($cache_control as $k => $v) {
            $headers['Cache-Control'] .= ($k === 'max-age' ? " $k=$v" : " $k");
        }

        // Set the headers on the response object.
        foreach ($headers as $header => $value) {
            $this->response->header($header, trim($value));
        }
    }

    /**
     * Get the necessary values for setting the Cache-Control and Expires
     * headers.
     *
     * @return array An associative array where the keys are Cache-Control
     *               directives. Only the value for "max-age" will be included,
     *               all others will simply be present in the header if the key
     *               is persent in this array.
     */
    protected function cacheControl() {
        $max_age = 0;
        if (method_exists($this->model(), 'ttl')) {
            $max_age = $this->model()->ttl();
        }
        return array('public' => true, 'max-age' => $max_age);
    }

    /**
     * Name the New Relic transaction.
     *
     * @return string The name of the current transaction for New Relic.
     */
    protected function newRelicTransaction() {
        return $this->name();
    }
}

?>
