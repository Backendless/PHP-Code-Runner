<?php 
namespace backendless\core;

use backendless\core\util\CodeRunnerUtil;
use backendless\core\parser\EventModelParser;
use backendless\core\commons\exception\CodeRunnerException;
use backendless\core\holder\ExternalHostHolder;
use backendless\core\processor\MessageProcessor;
use backendless\core\processor\DebugMessageProcessor;
use backendless\core\processor\ResponderProcessor;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use backendless\core\lib\Log;
use Exception;
use ZipArchive;

class CodeRunner
{
    private $message_processor;
    private $responder_processor;
    
    private $event_handlers_model;

    public function __construct() {
        
        //registered method called when app shutdown
        register_shutdown_function( array($this, 'shutdown') );
        
        if( Config::$CORE['os_type'] != "WIN" && function_exists("pcntl_signal") ) { // PCNTL extension not supported on Windows
            
            //register events when catch app termination and run shutdown method 
            pcntl_signal(SIGINT, array(&$this, 'terminateRunner'));     // CTRL+C
            pcntl_signal(SIGQUIT, array(&$this, 'terminateRunner'));    // CTRL+\(Y)
            
        } else {
            
            Log::writeInfo( "To terminate CodeRunner, enter the 'terminate' command", $target  = "console" );
            
        }
        
    }

    public function start() {
        
        if( GlobalState::$TYPE == 'LOCAL') {
            
            $this->register();
            $this->doInstructions( $instruction = null );
            
            $this->tryStopDebugIdUpdater();
            
            // CODE FOR TESTING
            //$predis = RedisManager::getInstance()->getRedis();
            //$predis->set( "51591778-2B61-B82F-FF33-B7B5F460FD00:8C902CEE-643E-C017-FF7D-C05ACC97C600:CodeRunnerDebug-TEST-DIMA" ,"51591778-2B61-B82F-FF33-B7B5F460FD00:8C902CEE-643E-C017-FF7D-C05ACC97C600:CodeRunnerDebug-TEST-DIMA" );
            //$predis->expire( "51591778-2B61-B82F-FF33-B7B5F460FD00:8C902CEE-643E-C017-FF7D-C05ACC97C600:CodeRunnerDebug-TEST-DIMA", 25 );
            
      
            $cmd = 'php ..' . DS . 'core' . DS . 'DebugIdUpdater.php ' . Config::$DEBUG_ID; 
            
            // start background script for updating in redis expire of debugId
            if( Config::$CORE['os_type'] != "WIN") {
                
               Config::$DEBUG_PID = exec( $cmd . ' > /dev/null 2>&1 & echo $!' );
                
            } else {
                
                $descriptorspec = [  
                                    0 => [ "pipe", "r" ],  
                                    1 => [ "pipe", "w" ],  
                                  ];
                $pipes ='';
                $proc = proc_open( "start /B " . $cmd, $descriptorspec, $pipes );
                $info = proc_get_status( $proc );
                
                Config::$DEBUG_PID =  $info['pid'];
                //proc_close( $proc );
                
            }

            // put pid into file for try kill DebugIdUpdater.php it before next run CodeRunner
            file_put_contents(".run", Config::$DEBUG_PID);
                        
            
            for( ; ; ) {             
                
                $this->message_processor->run();
                $this->responder_processor->localRun();
                
                $command = trim( fgets(STDIN) );
                
                if( $command == 'terminate' ) {
                    
                    $this->terminateRunner( $signal = 'SIGINT' );
                    
                }
                
            }
            
        } else {
            
            $this->message_processor->run();
            $this->responder_processor->cloudRun();
            
        }
        
    }
    
    public function shutdown() {

        try {
            
            if( GlobalState::$TYPE == 'CLOUD' ) {

                return;

            }
            
            if (Config::$STATUS == "registered" ) {
                
                CodeRunnerUtil::getInstance()->unRegisterCodeRunner();
                Log::writeInfo("Debugging Utility disconnected successfully.");
                Log::writeToLogFile("\n");
            }
            
            $this->tryStopDebugIdUpdater();            
                
            Log::writeInfo("Thank you for using Backendless.\n", "console");    
                
        }
        catch( Exception $e ) {
            
            Log::writeError("Unfortunately, Debugging Utility disconnected unsuccessfully.\n");
            
      }
        
      
    }
    
