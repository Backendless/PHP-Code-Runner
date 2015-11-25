<?php
namespace backendless\core\commons;

use backendless\core\commons\AbstractRequest;

class RequestMethodInvocation extends AbstractRequest
{
    private $event_id;
    private $arguments;
    private $decoded_arguments;
    private $target;
    private $timeout;
    private $async;
    
    public function __construct( $msg_array ) {
        
        parent::__construct();
        
        $this->id               =   $msg_array['id'];
        $this->application_id   =   $msg_array['applicationId'];
        $this->app_version_id   =   $msg_array['appVersionId'];
        
        $this->event_id     =   $msg_array['eventId'];
        $this->arguments    =   $msg_array['arguments'];
        $this->target       =   $msg_array['target'];
        $this->timeout      =   $msg_array['timeout'];
        $this->async        =   $msg_array['async'];
        $this->relative_path    =   $msg_array['relativePath'];
        
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