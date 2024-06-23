<?php

namespace App\System;

use Illuminate\Database\Capsule\Manager as Capsule;

trait Database
{
    public static function useDatabase(): ?Capsule
    {
        $db = null;
        self::debug(function () use (&$db) {
            $config = require APP . '/config/database.php';
            $db = new Capsule;
            $db->addConnection($config);
            $db->setAsGlobal();
            $db->bootEloquent();
            $db::connection()->getPdo()->query('SELECT 1');
            return $db;
        });

        return $db;
    }


}