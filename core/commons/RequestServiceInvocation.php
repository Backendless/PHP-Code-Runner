<?php
namespace backendless\core\commons;

use backendless\core\commons\AbstractRequest;


class RequestServiceInvocation extends AbstractRequest
{
    
    private $service_id;
    private $service_version_id;
    private $file_type;
    private $class_name;
    private $method;
    private $init_app_data;
    private $invocation_context;
    private $properies;
    private $arguments;
    private $decoded_arguments;
    
    public function __construct( $msg ) {

        parent::__construct();

        $this->setServiceId( $msg['serviceId'] )
             ->setServiceVersionId( $msg['serviceVersionId'] )
             ->setFileType( $msg['fileType'] )   
             ->setClassName( $msg['className']  )
             ->setMethod( $msg['method'] )
             ->setInitAppData( $msg['initAppData'] )   
             ->setInvocationContext( $msg['invocationContext'] )
             ->setProperties( $msg['properties'] )
             ->setArguments( $msg['arguments'] )
             ->setRelativePath( $msg['relativePath'] );   
        
        $this->decoded_arguments = null;
        
    }

    public function getServiceId() {
        
        return $this->service_id;
        
    }

    public function setServiceId( $service_id ) {
        
      $this->service_id = $service_id;
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
    
    public function getServiceVersionId() {
        
      return $this->service_version_id;
      
    }

    public function setServiceVersionId( $service_version_id ) {
      
        $this->service_version_id = $service_version_id;
        return $this;
        
    }
    
    public function getFileType() {
        
        return $this->file_type;
    }

    public function setFileType( $file_type ) {
        
      $this->file_type = $file_type;
      return $this;
      
    }

    public function getClassName() {
        
        return $this->class_name;
    }

    public function setClassName( $class_name ) {
        
      $this->class_name = $class_name;
      return $this;
      
    }

    public function getMethod() {
      
        return $this->method;
      
    }

    public function setMethod( $method ) {
        
        $this->method = $method;
        return $this;
      
    }

    public function getProperties() {
        
        return $this->properies;
        
    }

    public function setProperties( $properties ) {
      
        $this->properies;
        return $this;
        
    }

    public function getInitAppData() {
        
        return $this->init_app_data;
    }

    public function setInitAppData( $init_app_data ) {
        
        $this->init_app_data = $init_app_data;
        return $this;
        
    }

    public function getInvocationContext() {
        
        return $this->invocation_context;
        
    }

    public function setInvocationContext(  $invocation_context ) {
        
        $this->invocation_context = $invocation_context;
        return $invocation_context;
      
    }

    public function __toString() {
      
      return "RequestServiceInvocation{" .
              " serviceId=" . $this->service_id .
              ", serviceVersionId=" . $this->service_version_id .
              ", className=" . $this->class_name .
              ", method=" . $this->method   .
              '}';
    }
    
}