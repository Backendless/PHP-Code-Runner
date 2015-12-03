<?php
namespace backendless\core\runtime\task;

use backendless\core\runtime\concurrent\Runnable;
use backendless\core\parser\HostedServiceParser;
use backendless\core\processor\ResponderProcessor;
use backendless\core\commons\InvocationResult;
use backendless\core\util\XmlManager;
use backendless\core\Config;
use backendless\core\lib\Log;
use Exception;


class HostedServiceInvocationTask extends Runnable
{
    
    private $rsi;

    
    public function __construct( $rsi ) {

        $this->rsi = $rsi;
        
    }

    public function runImpl() {
        
        Log::writeInfo("Called invocation task: " . $this->rsi, $target = 'file' );

        if( $this->rsi == null ) {
            
            Log::writeInfo("Something is null in InvocationActionTask...");
            return;
            
        }

       // $invocation_result = new InvocationResult();
        
        try{       
            
            var_dump("RUN IMPL for service invocation");
            var_dump("TODO #44");

            ResponderProcessor::sendResult( $this->rsi->getId(), "ok" );

                
        } catch( Exception $e ) { 
            
            Log::writeError( $e->getMessage() );
            
        }
    
  }

     
}
