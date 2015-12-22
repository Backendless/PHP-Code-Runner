<?php
namespace backendless\core\commons;

abstract class AbstractRequest
{
    protected $id;
    protected $application_id;
    protected $app_version_id;    
    protected $timestamp;
    protected $timeout = 5000;
    protected $relative_path;
  
    public function __construct() {

        $this->timestamp = time() * 1000; // seconds convert to millis;

    }

    public function getId() {
        
      return $this->id;
      
    }

    public function setId( $id ) {
        
        $this->id = $id;
        return $this;
        
    }

    public function getApplicationId() {
    
      return $this->application_id;
      
    }

    public function setApplicationId( $application_id ) {
    
      $this->application_id = $application_id;
      return $this;
      
    }

    public function getAppVersionId() {
    
      return $this->app_version_id;
      
    }

    public function setAppVersionId( $app_version_id ) {
    
      $this->app_version_id = $app_version_id;
      return $this;
      
    }

    public function getTimestamp() {
    
      return $this->timestamp;
      
    }

    public function setTimestamp( $timestamp ) {
    
      $this->timestamp = $timestamp;
      return $this;
      
    }
    
    public function getTimeout() {
        
        return $this->timeout;
        
    }

    public function setTimeout( $timeout ) {
        
        $this->timeout = $timeout;
        return $this;
        
    }

    public function getRelativePath() {

        return $this->relative_path;
        
    }

    public function setRelativePath( $relative_path ) {
        
        $this->relative_path = $relative_path;
        return $this;
      
    }
  
}
