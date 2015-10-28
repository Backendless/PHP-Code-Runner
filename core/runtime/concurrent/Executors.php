<?php
namespace backendless\core\runtime\concurrent;

use backendless\core\lib\Log;
use backendless\core\GlobalState;
use Exception;


class Executors
{

  private static $DEBUG_TIMEOUT = 600000; //10 * 60 * 1000;

  public static function execute( $invocation_task ) {
      
    if( GlobalState::$TYPE  == "LOCAL" ) {
        
      $invocation_task->setTimeout( self::$DEBUG_TIMEOUT );
      
    }

    try {
        
        $invocation_task->runImpl();
        
    } catch( Exception $e ) {
        
        Log::writeError("Failed task execution" , $e->getMessage() );
      
    }
    
  }
  
}
