<?php

/**
 * Wrap exceptions so that they look like every other model.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ErrorModel
extends Model
{
    /**
     * Initialize.
     *
     * @param mixed $exception_or_message The Exception or a string message.
     * @param int   $code                 The HTTP status code which will only
     *                                    be used if the first argument was not
     *                                    an exception.
     */
    public function __construct($exception_or_message, $code = null) {
        parent::__construct();
        if ($exception_or_message instanceof Exception) {
            $this->_exception = $exception_or_message;
            $this->_message = $this->_exception->getMessage();
            $this->_code = $this->_exception->getCode();
        } else {
            $this->_exception = null;
            $this->_message = $exception_or_message;
            $this->_code = $code;
        }

        $this->_metadata['og:type'] = 'website';
        $this->_metadata['og:title'] = array($this, 'message');
    }

    /**
     * The integer which should be used as the HTTP status code.
     *
     * @return int The most appropriate HTTP status code for this error.
     */
    public function code() {
        if (!$this->_code) {
            return 500;
        }
        if ($this->_code < 400 || $this->_code >= 600) {
            return 500;
        }
        return $this->_code;
    }

    /**
     * The error message is the description for this.
     *
     * @return string The same value as message().
     */
    public function description() {
        return $this->message();
    }

    /**
     * The error message to display.
     *
     * @return string The error message to display.
     */
    public function message() {
        return $this->_message;
    }

    /**
     * Get the backtrace for the exception, if any.
     *
     * @return string The backtrace for the exception or an empty string if one
     *                is not available.
     */
    public function trace() {
        if ($this->_exception) {
            return $this->_exception->getTraceAsString();
        }
        return '';
    }

    /**
     * Generate an ETag for this model so that we can avoid unnecessarily
     * re-rendering it over and over again.
     *
     * @return string A unique fingerprint for this object.
     */
    public function eTag() {
        return md5($this->code() . $this->message() . $this->trace());
    }
}

?>
