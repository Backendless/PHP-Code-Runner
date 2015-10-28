<?php
//enable the display of errors
error_reporting(E_ALL);
ini_set('display_errors', true);
if(function_exists('xdebug_disable')) { xdebug_disable(); }
declare(ticks = 1);

use backendless\core\lib\Autoload;
use backendless\core\CodeRunnerLoader;

// define short constants
define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
define('BP', dirname(dirname(__FILE__)));

ini_set('default_socket_timeout', -1);

//include file with CodeRunner autoloader
include 'lib' . DS . 'Autoload.php';

//include file with predis autoloader 
include 'lib' . DS . 'predis' . DS . 'autoload.php';

//include file with backendless SDK autoloader 
include '../lib' . DS . 'backendless' . DS . 'autoload.php';

// initialize app autoloading
Autoload::register();
Autoload::addNamespace( 'backendless\core', BP . DS .'core' );

// initialize and run application
CodeRunnerLoader::load( $argc, $argv );