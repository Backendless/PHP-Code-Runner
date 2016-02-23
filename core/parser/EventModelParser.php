<?php
namespace backendless\core\parser;

use backendless\core\lib\Log;
use backendless\core\Config;
use backendless\core\commons\model\EventHandlersModel;
use backendless\core\commons\model\EventHandler;
use backendless\core\commons\exception\CodeRunnerException;
use backendless\core\util\ReflectionUtil;
use backendless\core\commons\holder\EventDefinitionHolder;
use backendless\core\holder\CodeExecutorHolder;
use backendless\core\util\PathBuilder;
use ReflectionClass;
use ReflectionMethod;


class EventModelParser
{

    private static  $event_definition_holder;
    private static $instance;

    private function __construct() {

        self::$event_definition_holder = EventDefinitionHolder::getInstance();
    }
  
    public static function getInstance() {

      if (!self::$instance) {
           self::$instance = new EventModelParser();
       }
       return self::$instance;

    }
  
    public function parseProductionModel( $app_version_id ) {
        
        Log::writeInfo("Parsing EventModel for appVersion: $app_version_id", $target = 'file');

        $event_handlers_model = new EventHandlersModel();

        try {
            
            $event_handlers_model->loadFromJson( PathBuilder::getProductionModel() );
            
        } catch( Exeption $e ) {
            
            Log::writeError( "Can not parse Event Model from file: " . PathBuilder::getProductionModel() . " for appVersionId: " . $app_version_id, 'file' );
            
        }

        return $event_handlers_model;
        
    }
    
    public function parseDebugModel() {
      
        Log::writeInfo("Parsing event model..."); 

        if( ! file_exists(  realpath( getcwd() . DS . Config::$CLASS_LOCATION) ) ) {
            
          $msg ="Seems, that the class location is wrong. Please, check it and run CodeRunner again.";
          Log::writeError( $msg ,$target = "console" );
          throw new CodeRunnerException($msg);
          
        }

        $event_handlers_model = new EventHandlersModel();
        
        $event_handlers_model->setApplicationId( Config::$APPLICATION_ID );
        $event_handlers_model->setAppVersionId( Config::$APP_VERSION );
        
        try {

            foreach( Config::$CORE['provider'] as $provider_name => $provider_data ) {

                $class_list = [];

                $class_list = ReflectionUtil::getClassesByProvider( $provider_data );  

                if( count($class_list) <= 0) {
                    continue;
                }

                foreach( $class_list as $class ) {
                    $this->processAnalyze( $provider_name, $provider_data, $class, $event_handlers_model );
                }

            }

            $executor = CodeExecutorHolder::getInstance()->getCodeExecutor( Config::$APPLICATION_ID, Config::$APP_VERSION );

            $executor->setEventHandlersModel( $event_handlers_model );
            

        } catch( Exception $e ) {

            throw new CodeRunnerException( $e );

        }
        
        return $event_handlers_model;
    }

    private function processAnalyze( $provider_name, $provider_data, $class, $event_handlers_model ) {
        
        Log::writeInfo( "Processing analyzing class: " . $class['class_name'] , $target = 'file' );

        ReflectionUtil::includeFile( $class['path'] );
        //ClassManager::addAsIncluded( $class['path'] );
        
        $reflection = new ReflectionClass( "\\" . $class['namespace'] . "\\".  $class['class_name']);
        $methods = $reflection->getMethods( ReflectionMethod::IS_PUBLIC );
        
        $target = $this->getTarget($class);
        
        $timer_definition = self::$event_definition_holder->getDefinitionByName( "TIMER", 'execute' );
        $timer_id = $timer_definition['id'];
        
        $custom_handler_definition = self::$event_definition_holder->getDefinitionByName( "CUSTOM", 'handleEvent' );
        $custom_handler_id = $custom_handler_definition['id'];
        
        foreach( $methods as $method ) {

            $definition = self::$event_definition_holder->getDefinitionByName( $provider_name, $method->name );
            
            if( $definition == null ) {
              continue;
            }
            
            $handler = new EventHandler();
            $handler->setId( $definition['id'] );
            $handler->setTarget( $target );
            $handler->setAsync( $this->getAsync( $method->name, $class ) );
            $handler->setTimer( ($definition['id'] == $timer_id ) ? true : false );
            $handler->setProvider( '\\' . $class['namespace'] . '\\' . $class['class_name']);
            
            if( $handler->isTimer() ) {
                
                $handler->setTarget( $this->getTimer($class) );
                $handler->setAsync( true );
                
                if( ! $this->isValidTimer( $handler ) ) {
                    continue;
                }
            }
        
            if( $handler->getId() == $custom_handler_id ) {

                $handler->setTarget( $this->getCustomEventName( $class ) );
                 
                if( $handler->getTarget() == null ) {
                    
                      throw new CodeRunnerException( "Asset is not present for custom event handler: " . $class['class_name'] );
                      
                }
            }
           
            if( Config::$CORE['provider'][$provider_name]['asset'] && $handler->getTarget() == null ) {
                
              throw new CodeRunnerException( "Asset is not present for handler: " . $class['class_name']);
              
            }
            
            $event_handlers_model->addHandler( $handler ); 
                  
        }
    }

    private function getCustomEventName( $class ) {
        
        return ReflectionUtil::getAnnotation( "BackendlessEvent", $class['path'] ); 
      
    }
  
    private function getTarget( $class ) {
        
        $asset = ReflectionUtil::getAnnotation( "Asset", $class['path'] ); 
       
        return ( $asset == null ) ? "*" : $asset;
        
    }

    private function getAsync( $method, $class ) {
        
        $method_annot = ReflectionUtil::getAnnotation( $method, $class['path'] ); 
        
        if( $method_annot == null ) {
            
            return false;
            
        }
        
        if( is_array($method_annot) ) {
            
            if( array_key_exists('Async', $method_annot) ) {
                
                return $method_annot['Async'];
                
            }
            
        } else {
            
            if( $method_annot == 'Async' ){
                
                return true;
                
            }
        }
        
        return false;
            
    }
    
    private function getTimer( $class ) {
    
      $timer = str_replace( "\"", "'", json_encode(ReflectionUtil::getAnnotation( "BackendlessTimer", $class["path"] ) ) );

      return ( $timer == null ) ? null : $timer;
      
    }

    private function isValidTimer( $handler ) {

        $current_time = time() * 1000; 
            
        $timer_info = json_decode( str_replace( "'", "\"", $handler->getTarget()), true);

        if( isset($timer_info["expire"]) ) {

          if( $timer_info["expire"] < $current_time ) {

            $masg =   "Timer '" . $timer_info["timername"] . "' already expired";

            Log::writeError($msg);

            throw new CodeRunnerException( $msg );

          }

        }

        if( $timer_info["startDate"] < $current_time ) {

            if( $timer_info["frequency"]["schedule"] == "once" ) {

                Log::writeError( "Timer's '" . $timer_info['timername'] . "' start time is in the past, unable to run the timer." );

                return false;
            }

            Log::writeWarn( "Timer's '" . $timer_info['timername'] . "' start time is in the past. The timer will run accordingly to the schedule.");

        }
        
        return true;
        
    }
    
}