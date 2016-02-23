<?php
namespace backendless\core\parser\typeparser;

abstract class TypeParser {
    
    protected $used_types = [];
    protected $parsed_types = [];    
    protected $parsed_method_return_type = '';
    protected $type_parser_error = null;
    
    abstract public function parseServiceMethod( $method );
    abstract public function parseDataModel( $properties_list );
    
    
    protected function addTypeToUsedList( $type ) {
        
        $type = str_replace( "[]", '', $type );
        
        if( !in_array( $type, $this->used_types) ) {
            
            if( $type != '') {
                
                $this->used_types[] = $type;
                
            }
            
        }
        
    }
    
    public function getListOfUsedTypes() {
        
        return $this->used_types;
        
    }
    
    protected function getTypeByVarName( $name ) {
        
        //var_dump($this->parsed_types[ $name ]);
    
        if( isset( $this->parsed_types[ $name ] ) ) {
            
            return $this->parsed_types[ $name ];
            
        }
        
        return '';
        
    }
    
    public function getError() {
        
        return $this->type_parser_error;
        
    }
    
    public function isError() {
        
        if( $this->type_parser_error === null ) {
            
            return false;
            
        }
        
        return true;
        
    }
    
}
