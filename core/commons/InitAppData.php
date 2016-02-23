<?php
namespace backendless\core\commons;

class InitAppData {
    
  private $secret_key;
  private $app_version_name;
  private $url;

  public function __construct( $data_array = null ) {
    
    if( $data_array != null ) {
    
        $this->secret_key = $data_array[ 'secretKey' ];
        $this->app_version_name = $data_array[ 'appVersionName' ];
        $this->url = $data_array['url'];
        
    }
    
  }

  public function getSecretKey() {
    
      return $this->secret_key;
              
  }

  public function getAppVersionName() {
    
      return $this->app_version_name;
      
  }

  public function getUrl() {
      
    return $this->url;
    
  }
  
}