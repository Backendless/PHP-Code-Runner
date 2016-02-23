<?php
namespace backendless\core\util;

use backendless\core\util\XmlManager;
use backendless\core\commons\holder\HostedModelHolder;
use ReflectionClass;


class HostedMapper
{
    
    private $mapping_error;
    protected $service_arg_mapping;
    protected $data_model_properties;
    protected $scalar_types = [ 'boolean', 'int', 'integer', 'float', 'string', 'String', 'array', 'Array' ];
    protected $xml_manager;

    public function __construct(  ) {
        
        $this->mapping_error = null;
        $this->xml_manager = new XmlManager();
        $this->xml_manager->loadDomtree( HostedModelHolder::getXMLModel() );
                
    }

    public function prepareArguments( &$arguments, $method_name ) {
        
        $method_description = $this->xml_manager->getMethodDescription( $method_name );

        foreach ( $method_description as $index => $arg_item ) {
            
            if( !in_array( $arg_item['type'], $this->scalar_types ) ) {
                
                $this->convertToClass( $arguments[ $index ], $this->getTypeInfo( $arg_item[ 'type' ] ) );
                
            }
            
        }
        
    }
    
    private function convertToClass( &$data, $type ) {
        
        if( ! class_exists( $type['class'] ) ) {

            $this->mapping_error = [
                                        "code" => 10,
                                        "msg" => "Mpping error: class {$type['class']} don't declared or missing including file with class"    
                                ];
            
            return;
            
        }
        
        if( $type['collection'] == true ) { // array of classes
            
            foreach ( $data as $index=>$class_data ) {
                
                $data[ $index ] = $this->convertToClassItem( $class_data, $type['class'] );
                
            }
            
        } else { // single class
            
            $data = $this->convertToClassItem( $data, $type['class'] );
            
        }
        
    }
    
    protected function getTypeInfo( $type ) {

        $type_info = [];

        if( preg_match( '/(.*)(\[\s*\])/', $type, $matches ) ) {

            $type_info['class'] = $matches[1];
            $type_info['collection'] = true;

        } else {

            $type_info['class'] = $type;
            $type_info['collection'] = false;

        }
        
        return $type_info;
        
    }
    
    private function convertToClassItem( $class_data, $class_name ) {
        
        $obj = new $class_name();
        
        $class_description = $this->xml_manager->getClassDescription( $class_name );
        
        $props = ( new ReflectionClass( $obj ) )->getProperties();

        foreach ( $props as $prop) {

            $prop->setAccessible( true );
            
            if( isset( $class_data[ $prop->getName() ] ) ) {
                
                foreach ( $class_description as $index => $type_descr ) {
            
                    if( !in_array( $type_descr['type'], $this->scalar_types ) && $type_descr['name'] == $prop->getName() ) {

                        $this->convertToClass( $class_data[ $prop->getName() ], $this->getTypeInfo( $type_descr[ 'type' ] ) );
                        unset( $class_description[ $index ] );

                    }
            
                }
                
                $prop->setValue( $obj, $class_data[ $prop->getName() ] );
              
            
               unset( $class_data[ $prop->getName() ] );
               
            }
            
        }
        
        $this->setUndeclaredProperties( $class_data, $obj, $class_description );
        
        return $obj;
        
    }
    
    private function setUndeclaredProperties( &$data, &$obj, &$class_description ) {
        
        foreach ( $data as $name => $val ) {
            
            foreach ( $class_description as $index => $type_descr ) {

                if( !in_array( $type_descr['type'], $this->scalar_types ) && $type_descr['name'] == $name ) {

                    $this->convertToClass( $data[ $name ], $this->getTypeInfo( $type_descr[ 'type' ] ) );
                    unset( $class_description[ $index ] );

                }

            }
              
            $obj->{$name} = $data[ $name];

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

            // dynamic declared
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
