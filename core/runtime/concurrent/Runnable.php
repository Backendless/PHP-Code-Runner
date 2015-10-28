<?php
namespace backendless\core\runtime\concurrent;

use backendless\core\lib\Log;


abstract class Runnable
{
    protected $application_id;
    protected $timeout;


    public abstract function runImpl();

    public function getTimeout() {

      return $this->timeout;
      
    }

    public function setTimeout( $timeout ) {

        $this->timeout = $timeout;

    }

    public function getApplicationId() {

        return $this->application_id;

    }

    public function setApplicationId( $application_id ) {

        $this->application_id = $application_id;

    }

    public function cleanUp() {

        $this->application_id = null;
        $this->timeout = null;

    }
  
}
