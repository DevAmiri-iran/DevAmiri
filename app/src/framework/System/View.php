<?php

namespace App\System;

trait View
{
    protected static function config_view(): self
    {
        \App\View::init(base_path('/resources/views'), base_path('/resources/storage/cache'));
        return new self();
    }
}