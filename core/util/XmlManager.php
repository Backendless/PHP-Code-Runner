<?php
namespace backendless\core\util;

use DOMDocument;
use DOMXpath;


class XmlManager
{
    
    // tag attributes description (key => val)   key = attr name val = alias from array
    
    private $datatype_tag_attributes =  [ 
                                            'name' => 'name', 
                                            'fullname' => 'fullname', 
                                            'typeNamespace' => 'namespace' 
                                        ];
    
    private $field_tag_attributes = [ 
                                        'name'  =>  'name', 
                                        'type'  =>  'type', 
                                        'fulltype'  =>  'fulltype',
                                        'nativetype'    =>  'nativetype',
                                        'javatype'  =>  'javatype'
                                    ];
    
    private $method_tag_attributes = [
                                        'name'  =>  'name',
                                        'type'  =>  'type',
                                        'neutralname'   =>  'neutralname',
                                        'nativetype'    =>  'nativetype',
                                        'javatype'   =>  'javatype',
                                        'containsvalues'    =>  'containsvalues'
                                      ];
    
    private $arg_tag_attributes =   [
                                        'name' =>   'name',
                                        'type'  =>  'type',
                                        'elementType'   =>  'elementType',
                                        'nativetype'    =>  'nativetype',
                                        'javatype'  =>  'javatype'
                                    ];
    
    private $runtime_tag_attributes =   [
                                            'path'  =>  'path',
                                            'endpoinURL'   =>  'endpointURL',
                                            'serverRootURL' =>  'serverRootURL',
                                            'serverPort'    =>  'serverPort',
                                            'serverName'    =>  'serverName',
                                            'codeFormatType'    =>  'codeFormatType',
                                            'generationMode'    =>  'generationMode',
                                            'randomUUID'    =>  'randomUUID'
                                        ];

    public function buildXml( $data_array, $runtime_vars ) {
        
        $domtree = new DOMDocument('1.0', 'ISO-8859-1');
         
        $domtree->preserveWhiteSpace = false;
        $domtree->formatOutput = true;

        $namespaces = $domtree->createElement("namespaces");
        $root = $domtree->appendChild($namespaces);

        $runtime = $domtree->createElement( "runtime" );
        $root->appendChild( $runtime );

        $this->fillAttributes( $runtime, $this->runtime_tag_attributes, $runtime_vars );

        foreach ( $data_array["datatype"] as $class_info ) {

            $this->addNodeByNamespace( $this->builDatatypeNode( $class_info, $domtree ), $domtree, $root,  $class_info['namespace'] );
            
        }
        
        $this->addNodeByNamespace( $this->builServiseNode( $data_array['service']['methods'], $domtree ), $domtree, $root,  $data_array['service']['class_description']['namespace'] );
        
        return $domtree->saveXML();
        
    }
    
    private function AddNodeByNamespace( $node, $domtree, $root, &$namespace ) {
        
        $namespace_parts = explode( "\\", $namespace );
        
        $xpath = new DOMXpath( $domtree );

        $full_name_space = '' ;
        
        $position_counter = 0;
        $last_position = count( $namespace_parts);
                
        foreach ( $namespace_parts as $n_part ) {

            $full_name_space .= ( $n_part != '' )? '\\' . $n_part : '';
            $position_counter++;
                    
            $search_node = $xpath->query( '//namespace[@fullname="'. $full_name_space .'"]' );
            
            if( $search_node->length == 0 ) {
                
                $root = $root->appendChild( $this->createNamespaceTag( $n_part, $full_name_space, $domtree ) );
                
                if( $position_counter == $last_position ) { // add namespace tag and if last add node
                
                    $root->appendChild( $node ); 
                    return;
                
                }
                
             }
             
            if( $position_counter == $last_position ) { // if last part of namespace add node
                
                $search_node = $xpath->query( '//namespace[@fullname="'. $full_name_space .'"]' );
                $search_node->item(0)->appendChild( $node );
                    
            }

        }
            
    }
    
    private function createNamespaceTag( $n_part, $full_name_space, $domtree ) { 
        
        $namespace_node = $domtree->createElement( "namespace" );
        
        $namespace_node->setAttribute( 'name', $n_part );
        $namespace_node->setAttribute( 'fullname', $full_name_space );
        
        return $namespace_node;
        
    }
    
    private function builDatatypeNode( &$class_info, $domtree ) {
        
        $data_type = $domtree->createElement( "datatype" );
        
        $this->fillAttributes( $data_type, $this->datatype_tag_attributes, $class_info );
        
        
        foreach ( $class_info['field'] as $field_item ){
            
            $field = $domtree->createElement( "field" );

            $this->fillAttributes( $field, $this->field_tag_attributes, $field_item );
            

            $data_type->appendChild( $field );
        }
        
        return $data_type;
        
    }
    
    private function builServiseNode( &$methods_info, $domtree ) {
                
        $service_node = $domtree->createElement( "service" );
        
        $methd_node = '';
        $arg_node = '';
        
        foreach ( $methods_info as $method_item ) {
            
            $methd_node = $domtree->createElement( "method" );
            
            $this->fillAttributes( $methd_node, $this->method_tag_attributes, $method_item );
            
            foreach ( $method_item['arg'] as $arg_item ) {
                
                $arg_node = $domtree->createElement( "arg" );
                
                $this->fillAttributes($arg_node, $this->arg_tag_attributes, $arg_item );
                
                $methd_node->appendChild( $arg_node );
            }
            
            $service_node->appendChild( $methd_node );
            
        }
        
        return $service_node;
        
    }
    
    private function fillAttributes( $tag, $attributes_description,  &$data_array , $add_empty = true ) {
        
        
        foreach ( $attributes_description as $attribute_name => $index_in_data ) {
            
            if( isset( $data_array[ $index_in_data ] )  ) {
                
                $tag->setAttribute( $attribute_name, $data_array[ $index_in_data ] );
                
            }elseif ( $add_empty ) {
                
                $tag->setAttribute( $attribute_name, '' );
                
            }
            
        }
        
    }
    
    public function getMethodDescription( $path_to_xml, $method_name ) {
        
        $domtree = new DOMDocument('1.0', 'ISO-8859-1');
        $domtree->load( $path_to_xml );
        
        $xpath = new DOMXpath( $domtree );
        
        $method_node = $xpath->query( '//service //method[@name="'. $method_name .'"]' );
        
        $description = [];
        
        if ( $method_node->item(0)->hasChildNodes() ) {
                
                $childs = $method_node->item(0)->childNodes;

                foreach( $childs as $item ) {
                    
                    if( is_a( $item, "DOMElement") ) { 
                                                
                        $array_item = [];
                        
                        $array_item[ 'name' ] = $item->getAttribute( 'name' );
                        $array_item[ 'type' ] = $item->getAttribute( 'type' );
                        
                        
                        $description[] = $array_item;
                        
                    }
                    
                }
            
        }
        
        return $description;
        
    }

}