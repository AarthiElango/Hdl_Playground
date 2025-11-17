<?php
namespace App\Helpers;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger as Monologger;

class Logger
{

    private $channel;
    private $logBase;

    public function __construct($channel = '')
    {
        $this->channel = $channel ?: 'app';
        $this->logBase = ABSPATH . '/logger';
    }

    private function getLogger(Level $level, string $type): Monologger
    {
        $logger = new Monologger($this->channel);

        // Ensure directory exists
        $dir = $this->logBase . '/' . $type . '/' . date('Y-m-d');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $file    = $dir . '/' . date('H') . '.log';
        $handler = new StreamHandler($file, $level);

        // Custom date + line format
        $dateFormat = "d-m-Y H:i:s";
        $output     = "[%datetime%] %channel%.%level_name%: %message% %context%\n";
        $formatter  = new LineFormatter($output, $dateFormat, true, true);

        $handler->setFormatter($formatter);

        $logger->pushHandler($handler);
        $logger->pushHandler(new FirePHPHandler());

        return $logger;
    }

    public function access($message, array $context = []): void
    {

        $this->getLogger(Level::Info, 'access')->info($this->stringify($message), $context);
    }

    public function error($message, array $context = []): void
    {
        $this->getLogger(Level::Error, 'error')->error($this->stringify($message), $context);
    }

    private function stringify($message): string
    {
        if ($message instanceof \Stringable) {
            return (string) $message;
        }
        if (is_array($message) || is_object($message)) {
            return json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        return (string) $message;
    }
}
