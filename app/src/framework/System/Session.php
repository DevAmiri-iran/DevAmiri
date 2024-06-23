<?php

namespace App\System;

use Symfony\Component\HttpFoundation\Session\Session as SessionMain;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;

trait Session
{
    public static function start_session(): SessionMain
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $storage = new NativeSessionStorage(['expire_on_use' => true], new NativeFileSessionHandler());
            $session = new SessionMain($storage);
            $session->start();
        }
        else
        {
            $storage = new NativeSessionStorage(['expire_on_use' => true]);
            $session = new SessionMain($storage);
        }
        return $session;
    }
}