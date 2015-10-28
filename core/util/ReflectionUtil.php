<?php
namespace backendless\core\util;

use backendless\core\Config;
use backendless\core\commons\exception\CodeRunnerException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use ReflectionClass;


class ReflectionUtil
{
    
    private static $annotations = [];

    private function __construct() {
        
    }
    
    public static function getClassesByProvider( $provider_data ) {

        $path = realpath( getcwd() . DS . Config::$CLASS_LOCATION );

        $allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $phpFiles = new RegexIterator($allFiles, '/\.php$/');
        
        $list = [];
        
        $provider_data = explode('\\', $provider_data['path']);
        
        $provider_class = trim( $provider_data[ count($provider_data)-1 ] );

        foreach ($phpFiles as $phpFile) {

            $code_src = file_get_contents( $phpFile->getRealPath() );
            
            $class_info = self::getClassInfo( $code_src );
            
            if( $class_info !== null ) {
                
                if( $class_info['parent_class_short_name'] == $provider_class ) {
                    
                    $class_info['path'] = $phpFile->getRealPath();
                    
                    $list[] = $class_info;
                    
                }
            }

        }
        
        return $list;

    }
    
    private static function getClassInfo( &$code ) {

        $class_info = []; 
        
        if ( preg_match('/^(.*)?(namespace(.*?);)(.*)?class(.*)extends(.*?){(.*)$/s', $code, $matches) ) {
        
            $namespace = explode(" ", rtrim( trim( $matches[2] ) , ";" ) );
            $class_info['namespace'] = $namespace[1];   
            $class_info['class_name'] = trim( $matches[5] );
            $class_info['parent_class'] = trim( $matches[6] );

            $tmp = explode("\\", $matches[6]);
            $count_elements = count($tmp);
            
            if( $count_elements > 1 ) {
                $class_info['parent_class_short_name'] = trim( $tmp[$count_elements-1] );
            } else{
                $class_info['parent_class_short_name'] = $class_info['parent_class'];
            }
            
        } else {
            
            return null;
            
        }
        
        return $class_info;
        
        
    }
    
    public static function includeFile( $path ) {
        
            require $path;
        
    }
    
    public static function getAnnotation($annot_name, $file_path) {
        
        if( ! isset( self::$annotations[$file_path] ) ) {
            
            self::loadAnnotations($file_path);
            
        }
        
        if( isset( self::$annotations[$file_path][$annot_name] ) ) {

            return self::$annotations[$file_path][$annot_name];

        } else {

            return null;

        }
        
    }
    
    private static function loadAnnotations( $file_path ) {
   
        $matches = [];
        //\/\*\*\s*@annot\((.*?)\)
        if( preg_match_all('/\/\*\*\s*@annot\((.*?)\)/', self::getComments($file_path), $matches) ) {
        
            if( count($matches) > 0 ) {
                
                self::$annotations[$file_path] = $matches[1];
                self::prepareAnnotations($file_path);
                
            }
            
        }        
    }
    
    private static function prepareAnnotations( $scope ) {
        
        foreach ( self::$annotations[$scope] as $index => $annot) {
            
             $annot_array = json_decode($annot, true);
             
             if( json_last_error() !== JSON_ERROR_NONE ) {
                 
                 throw new CodeRunnerException("Don't valid json annotation: $annot in file: $scope ");
                 
             }
             
             if( is_array($annot_array) ) { 
                 
                self::$annotations[$scope][key($annot_array)] = array_shift($annot_array);
                
             }
             
             unset(self::$annotations[$scope][$index]);
             
        }
        
    }
    
    private static function getComments( $file_path ) {
        
        $tokens = token_get_all( file_get_contents( $file_path ) );
        $comments = '';
        
        foreach( $tokens as $token ) {
            
            if($token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT) {
                
                $comments .= $token[1];
                
            }
            
        }
        
        return $comments;
        
    }
        
    public static function fillClassProperties( $class_object, $properties_data ) {
       
        $props = ( new ReflectionClass( $class_object ) )->getProperties();

         foreach ( $props as $property) {

             $property->setAccessible( true );

             if( isset( $properties_data[ $property->name ] ) ) {

                 $property->setValue( $class_object ,$properties_data[ $property->name ]);

             }

         }
            
   } 
   
    public static function getClassPropertiesAsArray( $class_object ) {
        
        $properties = [];
       
        $props = ( new ReflectionClass( $class_object ) )->getProperties();

        foreach ( $props as $property) {

            $property->setAccessible( true );

            $properties[ $property->name ] = $property->getValue( $class_object );

        }
        
        return $properties;
        
   } 

}    