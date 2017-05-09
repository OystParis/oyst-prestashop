<?php

namespace Oyst\Service\Http;

class CurrentRequest
{
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';

    /**
     * @return string
     */
    public function getContentType()
    {
        $headers = getallheaders();
        $contentType = 'text/html';

        // get the content type header
        foreach($headers as $headerKey => $headerValue) {
            if (substr(strtolower($headerKey),0,13) == "content-type") {
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

        return file_get_contents('php://input');
    }

    /**
     * @return bool|mixed
     */
    public function getJson()
    {
        $data = false;
        if ($this->getContentType() == 'application/json') {
            $data = json_decode(file_get_contents('php://input'), true);
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
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
            $value = $_POST[$key];
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
        if ($this->hasQuery($key)) {
            $value = $_POST[$key];
        }

        return $value;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasRequest($key)
    {
        return isset($_POST[$key]);
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasQuery($key)
    {
        return isset($_GET[$key]);
    }
}
