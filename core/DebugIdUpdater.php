<?php
//enable the display of errors
//error_reporting(E_ALL);
//ini_set('display_errors', true);

use backendless\core\lib\Autoload;
use backendless\core\RedisManager;
use backendless\core\Config;

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

$debug_id = $argv[1];

for( ; ; ){
    
    $predis->expire( $debug_id, 60 );
    sleep( 45 );
    
}