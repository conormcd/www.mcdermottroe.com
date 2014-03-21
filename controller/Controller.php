<?php

/**
 * A default controller which can be used for most GET requests. This can be
 * extended in order to provide more specific handling of requests. Simply
 * extend this class and then modify the routing in public/index.php in order
 * to direct requests to your new controller.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Controller {
    /** The first portion of the path part of the URL. */
    protected $action;

    /** Blog for /blog/*, etc. */
    protected $action_name;

    /** The model object to render. */
    protected $model;

    /** The Klein main object for this app. */
    protected $klein;

    /** The klein Request object for the current page request. */
    protected $request;

    /** The klein Response object for the current page request. */
    protected $response;

    /** The format of the output. */
    protected $output_format;

    /**
     * Initialise this controller.
     *
     * @param object $klein    The Klein main object.
     * @param object $request  The Request object from klein.
     * @param object $response The Response object from klein.
     */
    public function __construct($klein, $request, $response) {
        if (!($klein instanceof \Klein\Klein)) {
            throw new Exception("Bad Klein object provided.");
        }
        if (!($request instanceof \Klein\Request)) {
            throw new Exception("Bad request object provided.");
        }
        if (!($response instanceof \Klein\Response)) {
            throw new Exception("Bad response object provided.");
        }
        if (!($this->action || $request->action)) {
            throw new Exception("No action provided.");
        }

        if (!$this->action) {
            $this->action = $request->action;
        }
        $this->klein = $klein;
        $this->request = $request;
        $this->response = $response;
        $this->output_format = null;

        $this->action_name = '';
        foreach (explode('-', $this->action) as $part) {
            $this->action_name .= ucfirst($part);
        }

        $this->klein->onError(array($this, 'onError'));
    }

    /**
     * Handle a GET request.
     *
     * @return void
     */
    public function get() {
        $content = Mustache::render($this->view(), $this->model());
        $this->response->header('Content-Length', strlen($content));
        $this->response->body($content);
        return $this->response;
    }

    /**
     * Get the model for the current request.
     *
     * @return object An appropriate sub-class of Model if one exists, if not,
     *                the Response object from klein is used as the model.
     */
    protected function model() {
        if (!$this->model) {
            $this->model = $this->response;
        }
        return $this->model;
    }

    /**
     * Handle exceptions thrown anywhere (via klein's error handling).
     *
     * @param object $response  The response object to use for output.
     * @param string $msg       The message portion of the exception.
     * @param string $type      The type of the exception.
     * @param object $exception The exception originally thrown.
     *
     * @return void
     */
    public function onError($response, $msg, $type, $exception) {
        // Set the appropriate HTTP status code
        $code = $exception->getCode();
        if ($code >= 400 && $code < 600) {
            $response->code($exception->getCode());
        } else {
            $response->code(500);
        }

        // Now render the error
        $response->body(
            Mustache::render(
                'error',
                array(
                    'message' => $msg,
                    'type' => $type,
                    'trace' => $exception->getTraceAsString()
                )
            )
        );

        // Track the exception
        ExceptionTracker::getInstance()->captureException($exception);

        return $response;
    }

    /**
     * Fetch the main template for the action in question.
     *
     * @return string The name of the template to render.
     */
    protected function view() {
        if ($this->output_format) {
            return $this->action . '_' . $this->output_format;
        } else {
            return $this->action;
        }
    }
}

?>
