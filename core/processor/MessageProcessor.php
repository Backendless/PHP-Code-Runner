<?php
namespace backendless\core\processor;

use backendless\core\Config;
use backendless\core\RedisManager;
use backendless\core\processor\MessageDispatcher;
use backendless\core\lib\HttpRequest;
use backendless\core\lib\Log;


class MessageProcessor
{
      
    protected $dispatcher;
    protected static $redis_manager;

    public function __construct() {
        
        $this->dispatcher = new MessageDispatcher();
        
        if( !self::$redis_manager ) {
            
            self::$redis_manager = RedisManager::getInstance();
            
        }
        
    }

    public function run() {
   
        $socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
   
        $responce = '';
        
        $url_parts = parse_url( Config::$CORE[ 'processing_driverHostPort' ] );
  
        if( ! socket_connect( $socket, $url_parts[ 'host' ], $url_parts[ 'port' ] ) ) {
            
            Log::writeError( "Socket connection with host:{$url_parts[ 'host' ]} and port:{$url_parts[ 'port' ]} failed." );
            exit( 0 );
        
        } else {
            
            Log::writeInfo( "Connection to socket successful" );
            $this->sendPortInfo( [ "port:" => $url_parts[ 'port' ] ] );
                
        }
  
        while ( socket_recv( $socket, $buff, 2048, 0 ) ) {

            $responce .= $buff;

        }
                
        if( $responce == Config::$CORE[ 'shutdown_code' ] ) {
            
            Log::writeInfo( 'CodeRunner stopped from driver', $target = 'file' );
            
            exit( 0 );
                        
        }
                
        $this->dispatcher->onMessageReceived( $responce );
       
    }
    
    protected function sendPortInfo( $message ) {
        
        $http_request = new HttpRequest();
        
        $target = Config::$CORE[ 'processing_driverHostPort' ] . '/driverHostPort/sendPort';
        
        $http_request->setTargetUrl( $target )
                     ->setHeader( 'Content-type', 'application/json' )
                     ->request( json_encode( $message ), 'POST' );
        
        if( $http_request->getResponseCode() != 200 ) {
            
            Log::writeError( 'Sending port number to driver failed.' );
            exit( 0 );
        
        }
                
    }
    
}
