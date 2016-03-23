<?php
namespace backendless\core\commons;

use backendless\core\commons\AbstractRequest;

class RequestMethodInvocation extends AbstractRequest
{
    private $event_id;
    private $arguments;
    private $decoded_arguments;
    private $target;
    private $async;
    
    public function __construct( $msg ) {
        
        parent::__construct();

        $this->setId( $msg[ 'id' ] )
             ->setApplicationId( $msg[ 'applicationId' ] )
             ->setAppVersionId( $msg[ 'appVersionId' ] )   
             ->setEventId( $msg[ 'eventId' ]  )
             ->setArguments( $msg[ 'arguments' ] )
             ->setInitAppData( $msg[ 'initAppData' ] )
             ->setTarget( $msg[ 'target' ] )   
             ->setTimeout( $msg[ 'timeout' ] )
             ->setAsync( $msg[ 'async' ] )
             ->setRelativePath( $msg[ 'relativePath' ] );   
        
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

            foreach ( $this->arguments as $code ) {

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
        return $this;
        
    }
    
    public function getTarget() {
        
      return $this->target;
      
    }

    public function setTarget( $target ) {
      
        $this->target = $target;
        return $this;
        
    }

    public function getTimeout() {
        
      return $this->timeout;
      
    }

    public function setTimeout( $timeout ) {
      
        $this->timeout = $timeout;
        return $this;
        
    }

    public function isAsync() {
        
      return $this->async;
      
    }

    public function setAsync( $async ) {
        
      $this->async = $async;
      return $this;
      
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
