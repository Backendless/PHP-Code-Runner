<?php
namespace backendless\core\servercode;

use backendless\core\Config;
use backendless\core\servercode\AbstractContext;


class RunnerContext extends AbstractContext
{

    private $missingProperties;
    private $prematureResult;

    public function __construct( $arguments ) {
        
        parent::__construct( $arguments );
        
        $this->missingProperties   =   $arguments[ 'missingProperties' ];
        $this->prematureResult     =   $arguments[ 'prematureResult' ];
        
        $this->setEeventContext( $arguments );
        
    }

    public function getMissingProperties() { // return map (array)
        
        return $this->missingProperties;
        
    }
    
    public function setMissingProperties( $missing_properties ) { // set map (array)
        
        $this->missingProperties = $missing_properties;
        return $this;
         
    }
    
    public function getPrematureResult() { // return object
        
        return $this->prematureResult;
        
    }

    public function setPrematureResult( $premature_result ) {
        
        $this->prematureResult = $premature_result;
        return $this;
         
    }
    
    public function getConvertedToArray(){
        
        $properties =  ["___jsonclass" => Config::$CORE["runner_context"] ];
    
        return array_merge( $properties, get_object_vars( $this ) );
        
    }
    
    public function getEventContext() {
        
        return $this->event_context;
        
    }
    
    protected function setEeventContext( &$arguments ) {
        
        if( isset( $arguments[ 'eventContext' ] ) ) {
            
            $this->event_context = $arguments[ 'eventContext' ];
            
        } else {
            
            $this->event_context = null;
            
        }
        
    }
    
}
