<?php
namespace backendless\core\commons\model;


class HostedCollection {

    protected  $debuggable_hosted_models = [];
    
    public function addModel( $model ) {
        
        $this->debuggable_hosted_models[ ] = $model;
        
    }
    
    public function putModels( $models ) {
        
        $this->debuggable_hosted_models = $models;
        
    }
    
    public function getCountsOfModels( ) {
        
        return count( $this->debuggable_hosted_models );
        
    }
    
    public function getCountOfEvents() {
        
        $events_count = 0;
        
        foreach ( $this->debuggable_hosted_models as $model ) {
            
            $events_count += $model->getCountOfEvents();
            
        }
        
        return $events_count;
                
    }
    
     public function __toString() {

        return "HostedServicesModel{ hosted services=" . $this->getCountsOfModels() . " ,hosted services events=" . $this->getCountOfEvents() . ' }';
        
    }
    
    public function getJson() {
        
        $collection_array = [ ];
        
        foreach ( $this->debuggable_hosted_models as $model ) {
            
            $collection_array [] = $model->getAsArray();
            
        }
        
        return json_encode( $collection_array );        
        
    }
    
    public function getFirst() {
        
        if( isset( $this->debuggable_hosted_models[ 0 ] ) ) {
            
            return $this->debuggable_hosted_models[ 0 ];
            
        }
        
        return null;
        
    }
    
}
