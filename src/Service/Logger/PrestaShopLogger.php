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
use Guzzle\Common\Exception\InvalidArgumentException;
use Logger;

class PrestaShopLogger extends AbstractLogger
{
    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function log($level, $message, array $context = array())
    {
        if (!in_array($level, LogLevel::getList())) {
            throw new InvalidArgumentException('The log level you use does not exist');
        }

        $log = new Logger();
        $log->message = $message;
        $log->severity = $this->getSeverity($level);
        $log->date_add = (new DateTime())->format('Y-m-d H:i:s');
        $log->date_upd = $log->date_add;

        $log->error_code = isset($context['errorCode']) ? $context['errorCode'] : false;
        $log->object_id = isset($context['objectId']) ? $context['objectId'] : false;
        $log->object_type = isset($context['objectType']) ? $context['objectType'] : false;

        $state = $log->add();
        return $state;
    }

    /**
     * @param $level
     * @return int
     */
    private function getSeverity($level)
    {
        switch ($level) {
            case LogLevel::EMERGENCY:
                return 4;
                break;
            case LogLevel::ALERT:
                return 4;
                break;
            case LogLevel::CRITICAL:
                return 4;
                break;
            case LogLevel::ERROR:
                return 3;
                break;
            case LogLevel::WARNING:
                return 2;
                break;
            case LogLevel::NOTICE:
                return 2;
                break;
            case LogLevel::INFO:
                return 1;
                break;
            case LogLevel::DEBUG:
                return 1;
                break;
        }

        return 1;
    }
}