    public function terminateRunner( $signal ) {

        // hook for termination of script, if script terminated will call method shutdown.
        Log::writeInfo("Terminating...", $target = "console" );
        exit();
        
    }
    
    public function loadMessageProcessor() {
        
        if( GlobalState::$TYPE == 'CLOUD' ) {
            
            $this->message_processor = new MessageProcessor();

        } else {
            
            $this->message_processor = new DebugMessageProcessor();
            
        }
        
        $this->responder_processor = new ResponderProcessor();
        
    }

    private function register() {
        
        Log::writeInfo("Registering runner on: " . Config::$SERVER_URL . " with secretKey: ". Config::$SECRET_KEY);        
                
        try {
            
            CodeRunnerUtil::getInstance()->registerCodeRunner();
            GlobalState::$STATE = 'REGISTERED';
            Log::writeInfo("Runner successfully registered.");
        
        }
        catch( Exception $e ) {
          
          //Log::writeError("Runner registration failed. Please check 'application_id', 'application_secretKey' and 'application_version' in 'config.php' file.", $target = 'console');  
            
          Log::writeError("Runner registration failed.", $target = 'console');
          Log::writeError( $e->getMessage()  , $target = 'file' );
          
          self::Stop();
          exit();
          
        }
        
      }
    
    public function getModel() {
        //return model;
    }
  
      
    public function deployModel() {
        
        if( $this->event_handlers_model == null || $this->event_handlers_model->getCountTimers()== 0 && $this->event_handlers_model->getCountEventHandlers() == 0 ) {

            Log::writeWarn( "There are no any code, which can be deployed to Backendless..." );
            exit();
            
        }
        
        Log::writeInfo( "Deploying model to server, and starting debug..." );
        
        try {
            
            CodeRunnerUtil::getInstance()->deployModel( $this->event_handlers_model );
    
            ExternalHostHolder::getInstance()->setUrls( Config::$APPLICATION_ID, CodeRunnerUtil::getInstance()->getExternalHost() );
          
            GlobalState::$STATE = "DEPLOY";
            
            Log::writeInfo( "Model successfully deployed..." );
            Log::writeInfo( "Waiting for events..." );
            
        } catch( CodeRunnerException $e ) {

           Log::writeError( "Model deploying failed..." ); 
           Log::writeError( $e->getMessage(), $target = 'file' );
           self::Stop();
            
            
        } catch( Exception $e ) {
            
          Log::writeError( "Model deploying failed..." );
          Log::writeError( $e->getMessage(), $target = 'file' );
          self::Stop();
          
        }
  }

    public function publishCode() {

        if( ! file_exists ( Config::$CORE['tmp_dir_path'] ) ) {
            
            mkdir( Config::$CORE['tmp_dir_path'], 0777 );
          
        }
        
        $handlers = $this->event_handlers_model->getCountEventHandlers() == 1 ? "handler" : "handlers";
        $timers = $this->event_handlers_model->getCountTimers() == 1 ? "timer" : "timers";
        
        Log::writeInfo("Deploying {$this->event_handlers_model->getCountEventHandlers()} event " . $handlers . " and {$this->event_handlers_model->getCountTimers()} " . $timers . " to the server… ");

        try{
            
            $code_zip_path = realpath( getcwd() . DS . Config::$CORE['tmp_dir_path'] ) .DS. 'code.zip';
            
            $classes_path = realpath( getcwd() . DS . Config::$CLASS_LOCATION);

            $zip = new ZipArchive;
            
            $zip->open( $code_zip_path, ZipArchive::CREATE );

            $files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator($classes_path), RecursiveIteratorIterator::LEAVES_ONLY);
            
            $class_location_folder_name = basename ( Config::$CLASS_LOCATION );
            
            foreach ( $files as $file ) {

                if( $file->getFileName() === '.' || $file->getFileName() == '..') {
                    continue;
                }
                
                $path_part = explode($class_location_folder_name, $file);
                $zip->addFile($file, $class_location_folder_name . $path_part[1] );
                
            }
            
            $lib_path = realpath( getcwd() . DS . ".." . DS . 'lib' );
            
            $files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator($lib_path), RecursiveIteratorIterator::LEAVES_ONLY );

