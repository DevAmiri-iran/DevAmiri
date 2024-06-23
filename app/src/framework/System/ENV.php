<?php

namespace App\System;

use Dotenv\Dotenv;

trait ENV
{
    protected static function start_env(): self
    {
        if (file_exists(base_path('.env')))
        {
            $dotenv = Dotenv::createImmutable(ROOT);
            $dotenv->load();
        }

        return new self();
    }
}