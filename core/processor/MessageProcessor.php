<?php
namespace backendless\core\processor;

use backendless\core\Config;
use backendless\core\RedisManager;
use backendless\core\processor\MessageDispatcher;
use backendless\core\lib\HttpRequest;
use backendless\core\lib\Log;
use backendless\core\commons\exception\CodeRunnerException;


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
        
        $http_request = new HttpRequest();
        
        $target = Config::$CORE['processing_driverHostPort'] .'/getRequest?'
                                                                         . 'coderunnerId=' . Config::$CORE['processing_coderunnerId'] .''
                                                                         . '&requestId=' . Config::$CORE['processing_requestId']
                                                                         . '&lang=PHP';
        
        $http_request->setTargetUrl( $target )
                     ->setHeader('Content-type', 'application/json')
                     ->request( '', 'GET' );

        if( $http_request->getResponseCode() == 200 ) {

            Log::writeInfo( "Data received from java driver" . $http_request->getResponce(), $target = 'file' );
                        
            $this->dispatcher->onMessageReceived( $http_request->getResponce() );  

        } else {
        
            $msg = "CodeRunner get task fail, HTTP response code: " . $http_request->getResponseCode() . " response status: " . $http_request->getResponseStatus();

            Log::writeError( $msg, $target = 'file' );

            throw new CodeRunnerException( $msg );
            
        }
        
    }
    
}