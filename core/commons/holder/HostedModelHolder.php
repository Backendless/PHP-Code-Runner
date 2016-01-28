<?php
namespace backendless\core\commons\holder;

class HostedModelHolder {

    protected static $model = null;
    protected static $xml_model = null;
    
    public static function setXMLModel( $xml ) {
        
        self::$xml_model = $xml;
        
    }
    
    public static function getXMLModel( ) {
        
        return self::$xml_model;
        
    }
    
    public static function setModel( $model ) {
        
        self::$model = $model;
        
    }
    
    public static function getModel( ) {
        
        return self::$model;
        
    }
       
    
}
