<?php

//enable the display of errors
error_reporting(E_ALL);
ini_set('display_errors', true);

use backendless\core\lib\Autoload;
use backendless\core\RedisManager;
use backendless\core\Config;
use backendless\core\lib\Log;

// define short constants
define('DS', DIRECTORY_SEPARATOR);
define('BP', dirname(dirname(__FILE__)));

//include file with backendkess autoloader
include "lib" . DS . "Autoload.php";

//include file with predis autoloader 
include "lib" . DS . "predis" . DS . "autoload.php";

// initialize app autoloading
Autoload::register();
Autoload::addNamespace('backendless\core', BP . DS .'core' );

Config::loadConfig();
$predis = RedisManager::getInstance()->getRedis();

$predis->rpush( Config::$APPLICATION_ID, Config::$CORE['local_shutdown_code'] );
$predis->expire( Config::$APPLICATION_ID, 5);

echo "\n";
Log::writeInfo("CodeRunner Backendless debugging utility stopped!\n", $target ="console");
