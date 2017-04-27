<?php

namespace Oyst\Service\Logger;

/**
 * Describes log levels
 */
class LogLevel
{
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';

    /**
     * @return array
     */
    static public function getList()
    {
        return [
            static::EMERGENCY,
            static::ALERT,
            static::CRITICAL,
            static::ERROR,
            static::WARNING,
            static::NOTICE,
            static::INFO,
            static::DEBUG,
        ];
    }
}
