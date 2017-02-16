<?php namespace Kitrix\Common;

use Bitrix\Main\Config\Configuration;
use Kitrix\Load;

final class Kitx
{
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

    /**
     * Log high priority boot errors to log
     * @param \Exception $e
     * @return bool
     */
    static function logBootError(\Exception $e) {

        $isDebugMode = false;
        $exceptionHandling = Configuration::getValue("exception_handling");
        if (is_array($exceptionHandling) && $exceptionHandling['debug']) {
            $isDebugMode = true;
        }

        $logFile = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "__KITRIX_BOOT_LOG_HALT.txt";

        if (!$isDebugMode) {
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
}