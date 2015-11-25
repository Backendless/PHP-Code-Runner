<?php
namespace backendless\core\parser;

use backendless\core\Config;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use backendless\core\lib\Log;
use RegexIterator;


class HostedServiceParser {

    protected $base_interface_name;
    protected $interface_implementation;
    protected $classes_holder;
    protected $path_to_classes;
    protected $parsing_error;
    
    protected $is_exist_interface;

    public function __construct() {
        
        $this->base_interface_name = Config::$CORE["hosted_interface_name"];
        $this->path_to_classes = [];
        $this->i_base_service = null;
        $this->parsing_error = null;
        $this->is_exist_interface = false;
        
    }
    
    public function parseFolderWithCustomCode( ) {
        
        var_dump("CALL: parseFolderWithCustomCode( )");
        
        $this->scanDirectory();
        $this->scanCode();
        
        //var_dump( $this->path_to_classes );
        
    }
    
    protected function scanDirectory( ) {
        
        //IBackendlessService
        
        $all_files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( "../classes" ) );
        $php_files = new RegexIterator($all_files, '/\.php$/');
        
        foreach ( $php_files as $php_file) {
            
            $this->path_to_classes[] = $php_file->getRealPath();
            
            
//            $class_info = [];
//            
//            //$class_info["parent_class"] = self::getParentClass( file_get_contents( $php_file->getRealPath() ) );
//            
//            $class_info['namespace'] = str_replace( "/", "\\", trim( dirname( substr( $php_file->getRealPath(), strlen( PathBuilder::getClasses() ) ) ), "/" ) );
//            
//            $class_info['class_name'] = basename( $php_file->getRealPath(), ".php" );
//            
//            $class_info['path'] = $php_file->getRealPath();
//            
//            self::putToHolder( $class_info );
//            
//            $classes_namespaces[ $class_info['namespace'] ] = pathinfo($class_info["path"])["dirname"]; //key = namespace key = path to folder;
//            
//            Backendless::mapTableToClass( $class_info['class_name'], $class_info['namespace'] . "\\" . $class_info['class_name'] ); // set mapping for SDK.
            
             
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
            
            $class_describtion = [];
            
            $tmp_class_name = explode( " ", $matches_class[3] );
            $class_describtion["name"] = array_shift( $tmp_class_name );
            $class_describtion["path"] = $path;
            
            $file_name = basename( $path, ".php" );
            
            if( $file_name != $class_describtion["name"] ) {
                
                $this->parsing_error["code"] = 3;
                $this->parsing_error["msg"] = "File $file_name.php contains a class " . $class_describtion["name"] . "  that does not match with file name";
            
                Log::writeError("Hosted Service: Not found implementation for '" . Config::$CORE["hosted_interface_name"] . "'", 'file');
                
            }
            
            $matches_implementation = [];
            
            //check if exist any class in file if interface implementation
            
            if( preg_match( '/^.*implements(\s*)(' . $this->base_interface_name . ')(.*)$/m', $matches_class[3], $matches_implementation) ) {
               
                var_dump($path);
            }
            //var_dump($class_name);
            
            //var_dump($matches);
            
        } else{
            
            var_dump("NOT class ". $path);
            
        }
        
    }
    
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