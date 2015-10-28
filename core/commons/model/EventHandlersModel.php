<?php
namespace backendless\core\commons\model;

use backendless\core\commons\model\EventHandler;
use Exception;

class EventHandlersModel
{
    private $application_id;
    private $app_version_id;
    
    private $handlers;
    
    private $count_event_handlers;
    private $count_timers; 

    public function __construct() {
        
        $this->handlers = [];
        $this->count_event_handlers = 0;
        $this->count_timers = 0;
        
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

    public function getHandlers() {
        
        return $this->handlers;
      
    }

    public function setHandlers( $handlers ) {
        
        $this->handlers = $handlers;
        
    }

    public function getCountEventHandlers() {
        
        return $this->count_event_handlers;
      
    }
    
    public function getCountTimers() {
        
        return $this->count_timers;
        
    }
    
    public function addHandler( $handler ) {
        
        $this->handlers[] = $handler;

        if( $handler->isTimer() ) {
            
            $this->count_timers++;
            
        }else{
            
            $this->count_event_handlers++;
            
        }
        
    }

    public function getEventHandler( $event_id, $target ) {
        
        foreach( $this->handlers as $key => $handler ) {
          
            if( $handler->getId() != $event_id ) {
                continue;
            }

            if( $handler->getTarget() !== $target ) {
                continue;
            }

            return $handler;

        }

        return null;
      
    }

    public function __toString() {

        return "EventModel{ " . "timers=" . $this->count_timers . ", eventHandlers=" . $this->count_event_handlers . ' }';
        
    }
    
    
    public function loadFromJson( $path ) {

        if( file_exists( $path ) ) {
            
            $data_array = json_decode( file_get_contents($path), true );
            
            $this->application_id = $data_array['applicationId'];
            $this->app_version_id = $data_array['appVersionId'];
            
            
            foreach ( $data_array["handlers"] as  $handler_item ) {
                
                $handler = new EventHandler();
                $handler->setId($handler_item['id']);
                $handler->setAsync($handler_item['async']);
                $handler->setTarget($handler_item['target']);
                $handler->setTimer($handler_item['timer']);
                $handler->setProvider($handler_item['provider']);
                
                $this->addHandler( $handler );
                
            }
            
        } else {
            
            throw new Exception( "Event handler model json file absent in path: $path" );
            
        }
        
    }
        
    public function getJson( $pretty_print = false ) {
        
       
        $data = [];
        
        $data["applicationId"] = $this->application_id;
        $data["appVersionId"] = $this->app_version_id;
        $data["handlers"] = [];
        
        foreach ( $this->handlers as $index => $handler) {
            
            $data["handlers"][$index] = $handler->getVars();
            
        }
        
        
        if( $pretty_print ) {
            
            return json_encode( $data, JSON_PRETTY_PRINT );
            
        }
        
       return json_encode( $data );
        
    }
          
}


