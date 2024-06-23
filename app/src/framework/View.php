<?php

namespace App;

use eftec\bladeone\BladeOne;

class View
{
    private static BladeOne $blade;
    private static string $views;
    private static string $cache;
    private static string $newViews = '';

    public static function init($views, $cache): View
    {
        self::$views = $views;
        self::$cache = $cache;
        return new self();
    }

    private static function blade(): void
    {
        if (self::$newViews !== '')
        {
            self::$views = self::$newViews;
            self::$newViews = '';
        }

        if (env('APP_DEBUG'))
            $mode = BladeOne::MODE_SLOW;
        else
            $mode = BladeOne::MODE_SLOW;

        $blade = new BladeOne(self::$views, self::$cache, $mode);
        $blade->setCompiledExtension('.php');
        $blade->setOptimize(false);
        self::$blade = $blade;
    }
    public static function setViews($path): View
    {
        self::$newViews = $path . '/resources/views/';
        return new self();
    }
    public static function render($view, $data = []): View
    {
        self::blade();

        $viewPath = self::$views . "/$view";
        if (file_exists($viewPath . '.blade.php')) {
            echo self::$blade->run(str_replace('/', '.', $view), $data);
        } elseif (file_exists($viewPath . '.php')) {
            extract($data);
            require $viewPath . '.php';
        } else {
            throw new \Exception("View file not found: " . $viewPath);
        }
        return new self();
    }
}
