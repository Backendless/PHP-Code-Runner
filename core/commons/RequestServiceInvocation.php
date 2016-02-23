<?php
namespace backendless\core\commons;

use backendless\core\commons\AbstractRequest;
use backendless\core\commons\InitAppData;


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
    private $lang;
    private $decoded_arguments;

    public function __construct( $msg ) {

        parent::__construct();
        
        $this->setAppVersionId( $msg['appVersionId'] )
             ->setMethod( $msg['method'] )
             ->setServiceVersionId( $msg['serviceVersionId'] )
             ->setClassName( $msg['className']  )
             ->setTimeout( $msg['timeout'] )
             ->setInitAppData( $msg['initAppData'] )
             ->setInvocationContext( $msg['invocationContextDto'] )
             ->setRelativePath( $msg['relativePath'] )   
             ->setArguments( $msg['arguments'] )
             ->setId( $msg['id'] )
             ->setApplicationId( $msg['applicationId'] )
             ->setLang( $msg['lang'] )
             ->setServiceId( $msg['serviceId'] )
             ->setFileType( $msg['fileType'] )
             ->setProperties( $msg['properties'] )
             ->setTimestamp( $msg['timestamp'] );
        
        $this->decoded_arguments = null;
        
    }
    
    public function setLang( $lang ) {
        
        $this->lang = $lang;
        return $this;        
        
    }
    
    public function getLang() {
        
        return $this->lang;
        
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
      
        $this->arguments = '';

        foreach ( $arguments as $code ) {

            $this->arguments .= chr( $code ); //ASC||

        }
        
        $this->arguments = json_decode( $this->arguments , true );

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
    
    public function getShortClassName() {
        
        $parts = explode( '\\', $this->class_name );
        
        if( count( $parts ) > 1 ) {
            return array_pop( $parts );
        }
        
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
        
        if( is_array($init_app_data) ) {
            
            $init_app_data = new InitAppData( $init_app_data );
            
        }
        
        $this->init_app_data = $init_app_data;
        return $this;
        
    }

    public function getInvocationContext() {
        
        return $this->invocation_context;
        
    }
    
    public function getConfiguration() {
        
        if( isset( $this->invocation_context["configurationItems"] ) ) {
            
            if( count ( $this->invocation_context["configurationItems"] ) > 0 ) {
                
                return $this->invocation_context["configurationItems"];
                
            }
            
        }
        
        return [];
        
    }

    public function setInvocationContext(  $invocation_context ) {
        
        $this->invocation_context = $invocation_context;
        return $this;
      
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
