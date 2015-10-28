<?php
namespace backendless\core\commons\definition;

use backendless\core\Config;
use backendless\core\lib\Log;


class DefinitionReader
{

    protected function __construct() {
        
    }
    
    public static function getDefinition( $definition_name ){

        return self::readDefinition( $definition_name );
        
    }
    
    private static function readDefinition( $definition_name ) {
        
        $definition_path = BP . DS . implode( DS, Config::$CORE['definition_source_path'] ) . DS. $definition_name . ".php";

        $readed_definition = false;

        if( file_exists($definition_path) ) {

                $readed_definition = include $definition_path;

        }else {
            
            Log::writeError('Can\'t read definition file.', $target = 'file');
            
        }
            
        return $readed_definition;
    }
    
}
