<?php

namespace ride\service;

use ride\library\log\Log;
use ride\library\minifier\Minifier;
use ride\library\system\file\browser\FileBrowser;

/**
 * Service to minify JS and CSS scripts
 */
class MinifierService {

    /**
     * Name of the log source
     * @var string
     */
    const LOG_SOURCE = 'minifier';

    /**
     * Instance of the log
     * @var \ride\library\log\Log
     */
    protected $log;

    /**
     * Instance of the file browser
     * @var \ride\library\system\file\browser\FileBrowser
     */
    protected $fileBrowser;

    /**
     * Instance of the minifier for JS resources
     * @var \ride\library\minifier\Minifier
     */
    protected $jsMinifier;

    /**
     * Instance of the minifier for CSS resources
     * @var \ride\library\minifier\Minifier
     */
    protected $cssMinifier;

    /**
     * Base URL for the resources
     * @var string
     */
    protected $baseUrl;

    /**
     * Flag to see if JS minifying is disabled
     * @var boolean
     */
    protected $isJsDisabled;

    /**
     * Flag to see if CSS minifying is disabled
     * @var boolean
     */
    protected $isCssDisabled;

    /**
     * Constructs a new minifier service
     * @param \ride\library\system\file\browser\FileBrowser $fileBrowser
     * @param \ride\library\minifier\Minifier $jsMinifier
     * @param \ride\library\minifier\Minifier $cssMinifier
     * @return null
     */
    public function __construct(FileBrowser $fileBrowser, Minifier $jsMinifier, Minifier $cssMinifier) {
        $this->fileBrowser = $fileBrowser;
        $this->jsMinifier = $jsMinifier;
        $this->cssMinifier = $cssMinifier;
    }

    /**
     * Sets the log to the service
     * @param \ride\library\log\Log $log
     * @return null
     */
    public function setLog(Log $log) {
        $this->log = $log;
    }

    /**
     * Sets the base URL for the minified scripts
     * @param string $baseUrl
     * @return null
     */
    public function setBaseUrl($baseUrl) {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Gets the base URL
     * @return string
     */
    public function getBaseUrl() {
        return $this->baseUrl;
    }

    /**
     * Sets whether the CSS minifying is disabled
     * @param boolean $isCssDisabled
     * @return null
     */
    public function setIsCssDisabled($isCssDisabled) {
        $this->isCssDisabled = $isCssDisabled;
    }

    /**
     * Gets whether the CSS minifying is disabled
     * @return boolean
     */
    public function isCssDisabled() {
        return $this->isCssDisabled;
    }

    /**
     * Sets whether the JS minifying is disabled
     * @param boolean $isJsDisabled
     * @return null
     */
    public function setIsJsDisabled($isJsDisabled) {
        $this->isJsDisabled = $isJsDisabled;
    }

    /**
     * Gets whether the JS minifying is disabled
     * @return boolean
     */
    public function isJsDisabled() {
        return $this->isJsDisabled;
    }

    /**
     * Minifies the provided CSS resources
     * @param array $resources
     * @return array
     */
    public function minifyCss(array $resources) {
        $baseUrl = rtrim($this->getBaseUrl(), '/') . '/';
        $result = array();

        if ($this->isCssDisabled()) {
            foreach ($resources as $resource) {
                if ($this->isUrl($resource)) {
                    $result[] = $resource;
                } else {
                    $result[] = $baseUrl . $resource;
                }
            }

            return $result;
        }

        if ($this->log) {
            $this->log->logDebug('Rendering minified style for:');
            foreach ($resources as $resource) {
                $this->log->logDebug('- ' . $resource);
            }
        }

        $toMinify = array();
        foreach ($resources as $resource) {
            if (!$this->isUrl($resource)) {
                $toMinify[] = $resource;

                continue;
            }

            $result[] = $resource;
            $result[] = $baseUrl . $this->minify($this->cssMinifier, $toMinify);

            $toMinify = array();
        }

        if ($toMinify) {
            $result[] = $baseUrl . $this->minify($this->cssMinifier, $toMinify);
        }

        return $result;
    }

    /**
     * Minifies the provided JS resources
     * @param array $resources
     * @return array
     */
    public function minifyJs(array $resources) {
        $baseUrl = rtrim($this->getBaseUrl(), '/') . '/';
        $result = array();

        if ($this->isJsDisabled()) {
            foreach ($resources as $resource) {
                if ($this->isUrl($resource) || strpos($resource, '<script') === 0) {
                    $result[] = $resource;
                } else {
                    $result[] = $baseUrl . $resource;
                }
            }

            return $result;
        }

        if ($this->log) {
            $this->log->logDebug('Rendering minified script for:');
            foreach ($resources as $resource) {
                $this->log->logDebug('- ' . $resource);
            }
        }

        $toMinify = array();
        foreach ($resources as $resource) {
            if (!($this->isUrl($resource) || strpos($resource, '<script') === 0)) {
                $toMinify[] = $resource;

                continue;
            }

            $result[] = $resource;
            $result[] = $baseUrl . $this->minify($this->jsMinifier, $toMinify);
            $toMinify = array();
        }

        if ($toMinify) {
            $result[] = $baseUrl . $this->minify($this->jsMinifier, $toMinify);
        }

        return $result;
    }

    /**
     * Minifies the provided resources
     * @param ride\library\minifier\Minifier $minifier
     * @param array $resources
     * @return string
     */
    protected function minify(Minifier $minifier, array $resources) {
        $minifiedScript = $minifier->minify($resources);
        $minifiedScript = $this->fileBrowser->getRelativeFile($minifiedScript, true);

        if ($this->log) {
            $this->log->logDebug('Rendered minified file ' . $minifiedScript);
        }

        return $minifiedScript;
    }

    /**
     * Checks if the provided resource is a URL
     * @param string $resource
     * @return boolean
     */
    protected function isUrl($resource) {
        return filter_var($resource, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
    }

}
