<?php


namespace suffi\ErrorHandler;


use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Class ErrorHandler
 * @package suffi\ErrorHandler
 */
class ErrorHandler
{
    /**
     * psr-3 Logger
     * @var AbstractLogger
     */
    public $logger = null;

    /**
     * Debug flag. Show debug info
     * @var bool
     */
    public $debug = false;

    /**
     * Logging flag.
     * @var bool
     */
    public $writeLog = false;

    /**
     * Debugging information in the log recording flag
     * @var bool
     */
    public $debugLog = false;

    /**
     * Метод перехвата ошибок
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param array $errcontext
     */
    public function errorHandler(int $errno, string $errstr, string $errfile = '', int $errline = 0, array $errcontext = [])
    {
        $this->handler(new \ErrorException($errstr, 0, $errno, $errfile, $errline));
    }

    /**
     * Метод перехвата исключений
     * @param \Throwable $ex
     */
    public function exceptionHandler(\Throwable $ex)
    {
        $this->handler($ex);
    }

    /**
     * Обработка исключений
     * @param \Throwable $ex
     */
    protected function handler(\Throwable $ex)
    {
        if ($this->writeLog && $this->logger) {
            $this->log($ex);
        }

        if ($this->isError($ex->getCode())) {
            $this->page500($ex->getMessage());
        }

        if ($this->debug) {
            $this->debugError($ex);
        }

        if ($this->isError($ex->getCode())) {
            exit;
        }
    }

    /**
     * Определение по типу
     * @param int $errno
     * @return bool
     */
    protected function isError(int $errno):bool
    {
        switch ($errno) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_PARSE:
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            default:
                return true;
                break;

            case E_NOTICE:
            case E_USER_ERROR:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return false;
                break;
        }
    }

    /**
     * Определение типа сообщения в логе по типу ошибки
     * @param int $errno
     * @return bool
     */
    protected function getLogLevel(int $errno):bool
    {
        switch ($errno) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            default:
                return LogLevel::ERROR;
                break;

            case E_PARSE:
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                return LogLevel::WARNING;
                break;

            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return LogLevel::NOTICE;
                break;
        }
    }

    /**
     * Выбрасывает 500й статус и текст с ошибкой
     * @param string $errstr
     */
    protected function page500(string $errstr)
    {

        if (!$this->debug) {
            ob_clean();
        }
        if (!headers_sent()) {
            header('HTTP/1.1 500');
        }
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo $this->jsonError($errstr);
        } else {
            echo $this->htmlError($errstr);
        }

    }

    /**
     * Парсинг шаблона
     * @param \Throwable $ex
     * @param string $code
     * @return mixed
     */
    protected function parseDebug(\Throwable $ex, $code = '')
    {
        if ($code) {
            $code = '<?php ' . "\r\n" . $code;
        }

        ob_start();
        ob_implicit_flush(false);
        extract(['exception' => $ex, 'code' => $code], EXTR_OVERWRITE);
        require('errorTemplate.php');

        return ob_get_clean();

    }

    /**
     * Добавление глобальных переменных в строку
     * @param $fullMessage
     * @return string
     */
    protected function addGlobals($fullMessage)
    {
        $fullMessage .= "\r\n";
        $fullMessage .= "\r\n";

        $fullMessage .= '$_GET';
        $fullMessage .= "\r\n";
        $fullMessage .= print_r($_GET, true);
        $fullMessage .= "\r\n";

        $fullMessage .= '$_POST';
        $fullMessage .= "\r\n";
        $fullMessage .= print_r($_POST, true);
        $fullMessage .= "\r\n";

        if (\PHP_SESSION_ACTIVE == session_status()) {
            $fullMessage .= '$_SESSION';
            $fullMessage .= "\r\n";
            $fullMessage .= print_r($_SESSION, true);
            $fullMessage .= "\r\n";
        }

        $fullMessage .= '$_COOKIE';
        $fullMessage .= "\r\n";
        $fullMessage .= print_r($_COOKIE, true);
        $fullMessage .= "\r\n";

        $fullMessage .= '$_SERVER';
        $fullMessage .= "\r\n";
        $fullMessage .= print_r($_SERVER, true);
        $fullMessage .= "\r\n";

        $fullMessage = $this->addInDebugLog($fullMessage);

        return $fullMessage;
    }

    /**
     * Сообщение об ошибке в html
     * @param string $errstr
     * @return string
     */
    protected function htmlError(string $errstr)
    {
        return sprintf('<html>
                        <body>
                        <div style="text-align: center">
                        <h3>Ошибка!</h3>
                        <p>%s</p>
                        </div>
                        </body>
                    </html>', $errstr) ;
    }

    /**
     * Сообщение об ошибке в json
     * @param string $errstr
     * @return string
     */
    protected function jsonError(string $errstr)
    {
        return json_encode(['error' => $errstr]);
    }

    /**
     * Запись в логи об ошибке
     * @param \Throwable $ex
     */
    protected function log(\Throwable $ex)
    {
        $this->logger->log($this->getLogLevel($ex->getCode()), $ex->getMessage());
        if ($this->debugLog) {
            $fullMessage = $ex->getTraceAsString();

            $fullMessage = $this->addGlobals($fullMessage);
            $this->logger->log(LogLevel::DEBUG, $fullMessage);
        }

    }

    /**
     * Информация об ошибке
     * @param \Throwable $ex
     */
    protected function debugError(\Throwable $ex)
    {
        $file = $ex->getFile();
        $line = $ex->getLine();

        $code = '';
        try {
            if (file_exists($file) && is_readable($file)) {
                $strings = file($file);
                $strings = array_slice($strings, max(0, $line - 10), 20);
                if ($strings) {
                    $code = implode('', $strings);
                }
            }
        } catch (\Throwable $e) {

        }

        echo $this->parseDebug($ex, $code);
    }

    /**
     * @param $fullMessage
     * @return string
     */
    protected function addInDebugLog($fullMessage)
    {
        if (function_exists('pinba_get_info')) {
            $fullMessage .= 'Pinba';
            $fullMessage .= "\r\n";
            $fullMessage .= print_r(pinba_get_info(), true);
            $fullMessage .= "\r\n";
            return $fullMessage;
        }
        return $fullMessage;
    }

}