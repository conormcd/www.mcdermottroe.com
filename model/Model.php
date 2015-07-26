<?php

/**
 * Common functionality for all models.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class Model {
    /**
     * The URI used to access this model.
     */
    private $_uri;

    /**
     * The metadata (Open Graph, Twitter Cards, etc) for this model.
     */
    protected $_metadata;

    /**
     * A description of the data that this model represents, in order to better
     * provide metadata.
     *
     * @return string A description of the data that this model represents.
     */
    public abstract function description();

    /**
     * The value that should be used as an ETag for any page referencing this
     * model.
     *
     * @return string A string that can be used as an identifier of the content
     *                represented by this model.
     */
    public abstract function eTag();

    /**
     * Initialise this model with the basic metadata.
     *
     * @return void
     */
    public function __construct() {
        $this->_metadata = array();
        $this->_metadata['author'] = 'Conor McDermottroe';
        $this->_metadata['description'] = array($this, 'description');
        $this->_metadata['robots'] = 'index,follow';
        $this->_metadata['viewport'] = 'width=device-width';
        $this->_metadata['og:description'] = array($this, 'description');
        $this->_metadata['og:image'] = array($this, 'image');
        $this->_metadata['og:locale'] = 'en_GB';
        $this->_metadata['og:site_name'] = 'www.mcdermottroe.com';
        $this->_metadata['og:type'] = 'website';
        $this->_metadata['og:updated_time'] = array($this, 'updatedTime');
        $this->_metadata['og:url'] = array($this, 'uri');
        $this->_metadata['fb:profile_id'] = '635772221';
        $this->_metadata['twitter:card'] = 'summary';
        $this->_metadata['twitter:site'] = '@IRLConor';
        $this->_metadata['twitter:creator'] = '@IRLConor';
    }

    /**
     * The default image to use for metadata.
     *
     * @return string The URL of an image to use.
     */
    public function image() {
        return 'http://www.gravatar.com/avatar/' .
            md5('conor@mcdermottroe.com') .
            '.jpg?s=200';
    }

    /**
     * Generate the metadata for this object.
     *
     * @return array An array of arrays. Each of the inner arrays is an
     *               associative array and must contain a content key and
     *               either a name or a property key.
     */
    public function metadata() {
        $ret = array();
        foreach ($this->_metadata as $key => $value) {
            // Evaluate the lazy ones
            if (is_callable($value)) {
                if (is_array($value)) {
                    $value = call_user_func($value);
                } else {
                    $value = call_user_func($value, $this);
                }
            }

            if ($value !== null) {
                // Patch up the URL
                if ($key === 'og:url') {
                    $value = 'http://www.mcdermottroe.com' . $value;
                }

                if (preg_match('/^(?:article|fb|og|profile):/', $key)) {
                    $ret[] = array('property' => $key, 'content' => $value);
                } else {
                    $ret[] = array('name' => $key, 'content' => $value);
                }
            }
        }
        return $ret;
    }

    /**
     * A canonical link for the model in question, if it exists.
     *
     * @return string A canonical URL for the model.
     */
    public function canonicalLink() {
        $metadata = $this->metadata();
        foreach ($metadata as $item) {
            if (array_key_exists('property', $item)) {
                if ($item['property'] == 'og:url') {
                    return $item['content'];
                }
            }
        }
        return null;
    }

    /**
     * The last time this object was modified, in HTTP header form.
     *
     * @return string The value from timestamp, in HTTP header form.
     */
    public function lastModified() {
        $timestamp = $this->timestamp();
        return ($timestamp !== null) ? Time::http($timestamp) : null;
    }

    /**
     * The last time this object was modified, in ISO8601 format.
     *
     * @return string The ISO8601 formatted version of timestamp, or null if the
     *                time is not known.
     */
    public function updatedTime() {
        $timestamp = $this->timestamp();
        return ($timestamp !== null) ? Time::dateISO8601($timestamp) : null;
    }

    /**
     * The New Relic JavaScript monitoring code to be inserted in the footer.
     *
     * @return string The JavaScript to be inserted in the footer or an empty
     *                string if the New Relic extension is not loaded.
     */
    public function newRelicJSFooter() {
        return NewRelic::javaScriptFooter();
    }

    /**
     * The New Relic JavaScript monitoring code to be inserted in the header.
     *
     * @return string The JavaScript to be inserted in the header or an empty
     *                string if the New Relic extension is not loaded.
     */
    public function newRelicJSHeader() {
        return NewRelic::javaScriptHeader();
    }

    /**
     * The last time this object was modified, if known.
     *
     * @return int The UNIX epoch time for when this object was last changed or
     *             null if the time is not known.
     */
    public function timestamp() {
        return null;
    }

    /**
     * The maximum amount of time this model may be cached for.
     *
     * @return int The maximum number of seconds this model should be cached
     *             for. Only positive integers will be used as cache lifetimes,
     *             anything else will result in instantaneous expiration.
     */
    public function ttl() {
        return 0;
    }

    /**
     * Get/set the URI for this object.
     *
     * @param string $uri If provided, the new value for the model URI.
     *
     * @return string The current URI for this model.
     */
    public function uri($uri = null) {
        if ($uri !== null) {
            $this->_uri = $uri;
        }
        return $this->_uri;
    }
}

?>
