<?php
namespace backendless\core\commons\exception;
use backendless\core\Config;

use Exception;


class ExceptionWrapper extends Exception {
   
    protected $code;
    protected $exception_class;
    protected $exception_message;
    protected $http_status_code = -1;
    
    public function __construct( $message, $code = 0, $previous = null, $http_status_code = -1 ) {
        parent::__construct( $message, $code, $previous );

        $this->setCode( $code );
        $this->setExceptionClass( $previous );
        $this->setExceptionMessage( $message );
        $this->setHTTPStatusCode( $http_status_code);

    }

    public function setCode( $code ) {

        $this->code = $code;
        return $this;

    }

    public function setExceptionMessage( $message ) {

        $this->exception_message = $message;
        return $this;

    }

    public function getExceptionMessage() {

        return $this->exception_message;

    }
    
    public function setExceptionClass( $exception_class ) {

        if( is_object( $exception_class ) ) {
            
            $this->exception_class = get_class( $exception_class );
            
        } elseif (is_string( $exception_class ) ) {
        
            $this->exception_class = $class_name;
        } 
        
        return $this;

    }

    public function getExceptionClass() {

        return $this->exception_class;

    }

    public function setHTTPStatusCode( $class_name ) {

        $this->http_status_code = $class_name;
        return $this;

    }

    public function getHTTPStatusCode() {

        return $this->http_status_code;

    }
    
    public function getAsArray() {
        
        $data = [];
        
        $data[ '___jsonclass' ]     =   Config::$CORE["exception_wrapper"];
        $data[ 'code' ]             =   $this->code;
        $data[ 'exceptionClass' ]   =   $this->exception_class;
        $data[ 'exceptionMessage' ] =   $this->exception_message;
        $data[ 'httpStatusCode' ]   =   $this->http_status_code;
        
        return $data;

    }
    
    public function __toString() {
      return "ExceptionWrapper:" 
                . "code= " . $this->code 
                . "exceptionMessage=\"" . $this->exception_message . "\" "
                . "exceptionClass=\"" . $this->exception_class . "\" "
                . "httpStatusCode=" . $this->http_status_code ;
      
    }

}
