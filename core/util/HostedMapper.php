<?php
namespace backendless\core\util;

use backendless\core\util\XmlManager;

class HostedMapper
{
    
    public function prepareArguments( &$arguments, $path, $app_version_id, $method_name ) {
        
        $xml_path = realpath( $path . DS . ".." ) . DS . $app_version_id . ".xml";
        
        $xml_manager = new XmlManager();
        
        $method_description = $xml_manager->getMethodDescription( $xml_path, $method_name );
        
        //var_dump( $arguments, $xml_path, $method_description );
        
        foreach ( $method_description as $index => $arg_item ) {
            
            if( $arg_item[ 'type' ] != "" ) {
                
                $arguments[ $index ] = $this->convertToClass( $arguments[ $index ], $arg_item[ 'type' ] );
                
            }
            
        }
        
        
    }
    
    private function convertToClass( $data, $class_name ) {
        
        var_dump( $data, $class_name );
        
        $a = new $class_name();
        
    }
    

}