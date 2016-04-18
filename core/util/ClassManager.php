<?php
namespace backendless\core\util;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use backendless\core\lib\Log;
use backendless\core\lib\Autoload;
use backendless\Backendless;
use backendless\core\commons\exception\CodeRunnerException;

//class using for including user classes from folder "classes" in cloud and local mode, 
//also helper for creating class instances, and keep track of what classes have been included and which are not

class ClassManager
{
    
    protected static $included = [];
    protected static $classes_holder = [];
    protected static $is_analyzed = false;


    public static function analyze( $path_to_folder, $map_calsses = true ) { 
        
        //Log::writeInfo( "ClassManager start analyze classes", "file" );
        
        if( $path_to_folder == false) { 
            
            throw new CodeRunnerException( "Wrong path to code source" );
            
        }
        
        $all_files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path_to_folder ) );
        $php_files = new RegexIterator($all_files, '/\.php$/');
        
        $classes = [];
        $namespaces_path = [];
        
        foreach ( $php_files as $php_file) {
            
            $class_info = [];
            
            $class_info['class_name'] = basename( $php_file->getRealPath(), ".php" );
            
            $class_info['namespace'] = self::getNamespace( file_get_contents( $php_file->getRealPath() ) );
            
            if( $class_info['namespace'] != '' ) {
                
                $class_info['full_name'] = $class_info['namespace'] . '\\' . $class_info['class_name'];
                
            } else {
                
                $class_info['full_name'] = $class_info['class_name'];
                
            }
            
            $class_info['path'] = $php_file->getRealPath();
            
            self::putToHolder( $class_info, 'class_name' );
            
            $namespaces_path[ $class_info[ 'namespace' ] ] = pathinfo( $class_info[ 'path' ] )[ 'dirname' ]; //key = namespace key = path to folder;
            
            $classes[ ] = [ 'name' => $class_info[ 'class_name' ], 'namespace' => $class_info[ 'namespace' ] . "\\" . $class_info[ 'class_name' ] ];
             
        }
        
        foreach ( $namespaces_path as $namespace=>$path ) {
        
            Autoload::addNamespace( $namespace, $path ); // add autoloading for user classes
                        
        }
        
        if( $map_calsses == true ) {
            
            Backendless::ignoreMapException();
        
            foreach ( $classes as $class ) {
                
                Backendless::mapTableToClass( $class[ 'name' ], $class[ 'namespace' ] ); // set mapping for SDK.
                
            }
                
        }
        
        self::$is_analyzed = true;
        
    }
        
    private static function getNamespace( $code ) {
        
        if ( preg_match('/^(.*)?namespace(.*?);$/m', $code, $matches ) ) {
            
            if( !isset( $matches[2] ) ) {
                return '';
            }
            
            return trim($matches[2]);
            
            
            
        }
        
        return '';
        
    }
            
    private static function getInfoAboutClass( $code ) {

        $class_info = []; 
        
        // ^(.*)?(namespace(.*?);)(.*?)?class(.*)extends(.*?){{1}|$  - 3620
        //^(.*)?(namespace(.*?);)(.*?)class(.*?)(extends(.*?){{1}|{{1})|$        
        ///^(.*)?(namespace(.*?);)(.*?)class(.*?)(extends(.*?){{1}|{{1})|$/s
        
        if ( preg_match('/^(.*)?(namespace(.*?);)(.*?\/?\*?\*?.*\*?\/?)class(.*?)(extends(.*?){{1}|{{1})|$/s', $code, $matches ) ) {
        
            if( !isset( $matches[2] ) ) {
                return null;
            }
            
            $namespace = explode(" ", rtrim( trim( $matches[2] ) , ";" ) );
            $class_info['namespace'] = $namespace[1];   
            $class_info['class_name'] = trim( $matches[5] );
            
            if( isset( $matches[7] ) ) {
                
                $class_info['parent_class'] = trim( $matches[7] );

                $tmp = explode("\\", $matches[7]);
                $count_elements = count($tmp);

                if( $count_elements > 1 ) {
                    $class_info['parent_class_short_name'] = trim( $tmp[$count_elements-1] );
                } else{
                    $class_info['parent_class_short_name'] = $class_info['parent_class'];
                }
                
            }
            
        } else {
            
            return null;
            
        }
        
        return $class_info;
        
        
    }
    
    private static function getParentClass( $code ) {
        
        $parent_class = [];

        if ( preg_match('/^(.*)?(namespace(.*?);)(.*?\/?\*?\*?.*\*?\/?)class(.*?)(extends(.*?){{1}|{{1})|$/s', $code, $matches ) ) {

            if( isset( $matches[7] ) ) {

                $parent_class['short_name'] = trim( $matches[7] );

                $tmp = explode("\\", $matches[7]);
                $count_elements = count($tmp);

                if( $count_elements > 1 ) {
                    
                    $parent_class['name'] = $parent_class['short_name'];
                    $parent_class['short_name'] = trim( $tmp[$count_elements-1] );
                    
                } else {
                    
                     $parent_class['name'] = $parent_class['short_name'];
                }
            }

        } else {

            return null;

        }

        return $parent_class;

    }
    
    protected static function putToHolder( $class_info, $key ) {
        
        if( !isset( self::$classes_holder[ $class_info[ $key ] ] ) ) {
            
            self::$classes_holder[ $class_info[ $key ] ] = $class_info;
                        
        } else {
            
            Log::writeError("Folder with user classes contains duplicate declaration of class : \"". $class_info[ $key ]. "\".");
            
            exit();
            
        }
    }
    
    public static function getFullClassName( $class_name ) {
        
        $name_parts = explode("\\", $class_name);
        $count_elements = count( $name_parts );
        
        if( $count_elements > 1 ) {

            return $class_name;
            
        }
        
        if( array_key_exists( $class_name, self::$classes_holder ) ) {
            
            return self::$classes_holder[$class_name]["namespace"] . "\\" . $class_name;
            
        }
        
        return null;
        
    }
    
    public static function getClassInstanceByName( $class_name, $construc_data = null ) {
        
        $full_class_name = self::getFullClassName( $class_name );
        
        if( $construc_data == null ) {
            
            return new $full_class_name();
            
        } else {
            
            return new $full_class_name( $construc_data );
            
        }
        
    }
    
    public static function getPathByName( $calss_name) {
        
        if( isset( self::$classes_holder[ $calss_name ]) ) {
            
            return self::$classes_holder[ $calss_name ]['path'];
            
        } else {
            
            return null;
            
        }
        
    }
    
    public static function isAnalyzed() {
        
        return self::$is_analyzed;
                
    }
    
    public static function debug(){  var_dump( self::$classes_holder ); }
    
}   
