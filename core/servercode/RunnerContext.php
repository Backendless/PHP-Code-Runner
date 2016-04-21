<?php
namespace backendless\core\servercode;

use backendless\core\Config;
use backendless\core\servercode\AbstractContext;
use backendless\core\commons\exception\CodeRunnerException;


class RunnerContext extends AbstractContext
{

    private $missingProperties;
    private $prematureResult;
    private $eventContext;

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

    public function setPrematureResult( $premature_result ) {  //
        
        if( !is_array( $premature_result ) ) {
            
            throw new CodeRunnerException( 'Method \'setPrematureResult\' argument \'$premature_result\' must be array!' );
        
        }
        
        $this->prematureResult = $premature_result;
        return $this;
         
    }
    
    public function getConvertedToArray() {
        
        $properties =  [ '___jsonclass' => Config::$CORE[ 'runner_context' ] ];
        
        return array_merge( $properties, get_object_vars( $this ) );
        
    }
    
    public function getEventContext() {
        
        return $this->eventContext;
        
    }
    
    protected function setEeventContext( &$arguments ) {
        
        if( isset( $arguments[ 'eventContext' ] ) ) {
            
            $this->eventContext = $arguments[ 'eventContext' ];
            
        } else {
            
            $this->eventContext = null;
            
        }
        
    }
    
}
