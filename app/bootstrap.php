<?php
use App\System;

if (file_exists(__DIR__ . '/src/vendor/autoload.php'))
    require_once __DIR__ . '/src/vendor/autoload.php';
else
    die('To run this project, Composer must be installed.');

define('ROOT', dirname(__FILE__, 2));
define('APP', dirname(__FILE__));


System::up();
//System::useDatabase();
