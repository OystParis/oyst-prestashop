<?php

namespace Oyst\Classes;

use DateTime;
use Exception;
use Psr\Log\AbstractLogger;
use Tools;

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
        $level = Tools::strtoupper($level);

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
