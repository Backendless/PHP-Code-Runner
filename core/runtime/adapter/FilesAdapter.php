<?php
namespace backendless\core\runtime\adapter;

use backendless\core\Config;
use backendless\core\servercode\ExecutionResult;
use backendless\core\servercode\RunnerContext;

class FilesAdapter
{

    public function adaptBeforeExecuting( $definition, $rmi, $args ) {
      
        if( $definition['provider'] !== 'File' ) {
        
            return $args;
      
        }
        
        foreach ( $args as $arg_index => $arg_val ) {

             if( isset( $arg_val[ '___jsonclass' ] ) ) {

                 if( $arg_val[ '___jsonclass' ] == Config::$CORE[ 'execution_result' ] ) {
                     
                     $execution_result = new ExecutionResult();
                     $execution_result->setException( $args[ $arg_index ][ 'exception' ] );
                     $execution_result->setResult( $args[ $arg_index ][ 'result' ] );
                     $args[ $arg_index ] = $execution_result;

                 }
                 
                 if( $arg_val[ '___jsonclass' ] == Config::$CORE[ 'runner_context' ] ) {
                     
                     $args[ $arg_index ] = new RunnerContext( $args[ $arg_index ] );

                 }
                 
             }

         }

        return $args;

    }
    
    public function adaptAfterExecuting( $definition, $rmi, $args, $result ) {
        
        return $args;
      
    }
  
}
