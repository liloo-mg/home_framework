<?php
require('vendor/autoload.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class Logger_Logger
{
    /**
     * @param string $name
     * @param string $directoryLog
     *
     * @return Logger
     * @throws Exception
     */
    public function returnLogger($name = 'main', $directoryLog = '') {
        if ($directoryLog === '') {
            $date = new DateTime();

            $directoryLog = $date->format('Y_m_d');
        }
        $directory = implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', 'log', ''));

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (!is_dir($directory.$directoryLog)) {
            mkdir($directory.$directoryLog, 0755, true);
        }

        $directory.= $directoryLog;

        $logger = new Logger($name);
// Now add some handlers
        $logger->pushHandler(new StreamHandler($directory . "/$name.log", Logger::DEBUG));
        $logger->pushHandler(new FirePHPHandler());

        return $logger;
    }
}