<?php
namespace backendless\core\parser;

use backendless\core\Config;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use backendless\core\lib\Log;
use ReflectionClass;

use RegexIterator;


class HostedServiceParser {

    protected $base_interface_name;
    protected $interface_implementation;
    protected $interface_implementation_info;
    protected $classes_holder;
    protected $path_to_classes;
    protected $parsing_error;
    
    protected $is_exist_interface;

    public function __construct() {
        
        $this->base_interface_name = Config::$CORE["hosted_interface_name"];
        $this->interface_implementation_info = null;
        $this->path_to_classes = [];
        $this->i_base_service = null;
        $this->parsing_error = null;
        $this->is_exist_interface = false;
        
    }
    
    public function parseFolderWithCustomCode( ) {
        
        $this->scanDirectory();
        $this->scanCode();
        
    }
    
    protected function scanDirectory( ) {
        
        $all_files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( "../classes" ) );
        $php_files = new RegexIterator($all_files, '/\.php$/');
        
        foreach ( $php_files as $php_file) {
            
            $this->path_to_classes[] = $php_file->getRealPath();
            
        }
        
        if( count( $this->path_to_classes) <=0 ) {
            
            $this->parsing_error["code"] = 1;
            $this->parsing_error["msg"] = "Not found any files for parsing.";
            
            Log::writeError("Hosted Service: Not found any files for parsing.", 'file');
            
            return;
            
        }
    }
    
    protected function scanCode( ) {
        
        foreach ( $this->path_to_classes as $index=>$path ) {
            
            $this->parseFile( $path );
            
        }
        
        $this->parseServiceImplement( $this->interface_implementation_info );
        
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
            
            //check if exist any class in file if interface implementation
            
            if( preg_match( '/^.*implements(\s*)(.*?)(' . $this->base_interface_name . ')(.*)$/m', $matches_class[3], $matches_implementation) ) {
               
                $this->is_exist_interface = true;
                $this->interface_implementation_info = $class_description;
                
            } else {
                
                $this->parseServiceDataClass( $class_description );
            }
                
        } else {
            
            Log::writeInfo("Hosted Service persing: skip file " .$path, 'file');
            
        }
        
    }
    
    private function parseServiceImplement( $class_description ) {
        
        include $class_description["path"];
        
        $full_class_name = ( $class_description['namespace'] != null ) ? "\\" . $class_description['namespace'] . "\\" . $class_description["name"] : "\\" . $class_description["name"];
        
        $reflector = new ReflectionClass( $full_class_name );
        
        $methods = $reflector->getMethods();
        
        $methods_description = [ 'class_description' => $class_description, 'methods' =>[] ];
        $method_info = [];
        $args_info = [];
        
        foreach ( $methods as $method ) {
            
            $method_info['name'] = $method->getName();
            
            $params = $method->getParameters();
            
            $info = [];
            
            foreach ( $params as $param ) {
                
                $info['name'] = $param->getName();
                
                if( $param->getClass() !== null ) {
                    
                    $info['type'] = $param->getClass()->name;
                    
                } else {
                    
                    $info['type'] = '';
                    
                }
                
                $args_info[] = $info;

                $info = [];
                
            }
            
            $method_info['arg'] = $args_info; 
            $args_info = [];
            
            $methods_description['methods'][] = $method_info;
            unset( $method_info );
            
        }
        
        
        $this->interface_implementation = $methods_description;
        
    }
    
    private function parseServiceDataClass( $class_description ){
        
        include $class_description["path"];
        
        $full_class_name = ( $class_description['namespace'] != null ) ? "\\" . $class_description['namespace'] . "\\" . $class_description["name"] : "\\" . $class_description["name"];
        
        $props = ( new ReflectionClass( $full_class_name ) )->getProperties();

        $props_array = [];
        
        foreach ( $props as $prop ) {
            
            $props_array[]['name'] = $prop->getName();

        } 
        
        $class_description['fullname'] = $full_class_name;
        $class_description['field'] =   $props_array;
        
        $this->classes_holder[] = $class_description;

        
    }
    
    public function getParsedData() {
        
        return ["datatype" => $this->classes_holder, "service" => $this->interface_implementation ];
        
    }
   
//  private function parseAnnotation( ){
//        
//  }
    
    public function getErrorAsJson() {
        
        return json_encode( $this->parsing_error );
        
    }
    
    public function isError() {
        
        if( $this->parsing_error === null ) {
            
            return false;
            
        }
        
        return true;
        
    }
    
}