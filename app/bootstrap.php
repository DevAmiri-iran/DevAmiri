<?php
use App\System;

require_once 'src/vendor/autoload.php';

define('ROOT', dirname(__FILE__, 2));
define('APP', dirname(__FILE__));


System::up();
//System::useDatabase();
