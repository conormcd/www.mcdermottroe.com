<?php

/*
 * Copyright (c) 2012, Conor McDermottroe
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

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

    /** The klein _Request object for the current page request. */
    protected $request;

    /** The klein _Response object for the current page request. */
    protected $response;

    /** The format of the output. */
    protected $output_format;

    /**
     * Initialise this controller.
     *
     * @param object $request  The _Request object from klein.
     * @param object $response The _Response object from klein.
     */
    public function __construct($request, $response) {
        if (!($request instanceof _Request)) {
            throw new Exception("Bad request object provided.");
        }
        if (!($response instanceof _Response)) {
            throw new Exception("Bad response object provided.");
        }
        if (!($this->action || $request->action)) {
            throw new Exception("No action provided.");
        }

        if (!$this->action) {
            $this->action = $request->action;
        }
        $this->request = $request;
        $this->response = $response;
        $this->output_format = null;

        $this->action_name = '';
        foreach (explode('-', $this->action) as $part) {
            $this->action_name .= ucfirst($part);
        }

        $this->response->onError(array($this, 'onError'));
    }

    /**
     * Handle a GET request.
     *
     * @return void
     */
    public function get() {
        $content = Mustache::render($this->view(), $this->model());
        $this->response->header('Content-Length', strlen($content));
        echo $content;
    }

    /**
     * Get the model for the current request.
     *
     * @return object An appropriate sub-class of Model if one exists, if not,
     *                the _Response object from klein is used as the model.
     */
    protected function model() {
        if (!$this->model) {
            $model_name = $this->action_name . 'Model';
            $model_file = dirname(__DIR__) . "/model/$model_name.php";
            if (file_exists($model_file)) {
                $this->model = new $model_name();
            } else {
                $this->model = $this->response;
            }
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
        switch ($exception->getCode()) {
            case 404:
                $response->header('HTTP/1.1 404 Not Found');
                break;
            default:
                $response->header('HTTP/1.1 500 Internal Server Error');
                break;
        }

        // Now render the error
        echo Mustache::render(
            'error',
            array('message' => $msg, 'type' => $type)
        );

        // Send the data to Sentry
        if (isset($_ENV['SENTRY'])) {
            $_ENV['SENTRY']->captureException($exception);
        }
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
