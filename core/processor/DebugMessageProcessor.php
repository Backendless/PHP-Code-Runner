<?php
namespace backendless\core\processor;

use backendless\core\processor\MessageProcessor;
use Predis\Connection\ConnectionException;
use backendless\core\RedisManager;
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

            $result = $predis->blpop( [$this->getChannel()], 0 );

            if( $result == null ) {

                return;

            }elseif( $result[1] == Config::$CORE['local_shutdown_code'] ) {

                // stop background script for updating in redis expire of debugId
                posix_kill( Config::$DEBUG_PID, 9 );
                exit(0);

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
