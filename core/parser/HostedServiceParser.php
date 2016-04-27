<?php
namespace backendless\core\parser;

use backendless\core\util\HostedReflectionUtil;
use backendless\core\Config;
use backendless\core\lib\Log;
use backendless\core\commons\exception\CodeRunnerException;
use backendless\core\commons\model\HostedCollection;
use backendless\core\commons\model\DebuggableHostedModel;
use backendless\core\util\PathBuilder;


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
        
        Log::writeInfo( 'Parsing hosted user code...' ); 

        if( ! file_exists( PathBuilder::getDebugClasses()  )  ) {
            
          $msg = 'Seems, that the class location is wrong. Please, check it and run CodeRunner again.';
          Log::writeError( $msg ,$target = "console" );
          throw new CodeRunnerException( $msg );
          
        }

//        $debug_hosted_model = new DebuggableHostedModel();
//        
//        $debug_hosted_model->setApplicationId( Config::$APPLICATION_ID );
//        $debug_hosted_model->setAppVersionId( Config::$APP_VERSION );
            
        $reflection_util = new HostedReflectionUtil( PathBuilder::getDebugClasses() );
            
        $reflection_util->parseFolderWithCustomCode(); 
            
        if( $reflection_util->isError() ) {
                
            Log::writeError( $hosted_parser->getError()[ 'msg' ] );
            throw new CodeRunnerException( $hosted_parser->getError()[ 'msg' ] );
            
        }
        
        $hosted_collection = new HostedCollection();
        $hosted_collection->putModels( $reflection_util->getDebugModels() );
        
        return $hosted_collection;
       
    }
    
    public function parseModelRAI( $repo_path ) {
        
        Log::writeInfo( 'RAI parsing hosted user code.' ); 

        if( ! file_exists(  $repo_path ) ) {
            
          $msg = 'Seems, that the class location is wrong. Please, check it and run CodeRunner again.';
          
          Log::writeError( $msg, $target = 'console' );
          throw new CodeRunnerException( $msg );
          
        }
            
        $reflection_util = new HostedReflectionUtil( $repo_path );
            
        $reflection_util->parseFolderWithCustomCode(); 
            
        return $reflection_util;
        
    }
   
}
