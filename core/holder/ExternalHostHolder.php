<?php
namespace backendless\core\holder;

use backendless\core\Config;


class ExternalHostHolder
{
    private static $instance;
    
    private $allowed_hosts_for_app;
    
    private function __construct() {
        
        $this->allowed_hosts_for_app = [];
        
    }

    public static function getInstance() {

        if( ! self::$instance ) {

            self::$instance = new ExternalHostHolder();
        }

        return self::$instance;

    }


    public function addUrl( $application_id, $url ) {

        $this->setAllowedHostsForApp($application_id);
        $this->allowed_hosts_for_app[ $application_id ][] = trim( $url );

    }
    

    private function setAllowedHostsForApp(  $application_id ) {
        
        if( ! isset ( $this->allowed_hosts_for_app[ $application_id ] ) ) {
            
            $this->allowed_hosts_for_app[$application_id] = Config::$ALLOWED_HOSTS;
            
        }

    }
  
  
    public function setUrls( $application_id, $urls ) {

        $this->setAllowedHostsForApp($application_id);
        
        foreach ($urls as $url) {
            
            $this->allowed_hosts_for_app[$application_id][] = trim($url);
            
        }
        
    }
  
    public function deleteUrls( $application_id, $urls_to_delete ) {

        $this->setAllowedHostsForApp($application_id);

        foreach( $this->allowed_hosts_for_app[$application_id] as $index => $url ){
            
            if (in_array($url, $urls_to_delete ) ) {
                
                unset( $this->allowed_hosts_for_app[$application_id][$index] );
                
            }
            
        }
        
    }
    
 }