<?php

namespace backendless\core\commons\exception;

use Exception;

class CodeRunnerException extends Exception {
  
  
  public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
  }
  
  public function setCode( $code ) {
    $this->code = $code;
  }

  public function setMessage( $message ) {
    $this->message = $message;
  }
  

  public function __toString() {
      return "CodeRunnerException:" . "message=\"" . $this->message . "\" code= " . $this->code ;
  }

}