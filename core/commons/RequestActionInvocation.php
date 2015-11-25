<?php
namespace backendless\core\commons;

use backendless\core\commons\AbstractRequest;
//use backendless\core\Config;
//use backendless\core\GlobalState;
//use backendless\core\util\ClassManager;

class RequestActionInvocation extends AbstractRequest
{
    private $event_id;
    private $arguments;
    private $decoded_arguments;
    private $target;
    private $timeout;
    private $async;
    
    public function __construct( $msg ) {

        var_dump(" RequestActionInvocation created ");
        
        parent::__construct();

                
        $this->id               =   $msg['id'];
        $this->application_id   =   $msg['applicationId'];
        $this->app_version_id   =   $msg['appVersionId'];
        
        
        $this->event_id     =   $msg['eventId'];
        $this->arguments    =   $msg['arguments'];
        $this->target       =   $msg['target'];
        $this->timeout      =   $msg['timeout'];
        $this->async        =   $msg['async'];
        
        $this->decoded_arguments = null;
        
        
    }
    
    public function getAppVersionId() {
        
        return $this->app_version_id;
        
    }

    public function getEventId() {
        
        return $this->event_id;
        
    }

    public function setEventId( $event_id ) {
        
      $this->event_id = $event_id;
      
    }
    
    public function getArguments() {
        
      return $this->arguments;
      
    }
    
    public function getDecodedArguments() {
        
        if( $this->decoded_arguments == null ) {
        
            $argumenst_decoded_string = '';

            foreach ($this->arguments as $code ) {

                $argumenst_decoded_string .= chr($code); //ASC||
                //$argumenst_decoded_string .= iconv( 'UCS-4LE', 'UTF-8', pack('V', $code) );
                //$argumenst_decoded_string .=  pack('V', $code);

            }

            $this->decoded_arguments = json_decode($argumenst_decoded_string, true);
        }
        
        return $this->decoded_arguments;
        
    }
    

    public function setArguments( $arguments ) {
      
        $this->arguments = $arguments;
        
    }
    
    public function getTarget() {
        
      return $this->target;
      
    }

    public function setTarget( $target ) {
      
        $this->target = $target;
        
    }

    public function getTimeout() {
        
      return $this->timeout;
      
    }

    public function setTimeout( $timeout ) {
      
        $this->timeout = $timeout;
        
    }

    public function isAsync() {
        
      return $this->async;
      
    }

    public function setAsync( $async ) {
        
      $this->async = $async;
      
    }

    public function __toString() {
      
      $async = ( $this->async == true ) ? "true" : "false";
        
      return "RequestMethodInvocation{" .
              " appId=" . $this->application_id .
              ", versionId=" . $this->app_version_id .
              ", eventId=" . $this->event_id .
              ", target=" . $this->target .
              ", timeout=" . $this->timeout .
              ", async=" . $async  .
              '}';
    }
    
}
     //JAVA  

/*
 package com.backendless.coderunner.commons.protocol;

import com.backendless.coderunner.commons.ActionType;
import com.backendless.coderunner.commons.actionargs.IActionArgs;

public class RequestActionInvocation extends AbstractRequest
{
  private ActionType actionType;
  private IActionArgs argObject;

  public RequestActionInvocation()
  {
  }

  public RequestActionInvocation(String id, String appId, String appVersionId, ActionType actionType, IActionArgs argObject, int timeout )
  {
    this.setId( id );
    this.setApplicationId( appId );
    this.setAppVersionId( appVersionId );
    this.setActionType( actionType );
    this.setArgObject( argObject );
    this.setTimeout( timeout );
  }

  public ActionType getActionType()
  {
    return actionType;
  }

  public void setActionType( ActionType actionType )
  {
    this.actionType = actionType;
  }

  public IActionArgs getArgObject()
  {
    return argObject;
  }

  public void setArgObject( IActionArgs argObject )
  {
    this.argObject = argObject;
  }
}
*/