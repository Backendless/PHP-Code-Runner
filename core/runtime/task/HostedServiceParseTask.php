<?php
namespace backendless\core\runtime\task;

use backendless\core\runtime\concurrent\Runnable;
use backendless\core\parser\HostedServiceParser;
//use backendless\core\commons\holder\EventDefinitionHolder;
//use backendless\core\runtime\adapter\ArgumentAdapterList;
//use backendless\core\runtime\adapter\PersistenceAdapter;
//use backendless\core\runtime\adapter\UserAdapter;
//use backendless\core\runtime\adapter\MessagingAdapter;
//use backendless\core\runtime\adapter\CustomHandlerAdapter;
use backendless\core\processor\ResponderProcessor;
use backendless\core\commons\InvocationResult;
//use backendless\core\util\ClassManager;
//use backendless\exception\BackendlessException;
use backendless\core\lib\Log;
//use backendless\Backendless;
//use ReflectionClass;
//use ReflectionMethod;



class HostedServiceParseTask extends Runnable
{
    
    private $rai;

    
    public function __construct( $rai ) {

        $this->rai = $rai;
        
    }

    public function runImpl() {
        
        Log::writeInfo("Called invocation task: " . $this->rai, $target = 'file' );

        if( $this->rai == null ) {
            
            Log::writeInfo("Something is null in InvocationActionTask...");
            return;
            
        }

        $invocation_result = new InvocationResult();
        
        try{       
            
            var_dump("RUN IMPL for parsing");
            
            $hosted_parser = new HostedServiceParser();
            
            $hosted_parser->parseFolderWithCustomCode(); 
            
            
            var_dump($hosted_parser->getErrorAsJson());
            ResponderProcessor::sendResult( $this->rai->getId(), "ok" );
            
            //java // ResponderProcessor.sendResult( rsi.getId(), new Object[]{o} );
                
        } catch( Exception $e ) { 
            
            Log::writeError( $e->getMessage() );
            
        }
    
  }

     
}
