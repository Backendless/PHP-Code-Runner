<?php
namespace backendless\core\commons;

use backendless\core\commons\AbstractRequest;


class RequestActionInvocation extends AbstractRequest
{
    private $event_id;
    private $arguments;
    private $decoded_arguments;
    private $target;
    private $async;
    
    private $action_type;
    
    public function __construct( $msg ) {

        parent::__construct();

        $this->setId( $msg['id'] )
             ->setApplicationId( $msg['applicationId'] )
             ->setAppVersionId( $msg['appVersionId'] )   
             ->setEventId( $msg['eventId']  )
             ->setArguments( $msg['arguments'] )
             ->setTarget( $msg['target'] )   
             ->setTimeout( $msg['timeout'] )
             ->setAsync( $msg['async'] )
             ->setActionType( $msg['actionType'] )
             ->setRelativePath( $msg['relativePath'] );   
        
        $this->decoded_arguments = null;
        
    }

    public function getEventId() {
        
        return $this->event_id;
        
    }

    public function setEventId( $event_id ) {
        
      $this->event_id = $event_id;
      return $this;
      
    }
    
    public function getArguments() {
        
      return $this->arguments;
      
    }
    
    public function getDecodedArguments() {
        
        if( $this->decoded_arguments == null ) {
        
            $argumenst_decoded_string = '';

            foreach ($this->arguments as $code ) {

                $argumenst_decoded_string .= chr($code); //ASC||
                
            }

            $this->decoded_arguments = json_decode( $argumenst_decoded_string, true );
        }
        
        return $this->decoded_arguments;
        
    }
    
    public function setArguments( $arguments ) {
      
        $this->arguments = $arguments;
        return $this;
        
    }
    
    public function getTarget() {
        
      return $this->target;
      
    }

    public function setTarget( $target ) {
      
        $this->target = $target;
        return $this;
        
    }

    public function isAsync() {
        
      return $this->async;
      
    }

    public function setAsync( $async ) {
        
      $this->async = $async;
      return $this;
      
    }
    
    public function getActionType() {
    
        return $this->action_type;
        
    }

    public function setActionType( $action_type ){
  
      $this->action_type = $action_type;
      return $this;
      
  }

    public function __toString() {
      
      $async = ( $this->async == true ) ? "true" : "false";
        
      return "RequestActionInvocation{" .
              " appId=" . $this->application_id .
              ", versionId=" . $this->app_version_id .
              ", eventId=" . $this->event_id .
              ", target=" . $this->target .
              ", timeout=" . $this->timeout .
              ", async=" . $async  .
              '}';
    }
    
}