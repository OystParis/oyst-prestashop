<?php

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
