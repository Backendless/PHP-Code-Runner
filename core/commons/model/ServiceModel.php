<?php
namespace backendless\core\commons\model;

use backendless\core\Config;

class ServiceModel {

    protected $name;
    protected $version;
    protected $description;
    protected $xml;
    protected $config;
    protected $count_of_events;

    public function __construct() {  }

    public function setName( $name ) {
        
        $this->name = $name;
        return $this;
        
    }
    
    public function setVersion( $version ) {
        
        $this->version = $version;
        return $this;
        
    }
    
    public function setDescription( $description ) {
        
        $this->description = $description;
        return $this;
        
    }
    
    public function setXML( $xml ) {
        
        $this->xml = $xml;
        return $this;
        
    }
    
    public function setConfig( $config ) {
        
        $this->config = $config;
        return $this;
        
    }
    
    public function getXML() {
        
        return $this->xml;
        
    }
    
    public function getCountOfEvents() {
        
        return $this->count_of_events;
        
    }

    public function getAsArray() {
        
        $model = [ '___jsonclass' => Config::$CORE[ 'hosted_model' ] ];
        
        $model = array_merge( $model, get_object_vars( $this ) );
                
        unset( $model[ 'count_of_events' ] );
            
        return $model;
    }

}
