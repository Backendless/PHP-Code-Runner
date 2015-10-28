<?php
namespace backendless\core\commons;

abstract class AbstractRequest
{
    
    protected $id;
    protected $application_id;
    protected $relative_path;
    protected $app_version_id;
    protected $timestamp;

    public function __construct() {

        $this->timestamp = time() * 1000; // seconds convert to millis;

    }

    public function getId() {
        
      return $this->id;
      
    }

    public function setId( $id ) {
        
        $this->id = $id;
        
    }

    public function getApplicationId() {
    
      return $this->application_id;
      
    }

    public function setApplicationId( $application_id ) {
    
      $this->application_id = $application_id;
    }

    public function getAppVersionId() {
    
      return $this->app_version_id;
      
    }

    public function setAppVersionId( $app_version_id ) {
    
      $this->app_version_id = $app_version_id;
      
    }

    public function getTimestamp() {
    
      return $this->timestamp;
      
    }

    public function setTimestamp( $timestamp ) {
    
      $this->timestamp = $timestamp;
      
    }
}
