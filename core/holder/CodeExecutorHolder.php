<?php
namespace backendless\core\holder;

use backendless\core\GlobalState;
use backendless\core\runtime\CodeExecutor;
use backendless\core\holder\CodeExecutorFactory;


class CodeExecutorHolder
{
    private static $instance;

    private $debug_executor = null;     

    private function __construct() {
        
        $this->code_executor_factory = new CodeExecutorFactory();
        
    }

    public static function getInstance() {

        if( !self::$instance ) {
            self::$instance = new CodeExecutorHolder();
        }

        return self::$instance;
        
    }
    
    public function getCodeExecutor( $application_id, $app_version_id, $call_init = false ) {
        
        if( GlobalState::$TYPE == "LOCAL" ) {

            if( $this->debug_executor == null ) {
                
                $this->debug_executor = new CodeExecutor( $application_id , $app_version_id );
                
            }

            return $this->debug_executor; // $this->debug_executor need one instance for  possibility set event model in EventModelParser  class

        }
        
        $executor = new CodeExecutor( $application_id , $app_version_id );
        
        if( $call_init ) {
            
            $executor->init(); //in CLOUD mode red event model from file
            
        }
        
        return $executor;
       
    }
    
}