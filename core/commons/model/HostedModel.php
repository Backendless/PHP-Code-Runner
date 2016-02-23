<?php
namespace backendless\core\commons\model;

use backendless\core\Config;
use ReflectionClass;
use backendless\core\util\XmlManager;

class HostedModel
{
    private $application_id;
    private $app_version_id;
    
    private $datatype;
    private $service;
    
    protected $id;
    protected $name;
    protected $description;
    protected $update_notes;
    protected $version;
    protected $is_in_debug;
    protected $internal_only;
    protected $free_access_to_api;
    protected $deployment_scope = "LOCAL";
    protected $configuration;
    private   $xml_description;
    
    public function __construct() {
        
    }

    public function getApplicationId() {
        
        return $this->app_version_id;
      
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
    
    public function setData( $parsed_data ) {
        
        $this->service = $parsed_data[ "service" ];
        $this->datatype = $parsed_data[ "datatype" ];
        $this->xml_description = $this->generateXML();
    
    }
    
    public function getCountOfEvents() {
        
        return count( $this->service["methods"] );
        
    }
    
    public function getXml(){
        
        return $this->xml_description;
        
    }
    
    protected function generateXML() {
        
        $runtime = [

                        //'path'           => $path_to_hosted,
                        'endpointURL'    => Config::$CORE['hosted_service']['endpoint_url'],
                        'serverRootURL'  => Config::$CORE['hosted_service']['server_root_url'],
                        'serverPort'     => Config::$CORE['hosted_service']['server_port'],
                        'serverName'     => Config::$CORE['hosted_service']['server_name'],
                        'codeFormatType' => Config::$CORE['hosted_service']['code_format_type'], 
                        "generationMode" => Config::$CORE['hosted_service']['generation_mode'], 
                        'randomUUID'     => mt_rand( 100000000, PHP_INT_MAX ),

                   ];
            
        $xml_manager = new XmlManager();
        
        return $xml_manager->buildXml( [ "service" => $this->service, "datatype"  => $this->datatype ], $runtime );
        
    }
    
    public function __toString() {

        return "HostedModel{ hosted service events=" . $this->getCountOfEvents() . ' }';
        
    }
    
    public function getJson() {
        
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
                    
        return json_encode( $data_array );        
        
    }
          
}
