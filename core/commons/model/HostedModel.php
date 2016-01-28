<?php
namespace backendless\core\commons\model;

use backendless\core\Config;
use backendless\core\util\XmlManager;

class HostedModel
{
    private $application_id;
    private $app_version_id;
    
    private $datatype;
    private $service;

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
        
    }
    
    public function getCountOfEvents() {
        
        return count( $this->service["methods"] );
        
    }
    
    public function getXML() {
        
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
          
}
