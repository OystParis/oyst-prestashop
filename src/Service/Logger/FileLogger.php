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

namespace Oyst\Service\Logger;

use DateTime;
use Exception;
use Guzzle\Common\Exception\InvalidArgumentException;
use Logger;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class FileLogger extends AbstractLogger
{
    private $file;

    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @throws Exception
     */
    public function log($level, $message, array $context = array())
    {
        $resource = fopen($this->file, 'a');
        if (!$resource) {
            throw new Exception('Log resource can\'t be allowed or file can\'t be created');
        }

        $date = (new DateTime())->format('Y-m-d H:i:s');
        $level = strtoupper($level);

        if (!empty($context)) {
            fwrite($resource, sprintf(
                "[%s][%s][Context] %s".PHP_EOL,
                $date,
                $level,
                json_encode($context)
            ));
        }

        fwrite($resource, sprintf(
            "[%s][%s][Message] %s".PHP_EOL,
            $date,
            $level,
            $message
        ));

        fclose($resource);
    }
}
