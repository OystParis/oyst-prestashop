<?php

namespace Oyst\Classes;

use Tools;

class CurrentRequest
{
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';

    /** @var string */
    private $scheme;

    /** @var string */
    private $host;

    /** @var string */
    private $requestUri;

    /** @var string */
    private $method;

    /** @var string */
    private $body;

    public function __construct()
    {
        $this->initializeScheme();
        $this->initializeHost();
        $this->initializeRequestUri();
        $this->initializeMethod();
        $this->initializeBody();
    }

    /**
     * @return $this
     */
    private function initializeScheme()
    {
        $this->scheme = 'http://';
        if (isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $this->scheme = 'https://';
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function initializeHost()
    {
        $this->host = $_SERVER['HTTP_HOST'];

        return $this;
    }

    /**
     * @return $this
     */
    private function initializeRequestUri()
    {
        $this->requestUri = $_SERVER['REQUEST_URI'];

        return $this;
    }

    /**
     * @return $this
     */
    public function initializeMethod()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];

        return $this;
    }

    public function initializeBody()
    {
        $this->body = Tools::file_get_contents('php://input');

        return $this;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        $headers = getallheaders();
        $contentType = 'text/html';

        // get the content type header
        foreach ($headers as $headerKey => $headerValue) {
            if (Tools::substr(Tools::strtolower($headerKey), 0, 13) == "content-type") {
                list($contentType) = explode(";", $headerValue);
            }
        }

        return $contentType;
    }

    /**
     * @return bool|string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return bool|mixed
     */
    public function getJson()
    {
        // AS OVH drop my content-type.. I can't check the header properly..
        /*$data = false;
        if ($this->getContentType() == 'application/json') {
            $data = json_decode($this->body, true);
        }*/
        $data = json_decode($this->body, true);

        return $data;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $_POST;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getRequestItem($key)
    {
        if ($this->hasRequest($key)) {
            $value = Tools::getValue($key);
        }

        return $value;
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $_GET;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getQueryData($key)
    {
        $value = false;
        if ($this->hasQuery($key)) {
            $value = Tools::getValue($key);
        }

        return $value;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasRequest($key)
    {
        return Tools::getIsset($key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasQuery($key)
    {
        return Tools::getIsset($key);
    }

    /**
     * @return mixed
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }
}

if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (Tools::substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(Tools::strtolower(str_replace('_', ' ', Tools::substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
