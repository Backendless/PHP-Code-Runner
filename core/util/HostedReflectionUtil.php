<?php
namespace backendless\core\util;

use backendless\core\commons\model\BlConfigurationItemDescription;
use backendless\core\parser\typeparser\DefaultTypeParser;
use backendless\core\commons\exception\CodeRunnerException;
use backendless\core\GlobalState;
use backendless\core\processor\ResponderProcessor;
use backendless\core\util\TypeManager;
use backendless\core\Config;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use backendless\core\lib\Log;
use RecursiveCallbackFilterIterator;
use Exception;
use ReflectionClass;
use ReflectionMethod;
use RegexIterator;


class HostedReflectionUtil {

    protected $base_interface_name;
    protected $is_exist_interface;
    
    protected $interface_implementation;
    protected $interface_implementation_info;
    
    protected $classes_holder;
    protected $path_to_classes;
    protected $parsing_error;
    
    protected $used_classes;
    
    protected $path = null;
    protected $config = [];
   
    protected $type_parser;
    
    public function __construct( $path = null, $rai_id = null ) {

        $this->base_interface_name = Config::$CORE["hosted_interface_name"];
        $this->interface_implementation_info = null;
        $this->path_to_classes = [];
        $this->parsing_error = null;
        $this->is_exist_interface = false;
        
        $this->used_classes = [];

        $this->path = $path;
        $this->rai_id = $rai_id;
        
        $this->type_parser = new DefaultTypeParser();
        
    }
    
    public function parseFolderWithCustomCode( ) {
        
        $this->scanDirectory();
        $this->scanCode();
        
        if ( $this->is_exist_interface ) {
            
            $this->dataTypeConvertation();
            
        } 
        
    }
    
    protected function scanDirectory( ) {
        
        $skip_folders = [ ]; // folder can contain libraries which needed user
                
        $files_iterator = new RecursiveIteratorIterator(
                                                         new RecursiveCallbackFilterIterator(
                                                            new RecursiveDirectoryIterator( $this->path ),
                                                            function ( $file_info, $key, $iterator ) use ( $skip_folders ) {
                                                                return $file_info->isFile() || !in_array( $file_info->getBaseName(), $skip_folders );
                                                            }
                                                         )
                                                       );
                                                        
        $php_files = new RegexIterator( $files_iterator, '/\.php$/');
        
        foreach ( $php_files as $php_file) {
            
            $this->path_to_classes[] = $php_file->getRealPath();
            
        }
        
    }
    
    protected function scanCode( ) {
        
        $class_description_list = [];
        
        foreach ( $this->path_to_classes as $index=>$path ) {
            
            $description_item = $this->parseFile( $path );
            
            if( $description_item !== null ) {
                
                $class_description_list[] = $description_item;
                        
            }
            
        }
        
        if ( !$this->is_exist_interface ) {
            
            Log::writeInfo("Not found implementation for '" . Config::$CORE["hosted_interface_name"] . "'", 'file');
            
        } else {
        
            $this->parseServiceImplement( $this->interface_implementation_info );

            foreach ( $class_description_list as $description_item ) {

                $this->parseServiceDataClass( $description_item );

            }

            $this->deleteUnusedClasses();
        }
        
    }
    
    protected function parseFile( $path ) {
        
        $code = file_get_contents( $path );
        
        $matches_class = '';
        
        //check if exist any class in file
        if ( preg_match( '/^(.*?)?class(\s)+(.*?)(\s)?(\n*?)?(\s*){$/m', $code, $matches_class ) ) {
            
            $class_description = [];
            
            $tmp_class_name = explode( " ", $matches_class[3] );
            $class_description["name"] = array_shift( $tmp_class_name );
            $class_description["path"] = $path;
            
            $file_name = basename( $path, ".php" );
            
            if( $file_name != $class_description["name"] ) {
                
                $this->parsing_error["code"] = 3;
                $this->parsing_error["msg"] = "File $file_name.php contains a class " . $class_description["name"] . " that does not match with file name";
            
                Log::writeError("Hosted Service: Not found implementation for '" . Config::$CORE["hosted_interface_name"] . "'", 'file');
                
            }
            
            //parse namespace
            $namespace = [];
            
            preg_match("/^.*namespace\s(.*?);.*$/m", $code, $namespace);
            
            if( isset( $namespace[1]) ) {
                
                $class_description["namespace"] = $namespace[1];
                
            } else {
                
                $class_description["namespace"] = null;
                
            }
            
            $matches_implementation = [];
            
            //check if interface implementation
            
            if( preg_match( '/^.*implements(\s*)(.*?)(' . $this->base_interface_name . ')(.*)$/m', $matches_class[3], $matches_implementation) ) {
               
                if( $this->is_exist_interface == true ) {

                    throw new CodeRunnerException( "Multiple services has been found. Currently only one service per project is allowed. Please make sure there is only service in the project and try again." );
                
                }
                
                $this->is_exist_interface = true;
                $this->interface_implementation_info = $class_description;
                
                return null;
                
            } else {
                
                return $class_description;
                
            }
                
        } else {
            
            Log::writeInfo("Hosted Service persing: skip file " . $path , 'file');
            
            return null;
            
        }
        
    }
    
