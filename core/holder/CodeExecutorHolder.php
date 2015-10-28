<?php
namespace backendless\core\holder;

use backendless\core\GlobalState;
use backendless\core\runtime\CodeExecutor;
use backendless\core\Config;
use backendless\core\holder\CodeExecutorFactory;


class CodeExecutorHolder
{
    private static $instance;
    
    private $cache_manager;             
    private $code_executor_factory;
    
    private $debug_executor = null;     

    private function __construct() {
        
        $this->code_executor_factory = new CodeExecutorFactory();
        $this->cache_manager = [];
        
    }

    public static function getInstance() {

        if( !self::$instance ) {
            self::$instance = new CodeExecutorHolder();
        }

        return self::$instance;
        
    }
    
    public function getCodeExecutor( $application_id, $app_version_id ) {
        
        if( GlobalState::$TYPE == "LOCAL" ) {

            if( $this->debug_executor == null ) {
                
                $this->debug_executor = new CodeExecutor( Config::$APPLICATION_ID , Config::$APP_VERSION );
                
            }

            return $this->debug_executor;

        }

        if( isset( $this->cache_manager[$app_version_id] ) ) {
            
          return $this->cache_manager[$app_version_id];

        }

        $executor = $this->code_executor_factory->createExecutor($application_id, $app_version_id);

        $this->cache_manager[$app_version_id] = $executor;

        return $executor;
       
    }
    
}
