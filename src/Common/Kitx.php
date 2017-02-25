<?php
/******************************************************************************
 * Copyright (c) 2017. Kitrix Team                                            *
 * Kitrix is open source project, available under MIT license.                *
 *                                                                            *
 * @author: Konstantin Perov <fe3dback@yandex.ru>                             *
 * Documentation:                                                             *
 * @see https://kitrix-org.github.io/docs                                     *
 *                                                                            *
 *                                                                            *
 ******************************************************************************/

namespace Kitrix\Common;

use Bitrix\Main\Config\Configuration;

final class Kitx
{
    static private $debugMode = null;

    /**
     * Return formatted Exception to throw
     *
     * @param $msg
     * @param $params
     * @return string
     */
    static function frmt($msg, $params) {

        $msg = trim(preg_replace('/\s+/', ' ', $msg));
        $msg = vsprintf($msg, $params);

        return $msg;
    }

    /**
     * Print debug info into screen, and stop execution
     *
     * @param $data
     */
    static function pr($data) {
        ob_end_clean();
        die("<pre>".print_r($data, true)."</pre>");
    }

    static function fire($data) {

        $logData = print_r($data, true) . "\n\n";
        $logFile = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "__kitrix_debug_log.txt";

        if (!self::getDebugMode()) {
            return false;
        }

        file_put_contents($logFile, $logData, FILE_APPEND);
        return true;
    }

    /**
     * Log high priority boot errors to log
     * @param \Exception $e
     * @return bool
     */
    static function logBootError(\Exception $e) {

        $logFile = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "__KITRIX_BOOT_LOG_HALT.txt";

        if (!self::getDebugMode()) {
            return false;
        }

        $time = date(DATE_W3C);
        $stackTrace = $e->getTraceAsString();
        $message = chunk_split($e->getMessage(), 50, "\n");

        $msg = "
--------------------------------------------------
Kitrix Boot Error
at: {$time}
--------------------------------------------------
{$message}

-- End of message

Trace:
{$stackTrace}
//////////////////////////////////////////////////
";

        file_put_contents($logFile, $msg, FILE_APPEND);

        return true;
    }

    /**
     * Check we are in debug mode?
     * @return bool
     */
    static function getDebugMode()
    {
        if (self::$debugMode !== null) {
            return self::$debugMode;
        }

        self::$debugMode = false;
        $exceptionHandling = Configuration::getValue("exception_handling");
        if (is_array($exceptionHandling) && $exceptionHandling['debug']) {
            self::$debugMode = true;
        }

        return self::$debugMode;
    }
}