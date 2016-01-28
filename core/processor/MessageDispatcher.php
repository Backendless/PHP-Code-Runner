<?php
namespace backendless\core\processor;

use backendless\core\holder\CodeExecutorHolder;
use backendless\core\commons\RequestMethodInvocation;
use backendless\core\commons\RequestActionInvocation;    
use backendless\core\commons\RequestServiceInvocation;
use backendless\core\GlobalState;
use backendless\core\Config;
use backendless\core\util\ClassManager;
use backendless\core\util\PathBuilder;
use backendless\core\lib\Log;

class MessageDispatcher
{

    private $executor_holder;
    private $timeout;
    
    public function __construct() {
        
        $this->executor_holder = CodeExecutorHolder::getInstance();
        $this->timeout = 1000 * 60 * 20; //20 minutes in millis
        
    }

    public function  onMessageReceived( $msg ) {
        
        $msg = json_decode( $msg, true );
        
        if( isset( $msg["___jsonclass"] ) ) {
            
            $class_name_parts = explode( "." , $msg["___jsonclass"] );
                    
            switch( array_pop( $class_name_parts ) ) {
                
                case "RequestMethodInvocation" : $this->RequestMethodInvocation( $msg ); break;
                case "RequestActionInvocation" : $this->RequestActionInvocation( $msg ); break;
                case "RequestServiceInvocation" : $this->RequestServiceInvocation( $msg ); break;
                
                default : Log::writeError( "MessageDispatcher can`t define class provider of received message "); return;
                
            }
            
        } else{
            
            Log::writeError( 'Missing prperty "___jsonclass" in received message' );
            return;
            
        }
        
    }
    
    protected function RequestMethodInvocation( $msg ) {  //invocation event handlers 
        
        $rmi = new RequestMethodInvocation( $msg );
        
        Log::writeInfo( "Received RMI:" . $rmi, $target = 'file');
        
        if( GlobalState::$TYPE == 'CLOUD' && ( ( (time()*1000) - $rmi->getTimestamp() ) > $this->timeout ) ) {

            Log::writeError( "RMI ignored by timeout" . $rmi, $target = 'file');
            return;

        }
        
        if ( GlobalState::$TYPE === 'CLOUD') {
            
            Config::$RELATIVE_PATH  =   $msg['relativePath'];
            Config::$TASK_APPLICATION_ID = $msg['applicationId'];
            
            ClassManager::analyze( PathBuilder::getClasses() );
            
        }
        
        $code_executor = $this->executor_holder->getCodeExecutor( $rmi->getApplicationId(), $rmi->getAppVersionId(), true );
        $code_executor->invokeMethod( $rmi );
        
    }
    
    protected function RequestActionInvocation( $msg ) { //invocation actions// for example parse code for hosted service
        
        $rai = new RequestActionInvocation( $msg );
        
        Log::writeInfo( "Received RAI:" . $rai, $target = 'file');
        
        if( GlobalState::$TYPE == 'CLOUD' && ( ( (time()*1000) - $rai->getTimestamp() ) > $this->timeout ) ) {

            Log::writeError( "RAI ignored by timeout" . $rai, $target = 'file');
            return;

        }
        
        $executor = $this->executor_holder->getCodeExecutor( $rai->getApplicationId(), $rai->getAppVersionId() );
        $executor->invokeAction( $rai );
        
    }
    
    protected function RequestServiceInvocation( $msg ) {  /// invoke hosted servise action
        
        $rsi = new RequestServiceInvocation( $msg );
        
        Log::writeInfo( "Received RSI:" . $rsi, $target = 'file');
        
        if( GlobalState::$TYPE == 'CLOUD' && ( ( (time()*1000) - $rsi->getTimestamp() ) > $this->timeout ) ) {

            Log::writeError( "RSI ignored by timeout" . $rsi, $target = 'file');
            return;

        }
        
        if ( GlobalState::$TYPE === 'CLOUD') {
            
            Config::$RELATIVE_PATH  =   $msg['relativePath'];
            Config::$TASK_APPLICATION_ID = $msg['applicationId'];
            
            //ClassManager::analyze( PathBuilder::getClasses() );
            ClassManager::analyze( PathBuilder::getHostedService( $rsi->getAppVersionId(), $rsi->getRelativePath() ) );
            
        }
          
        
        $executor = $this->executor_holder->getCodeExecutor( $rsi->getApplicationId(), $rsi->getAppVersionId() );
        $executor->invokeService( $rsi );
        
    }
    
}