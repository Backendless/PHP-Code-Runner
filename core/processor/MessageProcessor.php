<?php
namespace backendless\core\processor;

use backendless\core\Config;
use backendless\core\RedisManager;
use backendless\core\processor\MessageDispatcher;
use backendless\core\lib\HttpRequest;
use backendless\core\lib\Log;


class MessageProcessor
{

    protected $socket = null;
    protected $bind_host = null;
    protected $bind_port = null;
    protected $is_send_port = false;
    
    protected $dispatcher;
    protected static $redis_manager;

    public function __construct() {
        
        $this->dispatcher = new MessageDispatcher();
        
        if( !self::$redis_manager ) {
            
            self::$redis_manager = RedisManager::getInstance();
            
        }
        
    }

    public function run() {
   
        if( $this->socket === null || $this->socket === false ) {
            
            $this->initSocket();
            
        }
        
        $this->listenSocket();
            
        $responce = '';
        
        $connect = socket_accept( $this->socket );

        while ( socket_recv( $connect, $buff, 2048, 0 ) ) {

            $responce .= $buff;

        }
                
        if( $responce == Config::$CORE[ 'shutdown_code' ] ) {
            
            Log::writeInfo( 'CodeRunner stopped from driver', $target = 'file' );
            
            exit( 0 );
                        
        }
                
        $this->dispatcher->onMessageReceived( $responce );
       
    }
    
    protected function initSocket() {
       
        $this->socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );

        if( ! $this->socket ) { 
             
            Log::writeError( socket_strerror( socket_last_error() ) );
            exit( 0 );
             
        }
        
        $url_parts = parse_url( Config::$CORE[ 'processing_driverHostPort' ] );
        
        if( ! socket_bind( $this->socket, $url_parts[ 'host' ], 0 ) ) {
            
            Log::writeError( socket_strerror( socket_last_error() ) );
            exit( 0 );
            
        }
        
    }
    
    protected function listenSocket(){
        
        socket_getsockname( $this->socket , $this->bind_host, $this->bind_port );
        
        if( !socket_listen( $this->socket ) ) { 
            
            Log::writeError( socket_strerror( socket_last_error() ) );
            exit( 0 );
            
        } else {
            
            if( !$this->is_send_port ) {
            
                $this->sendPortInfo( $this->bind_port );
                $this->is_send_port = true;
            
            }
            
            Log::writeInfo( "Socket listen on host:{$this->bind_host} and port:{$this->bind_port}" );
            
        }
    }
    
    protected function sendPortInfo( $port ) {
        
        $http_request = new HttpRequest();
        
        $target = Config::$CORE[ 'processing_driverHostPort' ] . '/driverHostPort/sendPort?port=' . $port;
        
        $http_request->setTargetUrl( $target )
                     ->setHeader( 'Content-type', 'application/json' )
                     ->request( '', 'GET' );
        
        if( $http_request->getResponseCode() != 200 ) {
            
            Log::writeError( 'Sending port number to driver failed.' );
            exit( 0 );
        
        }
                
    }
    
}
