<?php

namespace App\System;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

trait Debugger
{
    protected static function register_debugger(): self
    {
        if (env('APP_DEBUG'))
        {
            $whoops = new Run;
            $whoops->pushHandler(new PrettyPageHandler);
            $whoops->register();
        }
        return new self();
    }

    public static function debug(callable $func): void
    {
        try {
            $func();
        } catch (Throwable $e) {
            $log = new Logger('local');
            $log->pushHandler(new StreamHandler(ROOT . '/resources/storage/logs/' . date('Y.m.d') . '.log', Logger::WARNING));
            $log->error('An error occurred: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);

            if (env('APP_DEBUG')) {
                $whoops = new Run;
                $handler = new PrettyPageHandler;
                $handler->setPageTitle("Oops! Something went wrong.");
                $whoops->pushHandler($handler);
                $whoops->handleException($e);
                exit();
            } else {
                showError(500);
            }
        }
    }
}