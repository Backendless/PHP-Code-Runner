<?php
namespace backendless\core\runtime\adapter;

class MessagingAdapter
{

    public function adaptBeforeExecuting( $definition, $rmi, $args ) {
        
        return $args;
        
    }

    public function adaptAfterExecuting( $definition, $rmi, $args, $result ) {
        
        return $args;
        
    }
    
}
