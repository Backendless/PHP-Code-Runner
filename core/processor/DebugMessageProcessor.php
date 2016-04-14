<?php
namespace backendless\core\processor;

use backendless\core\processor\MessageProcessor;
use Predis\Connection\ConnectionException;
use backendless\core\Config;
use backendless\core\lib\Log;


class DebugMessageProcessor extends MessageProcessor
{

    public function __construct() {
        
        parent::__construct();
        
    }
    
    public function run() {
    
        try {
                
            $predis = self::$redis_manager->getRedis();

            $result = $predis->blpop( [$this->getChannel()], 1 );

            if( $result == null ) {

                return;

            }elseif( $result[1] == Config::$CORE['local_shutdown_code'] ) {

                exit( 0 );

            }

            $this->dispatcher->onMessageReceived( $result[1] ); 
              
        } catch( ConnectionException $e ) {
                
            Log::writeError( $e->getMessage() );
              
        } catch( Exception $e ) {
                
            Log::writeError( $e->getMessage() );
                
        } 
    }
        
    public function getChannel() {
        
        return Config::$APPLICATION_ID;
        
    }
        
}
