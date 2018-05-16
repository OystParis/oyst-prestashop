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

namespace Oyst\Classes;

use Tools;

class LoggerManager
{
    /** @var string  */
    private $directory;

    public function __construct()
    {
        $this->directory = dirname(__FILE__).'/../../../logs';
    }

    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        $files = glob($this->directory.'/*.log');

        return $files;
    }

    /**
     * @param string $file
     * @return bool|string
     */
    public function getContent($file)
    {
        $path = $this->directory.'/'.$file;
        $content = 'File doesn\'t exist';
        if (file_exists($path)) {
            $content = Tools::file_get_contents($path);
        }

        return $content;
    }

    public function deleteAll()
    {
        $logs = $this->getFiles();

        foreach ($logs as $log) {
            unlink($log);
        }
    }
}
