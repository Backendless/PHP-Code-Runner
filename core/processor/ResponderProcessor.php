<?php
namespace backendless\core\processor;

use Predis\Connection\ConnectionException;
use backendless\core\lib\Log;
use backendless\core\RedisManager;
use backendless\core\lib\HttpRequest;
use backendless\core\Config;

class ResponderProcessor
{
    
    private static $redis_manager;
    private static $results_queue = [];
    
    public function __construct() {
        
        if( !self::$redis_manager ) {
            
            self::$redis_manager = RedisManager::getInstance();
            
        }
        
    }
  
    public function localRun() {
        
        try {
            
            $result = array_shift(self::$results_queue);
            
            if( $result === null ) {
                
                return;
                
            }
                
            $predis = self::$redis_manager->getRedis();
            
            $predis->rpush( $result['destination'], json_encode( $result['result']->getConvertedToArray() ) );
            $predis->expire( $result['destination'], 10);
            
            Log::writeInfo( "Invocation result push to redis", "file");

            
        } catch( ConnectionException $e ) {

            Log::writeError($e->getMessage());
            
        } catch( Exception $e ) {
            
            Log::writeError($e->getMessage());
            
        }

    }
    
    public function cloudRun() {
        
        $result = array_shift( self::$results_queue );
            
            if( $result === null ) {
                
                return;
        }

        $http_request = new HttpRequest();
        
        $target = Config::$CORE['processing_driverHostPort'] . '/sendResult?'
                                                       . 'coderunnerId=' . Config::$CORE['processing_coderunnerId'] . ''
                                                       . '&requestId=' . Config::$CORE['processing_requestId'] . ''
                                                       . '&lang=PHP';
        $result_data = '';
        
        if( is_object( $result['result'] ) ) {
            
            $result_data = json_encode( $result['result']->getConvertedToArray() );
            
        } else {
            
            $result_data = json_encode($result['result']);
            
        }
        
        $http_request->setTargetUrl( $target )
                     ->setHeader('Content-type', 'application/json')
                     ->request( $result_data , 'POST' );
        
        Log::writeInfo( "Data sent to java driver" . $result_data, $target = 'file' );

        if( $http_request->getResponseCode() !== 200 ) {
            
            $msg = "CodeRunner set task result fail, HTTP response code: " . $http_request->getResponseCode() . " response status: " . $http_request->getResponseStatus();

            Log::writeError($msg, $target = 'file');

        } else {
            
            Log::writeInfo( "Invocation result put to driver", "file");
            
        }
        
    }

    public static function sendResult(  $destination, $invocation_result ) {
        
      array_push( self::$results_queue, array( 'destination' => $destination, 'result' => $invocation_result ) );  

    }
    
}