    private function parseServiceImplement( $class_description ) {
        
        include $class_description["path"];
        
        $full_class_name = ( $class_description['namespace'] != null ) ? "\\" . $class_description['namespace'] . "\\" . $class_description["name"] : "\\" . $class_description["name"];
        
        $reflector = new ReflectionClass( $full_class_name );
        
        $methods = $reflector->getMethods( ReflectionMethod::IS_PUBLIC ); //only public methods

        $class_description['fullname'] = implode( '\\', [ $class_description['namespace'], $class_description['name'] ] );
        $class_description['endpointURL'] = Config::$CORE['hosted_service']['endpoint_url'];
        
        $interface_implementation = [ 'class_description' => $class_description, 'methods' =>[] ];
        
        try {
        
            foreach ( $methods as $method ) {

                $interface_implementation['methods'][] = $this->type_parser->parseServiceMethod( $method );

            }

            if( $this->type_parser->isError() ) {

                $this->handleError( $this->type_parser->getError() );

            }
        
        } catch ( Exception $e ) { 
            
            $error = [];
            
            if( preg_match('/^Class (.*)? does not exist$/', $e->getMessage(), $matches ) ) {
            
                $error["code"] = 22;
                $error["msg"] = $e->getMessage() . ". Class $matches[1] don't declared or missing including file with class.";
                
            } else {
                
                $error["code"] = '';
                $error["msg"] = $e->getMessage();
                
            }
                        
            $this->handleError( $error );
        
        }
        
        $this->parseConfig( $interface_implementation["class_description"] );
        
        $this->interface_implementation = $interface_implementation;
        
    }

    private function deleteUnusedClasses() {

        $used_types = $this->type_parser->getListOfUsedTypes();

        foreach ( $this->classes_holder as $key => $class_definition ) {

            if( !in_array( $class_definition['fullname'], $used_types ) ) {

                unset( $this->classes_holder[ $key ] );
            }

        }

        $this->classes_holder = array_values( $this->classes_holder ); // re-index array
        
    }
   
    private function parseServiceDataClass( $class_description ) {
        
        $full_class_name = ( $class_description['namespace'] != null ) ? "\\" . $class_description['namespace'] . "\\" . $class_description["name"] : "\\" . $class_description["name"];

        if( ! class_exists( $full_class_name ) ) {
            
            include $class_description["path"];
            
        }
        
        $props = ( new ReflectionClass( $full_class_name ) )->getProperties();
        
        $class_description['fullname'] = trim( $full_class_name, "\\" );
        $class_description['field'] = $this->type_parser->parseDataModel( ( new ReflectionClass( $full_class_name ) )->getProperties() );
        
        if( $this->type_parser->isError() ) {

            $this->handleError( $this->type_parser->getError() );
                
        }
        
        $this->classes_holder[] = $class_description;
        
    }
    
    public function getParsedData() {
        
        return ["datatype" => $this->classes_holder, "service" => $this->interface_implementation ];
        
    }
    
//  private function parseAnnotation( ){
//        
//  }
    
    private function parseConfig( &$class_description ) {
        
        $props = ( new ReflectionClass( $class_description[ 'fullname' ] ) )->getProperties();
        
        $instance = new $class_description[ 'fullname' ];
        
        foreach ( $props as $prop ) {
            
            $php_doc = $prop->getDocComment();
            
            $matches = [];
            
            if( preg_match( '/(.*)?@BackendlessConfig(.*)({.*})(.*)?/', $php_doc , $matches ) ){
                
                $prop->setAccessible( true );
                $this->config[ ] = new BlConfigurationItemDescription( json_decode( $matches[ 3 ], true ), $prop->getName(), $prop->getValue( $instance ) );
                
                if( json_last_error() != 0 ) {
                    
                    $this->handleError( $error = [ "msg" => "Invalid configuration JSON: '" . $matches[ 3 ] . "'", "code" => ''  ] );
                    
                }

            }
            
        }
        
    }
    
    public function getConfigListAsArray() {
        
        $list = [];
        
        foreach ( $this->config as $conf_item ) {
            
            $list[] = $conf_item->getAsArray(); 
            
        }
        
        return $list;
        
    }
    
    public function getError() {
        
        return $this->parsing_error;
        
    }
    
    public function isError() {
        
        if( $this->parsing_error === null ) {
            
            return false;
            
        }
        
        return true;
        
    }
    
    private function dataTypeConvertation() {
        
        $type_manager = new TypeManager();
        
        foreach ( $this->interface_implementation['methods'] as $method_index => $val ) {
            
            foreach ( $this->interface_implementation['methods'][$method_index]['arg'] as $arg_index=>$arg_val ) {
                
                $type_manager->prepareTypesForXML( $this->interface_implementation['methods'][$method_index]['arg'][$arg_index] );
                
            }
            
            if( isset($this->interface_implementation['methods'][$method_index]['return_type'] ) ) {
                
                $type_descriptiopn = [ 'type' => $this->interface_implementation['methods'][$method_index]['return_type'] ];

                $type_manager->prepareTypesForXML( $type_descriptiopn );

                $this->interface_implementation['methods'][$method_index]['return_type'] = $type_descriptiopn;
            }
            
        }
        

        foreach ( $this->classes_holder as $index=>$val ) {

            if( isset( $val['field'] ) ) {
                
                foreach ( $this->classes_holder[$index]['field'] as $prop_index=>$prop_val ) {

                    $type_manager->prepareTypesForXML( $this->classes_holder[$index]['field'][$prop_index] );

                }
                
            }
            
        }        
        
    }
    
    private function handleError( $error ) {
        
        if ( GlobalState::$TYPE == 'CLOUD' ) {

            ResponderProcessor::sendResult( $this->rai_id, $error );

        }

        throw new CodeRunnerException( $error["msg"] );
            
    }
    
}
