<?php
namespace backendless\core\parser;

use backendless\core\util\HostedReflectionUtil;
use backendless\core\Config;
use backendless\core\lib\Log;
use Exception;
use backendless\core\commons\model\HostedModel;


class HostedServiceParser {
    
    private static $instance;

    private function __construct() {

    }
  
    public static function getInstance() {

      if ( ! self::$instance ) {
          
           self::$instance = new HostedServiceParser();
           
       }
       
       return self::$instance;

    }

    public function parseDebugModel() {        
        
        Log::writeInfo("Parsing hosted user code..."); 

        if( ! file_exists(  realpath( getcwd() . DS . Config::$CLASS_LOCATION) ) ) {
            
          $msg ="Seems, that the class location is wrong. Please, check it and run CodeRunner again.";
          Log::writeError( $msg ,$target = "console" );
          throw new CodeRunnerException( $msg );
          
        }

        $hosted_model = new HostedModel();
        
        $hosted_model->setApplicationId( Config::$APPLICATION_ID );
        $hosted_model->setAppVersionId( Config::$APP_VERSION );
            
        $hosted_parser = new HostedReflectionUtil( realpath( getcwd() . DS . Config::$CLASS_LOCATION) );
            
        $hosted_parser->parseFolderWithCustomCode(); 
            
        if( $hosted_parser->isError() ) {
                
            Log::writeError( $hosted_parser->getError()['msg'] );
            throw new Exception( $hosted_parser->getError()['msg'] );
            
        }
        
        $hosted_model->setData( $hosted_parser->getParsedData() );
        
        return $hosted_model;
       
    }
    
    public function parseModelRAI( $repo_path ) {
        
        Log::writeInfo("RAI parsing hosted user code."); 

        if( ! file_exists(  $repo_path) ) {
            
          $msg ="Seems, that the class location is wrong. Please, check it and run CodeRunner again.";
          Log::writeError( $msg ,$target = "console" );
          throw new CodeRunnerException( $msg );
          
        }
            
        $parser = new HostedReflectionUtil( $repo_path );
            
        $parser->parseFolderWithCustomCode(); 
            
        return $parser;
        
    }
   
}
