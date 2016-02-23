<?php 
namespace backendless\core\runtime;

use backendless\core\lib\Log;
use backendless\core\parser\EventModelParser;
use backendless\core\Config;
use backendless\core\GlobalState;
use backendless\core\runtime\task\InvocationTask;
use backendless\core\runtime\task\HostedServiceParseTask;    
use backendless\core\runtime\task\HostedServiceInvocationTask;


class CodeExecutor
{

    private $application_id;
    private $app_version_id;
    private $event_handlers_model = null;

    public function __construct( $application_id, $app_version_id ) {

        $this->application_id = $application_id;
        $this->app_version_id = $app_version_id;
        
    }

    public function invokeMethod( $rmi ) { 
       
        if( $rmi->getEventId() == Config::$CORE['shutdown_code'] && GlobalState::$TYPE == 'LOCAL' ) {

            Log::writeWarn("CodeRunner was stopped from console!");
            exit( 1 );

        }

        if($this->event_handlers_model == null ) {
            
            Log::write("Event handler model is null...", $target='file');
            return;
            
        }
        
        $event_handler = $this->event_handlers_model->getEventHandler( $rmi->getEventid(), $rmi->getTarget() );

        $invocation_task = new InvocationTask(  $rmi, $event_handler );

        $invocation_task->runImpl();
       
    }
    
    public function invokeAction( $rai ) {
        
        switch ( $rai->getActionType() ) {
            
            case 'PARSE_CUSTOM_SERVICE_FROM_JAR': 
                                                    $invocation_task = new HostedServiceParseTask( $rai );
                                                    $invocation_task->runImpl();
                                                    break;    
            
            default : Log::writeError("Can't define action type of received RequestActionInvocation", $target = "all");
            
        }

  }
  
   public function invokeService( $rsi ) {
       
        $invocation_task = new HostedServiceInvocationTask( $rsi );
        $invocation_task->runImpl();

  }

    public function init() {

        if( $this->event_handlers_model == null ) {
            
            $this->initEventModel();
          
        }
        
    }
      
    private function initEventModel() {
        
      Log::write( "Init event model for appVersionId : " . $this->app_version_id, $target = "file" );
      $this->event_handlers_model = EventModelParser::getInstance()->parseProductionModel( $this->app_version_id );
      
    }

    public function setEventHandlersModel( $event_handlers_model ) {

        $this->event_handlers_model = $event_handlers_model;

    }

    public function  __toString() {
        
        return "CodeExecutor{" .
                "applicationId='" . applicationId . '\'' .
                ",appVersionId='" . appVersionId . '\'' .
                '}';
        
    }
  
}