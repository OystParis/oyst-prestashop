<?php
/**
 * 2013-2016 Froggy Commerce
 *
 * NOTICE OF LICENSE
 *
 * You should have received a licence with this module.
 * If you didn't download this module on Froggy-Commerce.com, ThemeForest.net,
 * Addons.PrestaShop.com, or Oyst.com, please contact us immediately : contact@froggy-commerce.com
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to benefit the updates
 * for newer PrestaShop versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Froggy Commerce <contact@froggy-commerce.com>
 * @copyright 2013-2016 Froggy Commerce / 23Prod / Oyst
 * @license   GNU GENERAL PUBLIC LICENSE
 */

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
