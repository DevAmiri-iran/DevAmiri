<?php

namespace App\System;

trait Config
{
    protected static function config_loader(): self
    {
        \App\Support\Config::load(APP . '/config');
        return new self();
    }
}