<?php
namespace backendless\core\util;


class TypeManager {

    
    protected $types_relationship = [    //type => types for xml
                
                                        'boolean'   => [ 
                                                          'javatype' => 'boolean',
                                                          'type' => 'boolean', 
                                                          'nativetype' => 'bool' 
                                                        ],
        
                                        'bool'      => [ 
                                                          'javatype' => 'boolean',
                                                          'type' => 'boolean', 
                                                          'nativetype' => 'bool' 
                                                        ],
        
                                        'integer'   => [ 
                                                          'javatype' => 'int',
                                                          'type' => 'int', 
                                                          'nativetype' => 'int' 
                                                        ],
        
                                        'int'       => [ 
                                                          'javatype' => 'int',
                                                          'type' => 'int', 
                                                          'nativetype' => 'int' 
                                                        ],
        
                                        'float'     => [ 
                                                          'javatype' => 'float',
                                                          'type' => 'float', 
                                                          'nativetype' => 'float' 
                                                        ],
        
                                        'double'    => [ 
                                                          'javatype' => 'double',
                                                          'type' => 'float', 
                                                          'nativetype' => 'float' 
                                                        ],
        
                                        'string'    => [ 
                                                          'javatype' => 'java.lang.String',
                                                          'type' => 'string', 
                                                          'nativetype' => 'string' 
                                                        ],
        
                                        'array'     => [ 
                                                          'javatype' => 'java.util.ArrayList<java.lang.String>',
                                                          'type' => 'array', 
                                                          'nativetype' => 'array' 
                                                        ]
        
                                    ];
    
    public function prepareTypesForXML( &$type_description ) {
        
        if( isset( $this->types_relationship[ $type_description['type'] ] ) ) {
            
            $type_description = array_merge( $type_description, $this->types_relationship[ $type_description['type'] ] );
            
        }elseif( $type_description['type'] == '' ) {
            
            $type_description = array_merge( $type_description, $this->types_relationship[ 'string' ] );
            
        }elseif( preg_match( '/(.*)(\[\s*\])/', $type_description['type'], $matches ) ) {
            
            $type_description['type'] = $matches[1] . '[]';
            $type_description["nativetype"] = "Array";
            $type_description["javatype"] = "java.util.List<" . str_replace("\\", ".", $matches[1] ) . ">";
            $type_description["element_type"] = $matches[1];
            
        }else{
            
            $type_description["nativetype"] = $type_description['type'];
            $type_description["javatype"] = str_replace("\\", ".", $type_description['type'] ) ;
            
        }
        
    }
    
}
