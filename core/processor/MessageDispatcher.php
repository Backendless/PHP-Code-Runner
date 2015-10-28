<?php
namespace backendless\core\processor;

use backendless\core\holder\CodeExecutorHolder;
use backendless\core\commons\RequestMethodInvocation;
use backendless\core\GlobalState;
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
        
        $rmi = new RequestMethodInvocation( $msg );
        
        if( GlobalState::$TYPE == 'CLOUD' && ( ( (time()*1000) - $rmi->getTimestamp() ) > $this->timeout ) ) {

            Log::writeError( "RMI ignored by timeout" . $rmi, $target = 'file');
            return;

        }
        
        $code_executor = $this->executor_holder->getCodeExecutor( $rmi->getApplicationId(), $rmi->getAppVersionId() );
        $code_executor->invoke( $rmi );

    }
}