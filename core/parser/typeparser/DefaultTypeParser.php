<?php
namespace backendless\core\parser\typeparser;

use backendless\core\parser\typeparser\TypeParser;

class DefaultTypeParser extends TypeParser {
    
    public function parseServiceMethod( $method ) {
        
        $this->reset();
        $method_info = [];
        
        $method_info['name'] = $method->getName();
        
        $this->parsePHPDoc( $method->getDocComment(), 'param' );

        $arguments_info = [];
        
        $params = $method->getParameters();
        
        foreach ( $params as $param ) {

            $arguments_info['name'] = $param->getName();
            
            if( $param->isOptional() ) {
                
                $arguments_info['default_value'] = $param->getDefaultValue();
                
            }

            $arguments_info['type'] = $this->getTypeByVarName( $param->getName() );
            
            //  get type (class) from method signature if php version < 7              
            if( $param->getClass() !== null ) {

                $arguments_info['type'] = $param->getClass()->name; // set type of class from code definition
                
            }
            
//            get type if php version >= 7              
//            
//            if( $param->hasType() ) {
//                
//                $arguments_info['type'] = $param->getType();
//                
//            }            

              $this->addTypeToUsedList( $arguments_info['type'] ); 
              $method_info['arg'][] = $arguments_info;
        }
        
        if( $this->parsed_method_return_type != null ) {
            
            $method_info['return_type'] = $this->parsed_method_return_type;
        
        }
        

        return $method_info;
        
    }
    
    private function reset() {
        
        $this->parsed_method_return_type = null;
        $this->parsed_types = [];
        
    }
    
    public function parseDataModel( $properties_list ) {
        
        $prop_types = [];
        $this->reset();
        
        foreach ( $properties_list as $prop ) {
            
            $php_doc = $prop->getDocComment();
            $this->parsePHPDoc( $php_doc, "var" );
            
        }
        
        foreach ( $properties_list as $prop ) {
            
            $property_info = [];
            
            $property_info['name'] = $prop->getName();
            $property_info['type'] = $this->getTypeByVarName( $prop->getName() );
            $this->addTypeToUsedList( $property_info['type'] );
            
            $prop_types[] = $property_info;
            
        }
        
        return $prop_types;
        
    }

    private function parsePHPDoc( $php_doc, $index ) {
        
        if ( $php_doc !== false ) {
            
            $result = $this->slicePHPDoc( $php_doc ); 
            
            if( isset( $result['return'][0] ) ) {
                
                $this->parsed_method_return_type = trim( $result['return'][0] );
                
            } else {
                
                $this->parsed_method_return_type = '';
                
            }
            
            foreach ( $result[ $index ] as $arg_item ) {
                
                $parts = preg_split( '/\s++/', trim( $arg_item ) ); 
                
                $part_one = array_shift( $parts );
                $part_two = array_shift( $parts );
                
                if ( $part_one == "" || $part_one == null || $part_two == "" || $part_two == null  ) {
                    
                    $this->type_parser_error["code"] = 41;
                    $this->type_parser_error["msg"] = 'Wrong format of type declaration line: \'@' . $index . ' ' 
                                                    . $part_one . ' ' . $part_two . ' \'';
                    
                    return false;
                    
                }
                
                if( strpos( $part_one, '$' ) !== false || strpos( $part_two, '$' ) !== false )  {
                    
                    $type_info = [];

                    if( strpos( $part_one, '$' ) !== false ) {
                        
                       $type_info[0] = str_replace( '$', '', $part_one );
                        
                    } else{
                        
                        $type_info[1] = $part_one;
                        
                    }  
                    
                    if( strpos( $part_two, '$' ) !== false ) {

                        $type_info[0] = str_replace( '$', '', $part_two );
                        
                    } else{
                        
                        $type_info[1] = $part_two;
                        
                    }
                    
                    if( count( $type_info ) == 2) {
                        
                        $this->parsed_types[ $type_info[ 0 ] ] = $type_info[1];
                            
                    } else {
                        
                        $this->type_parser_error["code"] = 42;
                        $this->type_parser_error["msg"] = 'Type declaration \'@'. $index . ' ' 
                                                       . $part_one . ' ' . $part_two . '\' can\'t contain  symbol \'$\' twice';
                        return false;                        
                        
                    }

                } else {

                     $this->type_parser_error["code"] = 43;
                     $this->type_parser_error["msg"] = 'Type declaration \'@' . $index .'\'' 
                                                    . $part_one . ' ' . $part_two . '" missing symbold \'$\' for variable name';
                     return false;

                }
                
            }
        
        }
        
    }
    
    private function slicePHPDoc( &$php_doc  ) {
        
        $matches = [];
        $result = [];

        if( preg_match_all("/.*?@(\w+)(.*)/m", $php_doc, $matches ) ) {

            foreach ( $matches[ 2 ] as $index=>$parsed_item ) {

                $key = $matches[1][$index];
                
                if( ! isset( $result[ $key ] ) ) {
                
                    $result[ $key ] = [];
                    
                }
                
                $result[ $key ][] = $parsed_item;
                
            }

        }
        
        return $result;
        
    }
    
}
