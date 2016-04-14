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
    private static $keys_list = ['--driverHostPort', '--coderunnerId' ];

    public static function load( $argc, $argv ) {
        
        Config::loadConfig();

        self::phpEnviromentInit();
        
        Log::init( GlobalState::$TYPE, Config::$CORE['logging_in_cloud_mode'], Config::$CORE['os_type'] );
        
        Log::writeInfo("Start CodeRunner", $target = 'file');
        
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
        
        Log::writeInfo( "CodeRunner session is running for 2 hours and will be terminated on: " 
                        . date( "H:i:s", strtotime('+2 hours')) . " ( for you timezone: '" . date_default_timezone_get() . " ')", $target = 'console' );
        
    }

    private static function checkInputKeysForCloud( $argc, $argv ) {
        
        //./CodeRunner.sh --driverHostPort=<host:porrt> --coderunnerId=<id>
        unset( $argv[ 0 ] );
        
        if( count( $argv ) > 2 ) {
            
            Log::writeError( 'Script need two arguments, make sure that the set only --driverHostPort=<host:porrt> --coderunnerId=<id>' );
            exit( 0 );
        }
        
        $arg_info = [];
        
        foreach( $argv as $argument ) {
            
            $key_info = explode( "=", $argument );
            
            if( isset( $key_info[ 0 ] ) ) {
                
                $arg_info[ $key_info[ 0 ] ] = '';
            }
            
            if( isset( $key_info[ 1 ] ) ) {
                
                $arg_info[ $key_info[ 0 ] ] = $key_info[ 1 ];
            }
            
        }

        foreach ( self::$keys_list as $key ) {
        
            if( isset( $arg_info[ $key ] ) ) {

                if( $arg_info[ $key ] !== '' && $arg_info[ $key ] !== null ) {

                    Config::$CORE[ 'processing_' . str_replace( "-", "_", trim( $key, "-" ) ) ] = $arg_info[ $key ];
                    
                }else{

                    Log::writeError( " Missing value of argument $key " );
                    exit(0);  
                }

            } else {

                Log::writeError(" Missing argument $key ");
                exit(0);  

            }
        }
        
    }
    
    private static function checkForInputKeys( $argc, $argv ) {

        if( $argc <=1 ) { return false; }

        if( $argc != 4 && $argc !=2 && $argc !=5 ) {
            
            return Log::writeError("Invalid script arguments count", $target = 'console');
            
        }
        
        if( $argc == 2 ) {
            
             if( $argv[1] == "deploy" ) {
            
                 Config::$AUTO_PUBLISH = true;
                 return;
                 
             }
            
        }
        
        $argv_position = 0;
        
        if( $argc == 5) {

            if( $argv[1] == "deploy" ) {
            
                Config::$AUTO_PUBLISH = true;
                $argv_position = 1;
          
            } else {
                
                return Log::writeError("Invalid script arguments count", $target = 'console');
                
            }
            
        }

        if( $argc == 4 || $argc == 5 ) {
            
            Config::$APPLICATION_ID = $argv[ $argv_position+1 ];
            Config::$SECRET_KEY     = $argv[ $argv_position+2 ];
            Config::$APP_VERSION    = $argv[ $argv_position+3 ];
            Config::$need_save_keys = true;

            // chek if not format for cloud run
            $keys = [];
            $keys[] = explode( '=', $argv[1])[0];
            $keys[] = explode( '=', $argv[2])[0];
            $keys[] = explode( '=', $argv[3])[0];

            foreach ( self::$keys_list as $key ) {

                if( in_array($key, $keys) ) {

                    Log::writeError("Run in LOCAL mode don't need set keys " . implode( ',', self::$keys_list ), $target = 'all');
                    exit();

                }
            }
             
        }
        
      }
      
    private static function checkDefaultKeys() {
          
        if(    !preg_match( "/" . self::$VALID_PATTERN . "/", Config::$APPLICATION_ID, $matches ) 
            || !preg_match( "/" . self::$VALID_PATTERN . "/", Config::$SECRET_KEY, $matches )
            || Config::$APP_VERSION == null ) {
            
            echo "\n";
            Log::writeWarn("ApplicationID SecretKey application version is not set one or all values.", $target = 'console');
            Log::writeInfo("Try run again with specified ApplicationID SecretKey and application version as script arguments "
                           . "or edit it in config file [root_folder]" .DS."config.php", $target = 'console' );
            
            Log::writeInfo("NOTE: Run script pattern <ScriptName> [ApplicationID] [SecretKey] [AppVersion]", $target = 'console' );
            
            if( !preg_match( "/" . self::$VALID_PATTERN . "/", Config::$APPLICATION_ID, $matches ) ) {
                
                echo "\n";
                Log::writeWarn("Don't set or wrong application ID value.", $target = 'console' );
                Log::writeInfo("Please enter application ID and press [Enter]:!<new>", $target = 'console' );
            
                Config::$APPLICATION_ID = trim( fgets( STDIN ) );
                
                while( !preg_match( "/" . self::$VALID_PATTERN . "/", Config::$APPLICATION_ID, $matches ) ) {

                    Log::writeError( "The value is invalid, please try again:!<new>", $target = 'console' );

                    Config::$APPLICATION_ID = trim( fgets( STDIN ) );

                }
                
            }
            
            if(  !preg_match( "/" . self::$VALID_PATTERN . "/", Config::$SECRET_KEY, $matches ) ) {
            
                echo "\n";
                Log::writeWarn("Don't set or wrong CodeRunner secret key value.", $target = 'console' );
                Log::writeInfo("Please enter CodeRunner secret key and press [Enter]:!<new>", $target = 'console' );
            
                Config::$SECRET_KEY = trim( fgets( STDIN ) );
                
                while( !preg_match( "/" . self::$VALID_PATTERN . "/", Config::$SECRET_KEY, $matches ) ) {
                    
                    Log::writeError( "The value is invalid, please try again:!<new>", $target = 'console' );
            
                    Config::$SECRET_KEY = trim( fgets( STDIN ) );

                }
                
            }
            
            if( Config::$APP_VERSION == null ) {
                
                echo "\n";
                Log::writeWarn("Don't set or wrong application version value.", $target = 'console' );
                Log::writeInfo("Please enter application version and press [Enter]:!<new>", $target = 'console' );
            
                Config::$APP_VERSION = trim( fgets( STDIN ) );
                
                while( Config::$APP_VERSION == null ) {
                    
                    Log::writeError( "The value is invalid, please try again:!<new>", $target = 'console' );
                    Config::$APP_VERSION = trim( fgets( STDIN ) );
                    
                }
                
            }
            
            Config::$need_save_keys = true;
                
        }

    }
      
    public static function  phpEnviromentInit() {
        
        if ( GlobalState::$TYPE === 'LOCAL' ) {
        
             //for LOCAL use time zone or UTC if not set
             $timezone = @date_default_timezone_get(); // if not set default timezone set as UTC
             
             if( $timezone == 'UTC') {
                 
                 date_default_timezone_set('UTC');
                 
             }
            
        } else {
            
            date_default_timezone_set('UTC'); // for CLOUDE use UTC
            
        }
        
        // check if available openssl for use https
        if( ! extension_loaded("openssl") ) {
            
            Log::writeWarn('PHP module "openssl" not installed or not switch on in php.ini file', $target='file');
            
            Config::setServerUrl( preg_replace('/^http:\/\/|https:\/\/(.*)$/', 'http://${1}', Config::$SERVER_URL ) );
            
            Log::writeWarn('All https requests to ' . Config::$SERVER_URL . 'changed on http requests', $target='file');
            
        }
       
      }
}
