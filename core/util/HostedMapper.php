<?php
namespace backendless\core\util;

use backendless\core\util\XmlManager;
use ReflectionClass;

class HostedMapper
{
    
    private $mapping_error;
    
    public function __construct() {
        
        $this->mapping_error = null;
        
    }


    public function prepareArguments( &$arguments, $path, $app_version_id, $method_name ) {
        
        $xml_path = realpath( $path . DS . ".." ) . DS . $app_version_id . ".xml";
        
        $xml_manager = new XmlManager();
        
        $method_description = $xml_manager->getMethodDescription( $xml_path, $method_name );
        
        foreach ( $method_description as $index => $arg_item ) {
            
            if( $arg_item[ 'type' ] != "" ) {
                
                $arguments[ $index ] = $this->convertToClass( $arguments[ $index ], $arg_item[ 'type' ] );
                
            }
            
        }
        
        
    }
    
    private function convertToClass( $data, $class_name ) {
        
        if( ! class_exists( $class_name ) ) {
            
            $this->mapping_error = [
                                        "code" => 10,
                                        "message" => "Class $class_name don't declared or missing including file with class"    
                                ];
            
            return;
            
        }
        
        if( isset( $data[0] ) ) {
            
            foreach ( $data as $index=>$class_data ) {
                
                $data[ $index ] = $this->convertToClassItem( $class_data, $class_name );
                
            }
            
        } else {
            
            $data = $this->convertToClassItem( $data, $class_name );
            
        }
        
        return $data;
        
    }
    
    private function convertToClassItem( $class_data, $class_name ) {
        
        $obj = new $class_name();
        
        $props = (new ReflectionClass( $obj ) )->getProperties();

        foreach ( $props as $prop) {

            $prop->setAccessible( true );
            
            if( isset( $class_data[ $prop->getName() ] ) ) {
                    
                $prop->setValue( $obj, $class_data[ $prop->getName() ] );
                
                    //in future add logic for classes relation here
                
            }
            
            unset( $class_data[ $prop->getName() ] );
        }
        
        $this->setUndeclaredProperties( $class_data, $obj );
        
        return $obj;
        
    }
    
    protected function setUndeclaredProperties( &$data, &$obj ) {
        
        foreach ( $data as $name => $val ) {

            if( !is_array( $data[ $name ] ) ) {
                    
                $obj->{$name} = $val;
                
            } 
            
            //in future add logic for classes relation here

        }
        
    }
    
    public function getError() {
        
        return $this->mapping_error;
        
    }
    
    public function isError() {
        
        if( $this->mapping_error === null ) {
            
            return false;
            
        }
        
        return true;
        
    }
    
    public function prepareResult( &$result ) {
        
        if( is_array( $result ) ) {
            
            foreach ( $result as $index => $result_data ) {
                
                $result[ $index ] = $this->prepareResultItem( $result_data );
                
            }
            
        } else {
            
            $result = $this->prepareResultItem( $result );
            
        }
        
    }
    
    private function prepareResultItem( $item ) {
        
        if( is_object( $item ) ) {
          
            $data_array = [];

            $reflection = new ReflectionClass( $item );
            $props = $reflection->getProperties();

            foreach ( $props as $prop ) {

                $prop->setAccessible( true );
                $data_array[ $prop->getName() ] =  $prop->getValue( $item );

            }

            // dinamic declared
            $obj_vars = get_object_vars( $item );

            if( isset( $data_array ) && $data_array !== null ) {

                $data_array = array_merge( $data_array, $obj_vars );

            } else {

                $data_array = $obj_vars;

            }
            
            foreach ( $data_array as $data_key => $data_val ) {

                if( gettype( $data_val ) == "object" ) {

                    $data_array[ $data_key ] = $this->prepareResultItem( $data_val );

                } elseif( is_array( $data_val ) ) { // if relation one to many
                
                    foreach ( $data_val as $index => $val  ) {
                    
                        if( gettype( $val ) == "object" ) {

                            $data_array[ $data_key ][ $index ] = $this->prepareResultItem( $val );
                        
                        }
                    }
                
                }
            }
            
            return $data_array;
            
        } elseif( is_array( $item ) ) {
            
            $this->prepareResult( $item );
            return $item;
            
        } else{
            
            return $item;
            
        }
        
    }

}
