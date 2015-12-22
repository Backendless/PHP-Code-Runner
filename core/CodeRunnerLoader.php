<?php 
namespace backendless\core;
    
use backendless\core\CodeRunner;
use backendless\core\commons\holder\EventDefinitionHolder;
use backendless\core\lib\Log;
use backendless\core\util\PathBuilder;
use backendless\core\util\ClassManager;

class  CodeRunnerLoader
{
    
    private static $VALID_PATTERN =  "[A-F0-9\-]{36}";
    private static $keys_list = ['--driverHostPort', '--requestId', '--coderunnerId' ];

    public static function load( $argc, $argv ) {
        
        Config::loadConfig();

        self::phpEnviromentInit();
        
        Log::init( Config::$CORE['os_type'] );
        
        Log::writeInfo("Start CodeRunner.", $target = 'file');
        
        EventDefinitionHolder::getInstance()->load();
        
        if( GlobalState::$TYPE === 'LOCAL' ) {
            
            self::checkForInputKeys( $argc, $argv );
            self::checkDefaultKeys();
            
            ClassManager::analyze( PathBuilder::getClasses() );
            
            self::printGreeting();
        
        } else {

            self::checkInputKeysForCloud( $argc, $argv );
          
        }
      
        $code_runner = new CodeRunner();
        $code_runner->loadMessageProcessor();
        $code_runner->start();

    }
    
    private static function printGreeting(){
        
        echo "\n";
        Log::write("CodeRunner(tm) Backendless Debugging Utility", $target = 'console' );
        Log::write("Copyright(C) " . date("Y", time()) . " Backendless Corp. All rights reserved.", $target = 'console');
        Log::write( "Version: " . Config::$VERSION . " \n", $target = 'console' );
        
    }

    
    private static function checkInputKeysForCloud( $argc, $argv ) {
        
        //./CodeRunner.sh --driverHostPort=<host:porrt> --requestId=<id> --coderunnerId=<id>

        unset( $argv[0] );
        
        if( $argc <= 3 ) {
            
            Log::writeError("Not all the arguments passed to the script, make sure that the set --driverHostPort=<host:porrt> --requestId=<id> --coderunnerId=<id>");
            exit(0);
        }
        
        $arg_info = [];
        
        foreach( $argv as $argument ) {
            
            $key_info = explode( "=", $argument );
            
            if( isset( $key_info[0]) ) {
                
                $arg_info[$key_info[0]] = '';
            }
            
            if( isset( $key_info[1] ) ) {
                
                $arg_info[$key_info[0]] = $key_info[1];
            }
            
        }

        foreach ( self::$keys_list as $key ) {
        
            if( isset($arg_info[ $key ]) ) {

                if( $arg_info[ $key ] !== '' && $arg_info[ $key ] !== null ) {

                    Config::$CORE['processing_' . str_replace("-", "_", trim($key, "-") ) ] = $arg_info[$key];
                    
                }else{

                    Log::writeError(" Missing value of argument $key ");
                    exit(0);  
                }

            } else {

                Log::writeError(" Missing argument $key ");
                exit(0);  

            }
        }
        
    }
    
    private static function checkForInputKeys( $argc, $argv ) {

        if( $argc <=1 ) {
            return false;
        }
        
        if( $argv[1] == "deploy" ) {
          Config::$AUTO_PUBLISH = true;
        }

        if( $argc == 4 ) {
            Config::$APPLICATION_ID = $argv[2];
            Config::$SECRET_KEY     = $argv[3];
            
            // chek if not format for cloud run
            $keys = [];
            $keys[] = explode( '=', $argv[1])[0];
            $keys[] = explode( '=', $argv[2])[0];
            $keys[] = explode( '=', $argv[3])[0];
            
            foreach ( self::$keys_list as $key ) {
                 
                if( in_array($key, $keys) ) {
                    
                    Log::writeError("Run in LOCAL mode don't need set keys " . implode(',', self::$keys_list), $target = 'all');
                    exit();
                    
                }
            }
             
        }
        
        if( $argc == 3 ) {
            
          Config::$APPLICATION_ID   = $argv[1];
          Config::$SECRET_KEY       = $argv[2];
          
        }

        Config::saveKeys();
        
      }
      
    private static function checkDefaultKeys() {
          
        if( (Config::$APPLICATION_ID === null || Config::$APPLICATION_ID == '' ) || ( Config::$SECRET_KEY === null || Config::$SECRET_KEY == '' ) ) {
            
            echo "\n";
            Log::writeWarn("ApplicationID or SecretKey is not set.", $target = 'console');
            Log::writeInfo("Try run again with specified ApplicationID and SecretKey as script arguments "
                           . "or edit it in config file [root_folder]" .DS."config.php", $target = 'console' );
            
            Log::writeInfo("NOTE: Run script pattern <ScriptName> [ApplicationID] [SecretKey] \n", $target = 'console' );
            
            if( Config::$APPLICATION_ID === null || Config::$APPLICATION_ID == '' ) {
                
                Log::writeInfo("Please enter application ID and press [Enter]:", $target = 'console' );
            
                Config::$APPLICATION_ID = trim(fgets(STDIN));
                
            }
            
            if(  Config::$SECRET_KEY === null || Config::$SECRET_KEY == ''  ) {
            
                Log::writeInfo("Please enter CodeRunner secret key and press [Enter]:", $target = 'console' );
            
                Config::$SECRET_KEY = trim(fgets(STDIN));
            }
                
        }
        
        if( !preg_match( "/" . self::$VALID_PATTERN . "/", Config::$APPLICATION_ID, $matches ) ) {
            
            echo "\n";
            Log::writeWarn("ApplicationID is invalid.", $target = 'console');
            Log::writeInfo("Try again run script with specified ApplicationID and SecretKey as script arguments "
                           . "or edit it in config file [root_folder]" .DS."config.php.", $target = 'console');
            Log::writeInfo("NOTE: Run script pattern <ScriptName> [ApplicationID] [SecretKey] \n", $target = 'console' );
            
            Log::writeInfo("Please re enter application ID and press [Enter]:", $target = 'console' );
            
            Config::$APPLICATION_ID = trim(fgets(STDIN));
                
        }
        
       if( !preg_match( "/" . self::$VALID_PATTERN . "/", Config::$SECRET_KEY, $matches ) ) {
           
            echo "\n";
            Log::writeWarn("SecretKey is invalid.", $target = 'console');
            Log::writeInfo("Try again run script with specified ApplicationID and SecretKey as script arguments "
                           . "or edit it in config file [root_folder]" .DS."config.php.", $target = 'console');
            Log::writeInfo("NOTE: Run script pattern <ScriptName> [ApplicationID] [SecretKey] \n", $target = 'console' );
            
            Log::writeInfo("Please re enter CodeRunner secret key and press [Enter]:", $target = 'console' );
            
            Config::$SECRET_KEY = trim(fgets(STDIN));

        }
        
        Config::saveKeys();

    }
      
    public static function  phpEnviromentInit() {
          
        //set default timezone need for WIN and OS X
        date_default_timezone_set('UTC');
        
        // check if available openssl for use https
        if( ! extension_loaded("openssl") ) {
            
            Log::writeWarn('PHP module "openssl" not installed or not switch on in php.ini file', $target='file');
            
            Config::setServerUrl( preg_replace('/^http:\/\/|https:\/\/(.*)$/', 'http://${1}', Config::$SERVER_URL ) );
            
            Log::writeWarn('All https requests to ' . Config::$SERVER_URL . 'changed on http requests', $target='file');
            
        }
         
          
      }
}
