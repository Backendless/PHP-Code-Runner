<?php
namespace backendless\core\parser;

use backendless\core\processor\ResponderProcessor;
use backendless\core\Config;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use backendless\core\lib\Log;
use RecursiveCallbackFilterIterator;
use Exception;
use ReflectionProperty;
use ReflectionClass;
use ReflectionMethod;
use RegexIterator;



class HostedServiceParser {

    protected $base_interface_name;
    
    protected $interface_implementation;
    protected $interface_implementation_info;
    
    protected $classes_holder;
    protected $path_to_classes;
    protected $parsing_error;
    
    protected $used_classes;
    
    protected $path = null;
    protected $rai_id;

    protected $is_exist_interface;
    

    public function __construct( $path , $rai_id) {
        
        $this->base_interface_name = Config::$CORE["hosted_interface_name"];
        $this->interface_implementation_info = null;
        $this->path_to_classes = [];
        $this->parsing_error = null;
        $this->is_exist_interface = false;
        
        $this->used_classes = [];
        
        $this->path = $path;
        $this->rai_id = $rai_id;
        
    }
    
    public function parseFolderWithCustomCode( ) {
        
        $this->scanDirectory();
        $this->scanCode();
        
    }
    
    protected function scanDirectory( ) {
        
        $skip_folders = [ 'lib' ]; // lib folder can contain libraries which needed user
                
        $files_iterator = new RecursiveIteratorIterator(
                                                        new RecursiveCallbackFilterIterator(
                                                            new RecursiveDirectoryIterator(
                                                                $this->path
                                                            ),
                                                            function ( $fileInfo, $key, $iterator ) use ( $skip_folders ) {
                                                                        return $fileInfo->isFile() || !in_array( $fileInfo->getBaseName(), $skip_folders );
                                                            }
                                                        )
                                                       );
                                                        
        $php_files = new RegexIterator( $files_iterator, '/\.php$/');
        
        foreach ( $php_files as $php_file) {
            
            $this->path_to_classes[] = $php_file->getRealPath();
            
        }
        
   
        if( count( $this->path_to_classes) <= 0 ) {
            
            $this->parsing_error["code"] = 1;
            $this->parsing_error["msg"] = "Not found any files for parsing.";
            
            Log::writeError("Hosted Service: Not found any files for parsing.", 'file');
            
            return;
            
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
        
        $this->parseServiceImplement( $this->interface_implementation_info );
        
        foreach ( $class_description_list as $description_item ) {
        
            $this->parseServiceDataClass( $description_item );
            
        }
        
        $this->deleteUnusedClasses();
        
        if( $this->is_exist_interface === false ) {
            
            $this->parsing_error["code"] = 2;
            $this->parsing_error["msg"] = "Not found implementation for '" . Config::$CORE["hosted_interface_name"] . "'";
            
            Log::writeError("Hosted Service: Not found implementation for '" . Config::$CORE["hosted_interface_name"] . "'", 'file');
            
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
        
        $reflection_property = $reflector->getProperty('_mapping');
        $reflection_property->setAccessible( true );
        $mapping_rules = $reflection_property->getValue( new $full_class_name() );
        
        $methods_description = [ 'class_description' => $class_description, 'methods' =>[] ];
        $method_info = [];
        $args_info = [];
        
        try {
        
            foreach ( $methods as $method ) {

                $method_info['name'] = $method->getName();
                $params = $method->getParameters();
                $info = [];

                foreach ( $params as $param ) {

                    $info['name'] = $param->getName();

                    if( $param->getClass() !== null ) {

                        $info['type'] = $param->getClass()->name; // set type of class from code definition
                        $this->addClassToUsedList( $param->getClass()->name );

                    } else {

                        if( isset( $mapping_rules[ $method->getName() ] ) ) {  // set type of class from mapping definition

                            if( isset( $mapping_rules[ $method->getName() ][ $param->getName() ] ) ) {

                                $info['type'] = $mapping_rules[ $method->getName() ][ $param->getName() ];  

                                $this->addClassToUsedList( $mapping_rules[ $method->getName() ][ $param->getName() ] );

                            } else {

                                $info['type'] = '';

                            }

                        } else {

                            $info['type'] = '';

                        }

                    }

                    $args_info[] = $info;

                    $info = [];

                }

                $method_info['arg'] = $args_info; 
                $args_info = [];

                $methods_description['methods'][] = $method_info;
                unset( $method_info );

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
                        
            ResponderProcessor::sendResult( $this->rai_id, $error );
                
            throw new Exception( $error["msg"] );
        
        }
        
        $this->interface_implementation = $methods_description;
        
    }

    private function addClassToUsedList( $class_full_name ) {
        
        //TODO; // Also add related classes of relation if relation mapping will be approved
        
        if( ! in_array( $class_full_name, $this->used_classes) ) { // fix duplicate classes
            
            $this->used_classes[ ] =  $class_full_name;
            
        }
       
    }
    
    private function deleteUnusedClasses() {
        
        foreach ( $this->classes_holder as $key => $class_definition ) {
            
            if( !in_array( $class_definition['fullname'], $this->used_classes ) ) {
                
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

        $props_array = [];
        
        foreach ( $props as $prop ) {
            
            $props_array[]['name'] = $prop->getName();

        } 
        
        $class_description['fullname'] = trim( $full_class_name, "\\" );
        $class_description['field'] =   $props_array;
        
        $this->classes_holder[] = $class_description;
        
    }
    
    public function getParsedData() {
        
        return ["datatype" => $this->classes_holder, "service" => $this->interface_implementation ];
        
    }
   
//  private function parseAnnotation( ){
//        
//  }
    
    public function getError() {
        
        return $this->parsing_error;
        
    }
    
    public function isError() {
        
        if( $this->parsing_error === null ) {
            
            return false;
            
        }
        
        return true;
        
    }
    
}
