<?php
namespace backendless\core\runtime\adapter;

class MessagingAdapter
{

    public function adaptBeforeExecuting( $definition, $rmi, $args ) {
        
        if( $definition['provider'] !== 'Messaging' ) {
            
          return $args;
          
        }

        if( $definition['name'] == 'afterPoll' ) {
            
            //TODO: implement
            var_dump( 'TODO: implement MessagingAdapter adaptBeforeExecuting' );

            //      ExecutionResult<Object[]> executionResult = (ExecutionResult<Object[]>) args[ args.length - 1 ];
            //      if( executionResult.getResult().length == 0 )
            //        executionResult.setResult( new Message[ 0 ] );
            
        }

        return $args;
        
    }

    public function adaptAfterExecuting( $definition, $rmi, $args, $result ) {
        
        return $args;
        
    }
    
}
