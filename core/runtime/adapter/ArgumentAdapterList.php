<?php
namespace backendless\core\runtime\adapter;


class ArgumentAdapterList
{
    
    private $adapter_list;
    
    public function __construct(){
        
        $this->adapter_list = [];
        
    }
    
    public function registerAdapter( $adapter ) {
      
        $this->adapter_list[] = $adapter;
        
    }

    public function beforeExecuting(  $definition, $rmi, $arguments ) {
        
        foreach ( $this->adapter_list as $index => $adapter) {
            
            $arguments = $adapter->adaptBeforeExecuting( $definition, $rmi, $arguments );
            
        }
        
        return $arguments;
        
    }
    
    
    public function afterExecuting( $definition, $rmi, $arguments, $result ) {
        
        foreach ( $this->adapter_list as $index => $adapter) {
            
          $arguments = $adapter->adaptAfterExecuting( $definition, $rmi, $arguments, $result );
          
        }
        
        return $arguments;
        
    }

}
