<?php 
namespace backendless\core;

use Predis\Client as Predis;
use backendless\core\Config;
use backendless\core\lib\Log;
use Predis\Connection\ConnectionException;


class RedisManager
{
    
    private static $instance; 
    
    private $redis;
    
    private function __construct() {
        
        try{
            
            $this->redis = new Predis( [    "scheme"    =>  "tcp", 
                                            "host"      =>  Config::$REDIS_HOST, 
                                            "port"      =>  Config::$REDIS_PORT
                                        ]
                                      );
            
            // Log::writeInfo("Successfully connected to Redis", $target = 'file');
            
            
        } catch ( ConnectionException $e ){
            
            Log::writeError("Couldn't connected to Redis", $target = 'file');
            
        }
        
    }
    
    static public function getInstance() {

        if (!self::$instance) {
            
            self::$instance = new RedisManager();
            
        }

        return self::$instance;

    }
    
    public function getRedis() {
        
        return $this->redis;
        
    }

}
