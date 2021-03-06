<?php
namespace backendless\core\runtime\adapter;

use backendless\core\Config;
use backendless\core\servercode\ExecutionResult;

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
        
        foreach ( $args as $arg_index => $arg_val ) {

             if( isset( $arg_val[ "___jsonclass" ] ) ) {

                 if( $arg_val[ "___jsonclass" ] == Config::$CORE[ "execution_result" ] ) {

                     $execution_result = new ExecutionResult();
                     $execution_result->setException( $args[ $arg_index ][ "exception" ] );
                     $execution_result->setResult( $args[ $arg_index ][ "result" ] );
                     $args[ $arg_index ] = $execution_result;

                 }
             }

         }

        return $args;

    }
    
    public function adaptAfterExecuting( $definition, $rmi, $args, $result ) {
        
        return $args;
      
    }
  
}
