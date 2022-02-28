<?php

namespace App;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class UtmSourcesLogger
{
    private $logsFolderPath = "/var/log/utm_sources/";

    private $logsFileName = "utm_sources.log";

    private $fullLogsFilePath;
    
    private $filesystem;

    function __construct() {
        $this->filesystem = new Filesystem();
        $this->fullLogsFilePath = $this->logsFolderPath . '/' . $this->logsFileName;
    }

    public function log() {
        if ($_ENV['UTM_SOURCES_LOGS_IS_ACTIVE'] && $_ENV['LOGS_MAX_ROWS_COUNT']) {
            if (!$this->filesystem->exists($this->fullLogsFilePath)) {
                $this->filesystem->dumpFile($this->fullLogsFilePath, '');
            }
    
            if ( intval($this->fileRowsCount()) > intval($_ENV['LOGS_MAX_ROWS_COUNT'])) {
                $this->removeFirstRow();
            }

            if (($_SERVER['REQUEST_METHOD'] === 'GET' && !self::isExceptedUrl()) ||  self::isPostback()) {
                $this->filesystem->appendToFile($this->fullLogsFilePath, $this->makeLogRow());
            }
        }
    }

    public static function isExceptedUrl() {
        return stripos($_SERVER['REQUEST_URI'], 'preview') ||
               stripos($_SERVER['REQUEST_URI'], 'assets') ||
               stripos($_SERVER['REQUEST_URI'], 'ajax') ||
               stripos($_SERVER['REQUEST_URI'], 'admin') ||
               stripos($_SERVER['REQUEST_URI'], 'upload') ||
               stripos($_SERVER['REQUEST_URI'], '_wdt');
    }

    public static function isPostback() {
        return stripos($_SERVER['REQUEST_URI'], 'postback');
    }

    public function makeLogRow() {
        return date('Y-m-d H:i:s') . " : " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $this->isExceptedUrl() . "\n";
    }

    public function fileRowsCount() {
        return sizeof(file($this->fullLogsFilePath));
    }

    public function removeFirstRow() {
        $handle = fopen($this->fullLogsFilePath, "r");
        $first = fgets($handle,2048);
        $outfile= $this->logsFolderPath . "/temp";
        $o = fopen($outfile,"w");
        while (!feof($handle)) {
            $buffer = fgets($handle,2048);
            fwrite($o,$buffer);
        }
        fclose($handle);
        fclose($o);
        rename($outfile, $this->fullLogsFilePath );
    }
}