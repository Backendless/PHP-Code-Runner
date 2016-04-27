<?php
namespace backendless\core\commons\model;

use ReflectionClass;


class DebuggableHostedModel
{
    private $application_id;
    private $app_version_id;
    
    private $datatype;
    private $service;
    
    protected $id;
    protected $name;
    protected $lang = 'PHP';
    protected $description;
    protected $update_notes;
    protected $version;
    protected $is_in_debug;
    protected $internal_only;
    protected $free_access_to_api;
    protected $deployment_scope = 'LOCAL';
    protected $configuration;
    protected $xml_description;
    
    public function __construct() {
        
    }

    public function getApplicationId() {
        
        return $this->app_version_id;
      
    }

    public function setApplicationId( $application_id ) {
        
        $this->application_id = $application_id;
        $this->id = $application_id;
        return $this;
        
    }

    public function getAppVersionId() {
        
        return $this->app_version_id;
        
    }
    
    public function setAppVersionId( $app_version_id ) {
        
        $this->app_version_id = $app_version_id;
        
    }
    
    public function setData( $service, $datatypes ) {
        
        $this->service = $service;
        $this->datatype = $datatypes;
        return $this;
    
    }
    
    public function getCountOfEvents() {
        
        return count( $this->service[ 'methods' ] );
        
    }
    
    public function setXML( $xml ) {
        
        $this->xml_description = $xml;
        return $this;
        
    }
    
    public function getXml(){
        
        return $this->xml_description;
        
    }
    
    public function __toString() {

        return "HostedModel{ hosted service events=" . $this->getCountOfEvents() . ' }';
        
    }
    
    public function getJson() {
        
        return json_encode( $this->getAsArray() );        
        
    }
    
    public function getAsArray() {
        
        $keys_ratio = [
                            "update_notes"          =>  "updateNotes",
                            "is_in_debug"           =>  "isInDebug",
                            "internal_only"         =>  "internalOnly",
                            "free_access_to_api"    =>  "freeAccessToApi",
                            "deployment_scope"      =>  "deploymentScope",
                            "xml_description"       =>  "xmlDescription",
                      ];
        
        $exceptions = [
                            "application_id",
                            "app_version_id",
                            "datatype",
                            "service",
                            "is_in_debug",
                       ];
        
        $data_array = [ ];          
        
        
        $reflection = new ReflectionClass( $this );
        $props = $reflection->getProperties();

        foreach ( $props as $prop ) {

            $prop->setAccessible( true );
            
            if ( in_array( $prop->getName(),  $exceptions ) ) {
                
                continue;
                
            }
            
            $json_key = '';
            
            if( isset( $keys_ratio[ $prop->getName() ] ) ) {
                
                $json_key = $keys_ratio[ $prop->getName() ];
                
            } else {
                
                $json_key = $prop->getName();
                
            }
            
            $data_array[ $json_key ] =  $prop->getValue( $this );
            
        }
        
        return $data_array;
        
    }
    
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
    
    public function setConfig( $config ) {
        
        $this->configuration = $config;
        return $this;
        
    }
          
}
