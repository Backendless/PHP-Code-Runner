<?php
namespace backendless\core\commons;

class InvocationResult
{
    private $arguments;
    private $exeption;


    public function setInvocationResult( $arguments, $exeption = null ) {
      
        $this->arguments = $arguments;
        
        if( $exeption !== null ) {
            
            $this->exeption = $exeption;
            
        }
              
    }

    public function getArguments() {
        
      return $this->arguments;
      
    }
    
    public function setArguments( $arguments ) {

      $this->arguments = $arguments;

    }
  
    public function getException() {
        
      return $this->$exception;
      
    }

    public function setException( $exception ) {
        
        $this->exception = $exception;
        
    }
    
    private function encode( $string ) {
        
        $result_array = [];
        
        for( $i = 0; $i < strlen($string); $i++) {
            
            $result_array[$i] = ord($string[$i]);
            
        }
        
        return $result_array;
        
    } 
    
    public function getConvertedToArray() {
        
        $data = ["___jsonclass" => "com.backendless.coderunner.commons.InvocationResult"];
        $data['arguments'] = $this->encode( json_encode( $this->arguments) );
        $data['exception'] = $this->exeption;
        
        return $data;

    }
    
}
