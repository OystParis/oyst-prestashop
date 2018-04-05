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
use Logger;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

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
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
                return 4;
            case LogLevel::ERROR:
                return 3;
            case LogLevel::WARNING:
            case LogLevel::NOTICE:
                return 2;
            case LogLevel::INFO:
            case LogLevel::DEBUG:
                return 1;
        }

        return 1;
    }
}