            foreach ( $files as $file ) {

                if( $file->getFileName() === '.' || $file->getFileName() == '..') {
                    continue;
                }
                
                $path_part = explode('lib', $file);
                $zip->addFile($file, 'lib' . $path_part[1] );
                
            }
            
            $model_file_path = realpath( getcwd() . DS . Config::$CORE['tmp_dir_path'] );
            $model_file_path .=  DS . 'model.json';
            
            file_put_contents( $model_file_path, $this->event_handlers_model->getJson(true));
            
            $zip->addFile( $model_file_path, 'model.json' );

            $zip->close();
            
            unlink($model_file_path);
            
            CodeRunnerUtil::getInstance()->publish( $code_zip_path );

            GlobalState::$STATE = 'PUBLISH';

            Log::writeInfo( "Successfully deployed all event handlers and timers." );

            if( ($this->event_handlers_model->getCountEventHandlers() + $this->event_handlers_model->getCountTimers()) > 5 ) {
                
                Log::writeWarn( "The deployment will result in additional charges as it exceeds the free plan limit." );
                Log::writeInfo( "See the Billing screen in Backendless Console for details." );
                Log::writeInfo( "The billing screen is available at Manage > Billing." );
                
            }

            Log::writeInfo( "CodeRunner will shutdown now." );
            
            $this->rrmdir( Config::$CORE['tmp_dir_path'] );
            
            exit(0);
            
            
            
        } catch( CodeRunnerException $e ) {
            
            Log::writeError( $e->getMessage(), $target= 'all'); //change to file
            
            Log::writeError( "Code publishing failed..", $target= 'all');
            
            if( file_exists ( Config::$CORE['tmp_dir_path'] ) ) {
                
               $this->rrmdir( Config::$CORE['tmp_dir_path'] );
                
            }
            
        }
        
  }
  
    public function doInstructions( $instruction ) {
        
      try {
          
        $this->doBuild();
        $this->deployModel();
        
        if( Config::$AUTO_PUBLISH) {
            
            $this->publishCode();
            exit(0);
            
        }
        
      } catch( CodeRunnerException $e ) {
          
        Log::writeError( $e->getMessage() );
        self::Stop();
        exit(1);
        
      }
            
    }
    
    public function doBuild() {
        
        $this->event_handlers_model = EventModelParser::getInstance()->parseDebugModel();
        
        Log::writeInfo( "Build successfully: " . $this->event_handlers_model);                
        GlobalState::$STATE = "BUILD";
        
    }
    
    private function rrmdir( $dir ) { 
        
        if ( is_dir( $dir ) ) { 
            
          $objects = scandir( $dir ); 
          
          foreach ( $objects as $object ) { 
              
            if ($object != "." && $object != "..") { 
                
              if ( filetype($dir . DS . $object) == "dir" ) { 
                  
                $this->rrmdir( $dir . DS . $object ); 
                
              }else{
                  
                unlink( $dir . DS . $object ); 
                                  
              }
              
            } 
            
          } 

          rmdir($dir); 
          
        } 
        
    }
    
    static public function Stop() {
        
        if ( GlobalState::$TYPE == 'LOCAL' ) {
        
            exec( 'php ..' . DS . 'core' . DS . 'Stop.php' );
            
        }
        
    }
    
    private function tryStopDebugIdUpdater() {
        
        
        if( file_exists( ".run" ) ) {
            
           $pid = file_get_contents(".run");
           
            if( $pid != "" ) {
               
                if( Config::$CORE['os_type'] != "WIN") {               
                    
                        //exec("kill -9 $pid");
                    posix_kill( (int)$pid, 9 );
                    
                } //else {    exec("taskkill /F /PID $pid"); }
                
                $predis = RedisManager::getInstance()->getRedis();
                $predis->expire( Config::$DEBUG_ID, 0 );
                
           }
           
           unlink(".run");
           
        }
        
    }

}