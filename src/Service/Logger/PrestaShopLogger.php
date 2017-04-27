<?php

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
