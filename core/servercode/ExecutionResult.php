<?php
namespace backendless\core\servercode;

class ExecutionResult
{
    
    private $result;
    private $exception_wrapper;

    public function __construct( $result = null, $exception_wrapper = null ) {

        if( $result != null ) {

            $this->result = $result;

        }

        if( $exception_wrapper != null ) {

            $this->exception_wrapper = $exception_wrapper;

        }

    }
  
    public function getException() {

        return $this->exception_wrapper;

    }

    public function getResult() {

        return $this->result;

    }

    public function setResult( $result ) {

        $this->result = $result;

    }

    public function setException( $exception ) {

        $this->exception_wrapper = $exception;

    }
  
}
