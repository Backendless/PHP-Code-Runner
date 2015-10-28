<?php
namespace backendless\core\util;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use backendless\core\util\PathBuilder;
use backendless\core\lib\Log;
use backendless\core\lib\Autoload;
use backendless\Backendless;

//class using for including user classes from folder "classes" in cloud and local mode, 
//also helper for creating class instances, and keep track of what classes have been included and which are not

class ClassManager
{
    
    protected static $included = [];
    protected static $classes_holder = [];
    
    public static function analyze() { 
        
        Log::writeInfo( "ClassManager start analyze classes", "file" );
        
        $all_files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( PathBuilder::getClasses() ) );
        $php_files = new RegexIterator($all_files, '/\.php$/');
        
        $classes_namespaces = [];
        
        foreach ( $php_files as $php_file) {
            
            $class_info = [];
            
            //$class_info["parent_class"] = self::getParentClass( file_get_contents( $php_file->getRealPath() ) );
            
            $class_info['namespace'] = str_replace( "/", "\\", trim( dirname( substr( $php_file->getRealPath(), strlen( PathBuilder::getClasses() ) ) ), "/" ) );
            
            $class_info['class_name'] = basename( $php_file->getRealPath(), ".php" );
            
            $class_info['path'] = $php_file->getRealPath();
            
            self::putToHolder( $class_info );
            
            $classes_namespaces[ $class_info['namespace'] ] = pathinfo($class_info["path"])["dirname"]; //key = namespace key = path to folder;
            
            Backendless::mapTableToClass( $class_info['class_name'], $class_info['namespace'] . "\\" . $class_info['class_name'] ); // set mapping for SDK.
            
             
        }
        
        foreach ( $classes_namespaces as $namespace=>$path ) {
        
            Autoload::addNamespace( $namespace, $path ); // add autoloading for user classes
            
        }
        
        Log::writeInfo( "ClassManager finished analyze classes", "file" );
        
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
    
    protected static function putToHolder( $class_info ) {
        
        if( !isset( self::$classes_holder[ $class_info['class_name'] ] ) ) {
            
            self::$classes_holder[ $class_info['class_name'] ] = $class_info;
                        
        } else {
            
            Log::writeError("Folder with user classes contains duplicate declaration of class : \"". $class_info['class_name']. "\".");
            
            exit();
            
        }
    }
    
//    protected static function includeFile( $full_path ) {
//        
//        if( ! in_array( $full_path, self::$included ) ) {
//
//            //require $full_path;
//            
//            self::$included[] = $full_path;
//
//        }
//        
//    }
    
//    public static function addAsIncluded( $full_path ){
//        
//        if( ! in_array( $full_path, self::$included ) ) {
//            
//            self::$included[] = $full_path;
//
//        }
//        
//    }
//    
//    public static function IncludeIfStillNot( $class_name ) {
//        
//        $name_parts = explode("\\", $class_name);
//        $count_elements = count( $name_parts );
//        
//        if( $count_elements > 0 ) {
//            
//            $class_name =  $name_parts[ $count_elements-1 ];
//            
//        }
//        
//        $path = self::getPathByClass( $class_name );
//        
//        if( $path !== null ) {
//            
//            self::includeFile( $path );
//            
//        } else {
//            
//            Log::writeError( "Try include unknown class: \"" . $class_name . "\"" );
//            
//            return false;
//            
//        }
//        
//        return true;
//        
//    }
    
//    protected static function getPathByClass( $class_name ) {
//        
//        if( array_key_exists($class_name, self::$classes_holder ) ) {
//            
//            return self::$classes_holder[$class_name]["path"];
//            
//        } else {
//            
//            null;
//            
//        }
//                
//    }
    
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
        
        //self::IncludeIfStillNot( $class_name );
        
        $full_class_name = self::getFullClassName( $class_name );
        
        if( $construc_data == null ) {
            
            return new $full_class_name();
            
        } else {
            
            return new $full_class_name( $construc_data );
            
        }
        
    }
        
    public static function debug(){
        
        //var_dump(self::$included);
        //var_dump(self::$classes_holder);
        
    }
    
}   