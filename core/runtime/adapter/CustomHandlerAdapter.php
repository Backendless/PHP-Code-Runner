<?php
namespace backendless\core\runtime\adapter;

class CustomHandlerAdapter
{

    public function adaptBeforeExecuting( $definition, $rmi, $args ) {
      
        if( $definition['name'] !== 'handleEvent' ) {
            
            return $args;
            
        }

        $new_arguments = [];
        
        $new_arguments[0] = $args[0];
        $new_arguments[1] = $args[1];
        
        return $new_arguments;
      
    }


    public function adaptAfterExecuting( $definition, $rmi, $args, $result ) {

        if( $definition['name'] !== 'handleEvent' ) {
            
            return $args;
            
        }

        $new_arguments = [ ];
        
        $new_arguments[0] = $args[0];
        $new_arguments[1] = $args[1];
        $new_arguments[2] = $result;
        
        return $new_arguments;
        
    }
  
}
