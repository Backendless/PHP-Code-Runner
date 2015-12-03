<?php
namespace backendless\core\commons;

use backendless\core\commons\AbstractRequest;
use backendless\core\commons\actionargs\CustomServiceParserArgs;


class RequestActionInvocation extends AbstractRequest
{
    
    private $action_type;
    private $arg_object;
    
    public function __construct( $msg ) {

        parent::__construct();

        $this->setId( $msg['id'] ) 
             ->setApplicationId( $msg['applicationId'] )
             ->setAppVersionId( $msg['appVersionId'] )
             ->setArgObject( $msg['argObject'] )
             ->setTimeout( $msg['timeout'] )
             ->setTimestamp( $msg['timestamp'] )
             ->setActionType( $msg['actionType'] )
             ->setRelativePath( $msg['relativePath'] );
        
    }

    public function getActionType() {
    
        return $this->action_type;
        
    }

    public function setActionType( $action_type ){
  
      $this->action_type = $action_type;
      return $this;
      
  }
  
  public function setArgObject( $args ) {
      
      if( is_array( $args ) ) {
          
          $args = new CustomServiceParserArgs( $args );
          
      }
      
      $this->arg_object = $args;
      return $this;
      
  }
  
  public function getArgObject() {
      
      return $this->arg_object;
      
  }

    public function __toString() {
      
      return "RequestActionInvocation{" .
              " appId=" . $this->application_id .
              ", versionId=" . $this->app_version_id .
              ", id=" . $this->id .
              ", timeout=" . $this->timeout .
              '}';
    }
    
}