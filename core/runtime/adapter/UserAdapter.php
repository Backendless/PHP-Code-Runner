<?php
namespace backendless\core\runtime\adapter;

class UserAdapter
{

    public function adaptBeforeExecuting( $definition, $rmi, $args ) {
      
        if( $definition['provider'] !== 'User' ) {
        
            return $args;
      
        }

        if( $definition['name'] == 'beforeFindById' || $definition['name'] == 'afterFindById' ) {
            
            if( isset( $args[2] ) ){
            
                $objects =  $args[2];

                if( $objects != null && count($objects) == 0 ) {
                    
                    $args[2] = '';
                    
                }
                
            }
            
        }

        return $args;

    }
    
    public function adaptAfterExecuting( $definition, $rmi, $args, $result ) {
        
        return $args;
      
    }
  
}
