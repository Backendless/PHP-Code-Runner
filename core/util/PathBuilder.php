<?php
namespace backendless\core\util;

use backendless\core\Config;
use backendless\core\lib\Log;
use backendless\core\GlobalState;

class PathBuilder
{

    private static $production_resources_path = null;
    
    public static function getProductionModel() {
        
        if( self:: $production_resources_path === null ) {
            
            self::buildPathToProductionResources();
            
        }
        
        return self::$production_resources_path . DS . "model.json";
        
    }
    
    public static function getProductionClasses() {
        
        if( self:: $production_resources_path === null ) {
            
            self::buildPathToProductionResources();
            
        }
        
        return self::$production_resources_path . DS . "classes";
        
    }
    
    public static function getDebugClasses() {
        
        return realpath( getcwd() . DS . Config::$CLASS_LOCATION );
        
    }

    protected static function buildPathToProductionResources() {
        
        $repo_path = rtrim( Config::$REPO_PATH, "/" );
                
        self::$production_resources_path = realpath( getcwd() . DS . $repo_path ) . DS . strtolower(Config::$TASK_APPLICATION_ID) . DS . Config::$RELATIVE_PATH;
        
        Log::writeInfo( "Build path to production resources : " . self::$production_resources_path, "file" );
        
    }
    
    public static function getClasses() {

        if( GlobalState::$TYPE === 'CLOUD' ) {

            return self::getProductionClasses();
            
        } else {
            
            return self::getDebugClasses();
            
        }
        
    }
    
    public static function getHostedService( $app_version_id, $relative_path = null ) {
        
        if( GlobalState::$TYPE === 'CLOUD' ) {

            $path = rtrim( Config::$REPO_PATH, "/" );
                
            $path = realpath( getcwd() . DS . $path ) . DS . strtolower( $app_version_id ) . DS . $relative_path;
        
            Log::writeInfo( "Build path to hosted code : " . $path , "file" );

            return $path;
            
        } else {
            
            return "TODO if need local debug";
            
                        
        }
        
    }

}