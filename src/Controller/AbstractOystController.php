<?php

namespace Oyst\Controller;

use Oyst\Classes\FileLogger;

abstract class AbstractOystController
{
    /** @var  FileLogger */
    public $logger;

    public $logs_path;

    public function __construct()
    {
        $this->logger = new FileLogger();
        $this->logs_path = dirname(__FILE__).'/../../logs/';
        $this->setLogName('global');
    }

    /**
     * @param $name string
     */
    public function setLogName($name)
    {
        $this->logger->setFile($this->logs_path.$name.'.log');
    }

    /**
     * @param mixed $content
     * @param bool $already_json
     */
    protected function respondAsJson($content, $already_json = false, $define_header = true)
    {
        if ($define_header) {
            header("HTTP/1.1 200 OK");
            header('Content-Type: application/json');
        }
        if ($already_json) {
            echo $content;
        } else {
            echo json_encode($content);
        }
    }

    /**
     * @param $code
     * @param string $msg
     */
    public function respondError($code, $msg = '')
    {
        if (!function_exists('http_response_code')) {
            $text = '';
            switch ($code) {
                case 400:
                    $text = 'Bad Request';
                    break;
                case 401:
                    $text = 'Unauthorized';
                    break;
                case 403:
                    $text = 'Forbidden';
                    break;
                case 404:
                    $text = 'Not Found';
                    break;
                case 500:
                    $text = 'Internal Server Error';
                    break;
            }
            header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$text);
        } else {
            http_response_code($code);
        }
        header('Content-Type: application/json');
        die(json_encode(array('error' => $msg)));
    }
}
