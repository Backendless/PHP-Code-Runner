<?php 
namespace backendless\core;

use backendless\core\util\CodeRunnerUtil;
use backendless\core\parser\EventModelParser;
use backendless\core\parser\HostedServiceParser;
use backendless\core\commons\exception\CodeRunnerException;
use backendless\core\commons\holder\HostedModelHolder;
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
    private $hosted_collection = [];

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
        
        if( GlobalState::$TYPE == 'LOCAL' ) {
            
            $this->register();
            $this->doInstructions( $instruction = null );
            
            $this->tryStopDebugIdUpdater();
      
            $cmd = 'php -d xdebug.remote_autostart=0 ..' . DS . 'core' . DS . 'DebugIdUpdater.php ' . Config::$DEBUG_ID; 
            
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
                
                stream_set_blocking ( STDIN , false );
                
                $command = trim( fgets(STDIN) );
                
                stream_set_blocking ( STDIN , true );
                
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

                // add send message about error to redis/driver.
                return;

            }
            
            if ( Config::$STATUS == "registered" ) {
                
                CodeRunnerUtil::getInstance()->deleteHostedModel();
                CodeRunnerUtil::getInstance()->unRegisterCodeRunner();
                Log::writeInfo("Debugging Utility disconnected successfully.");
                Log::writeToLogFile("\n");
            }
            
            if( GlobalState::$TYPE == 'LOCAL' ) {
                
                $this->tryStopDebugIdUpdater();            
                
            }
                
            Log::writeInfo("Thank you for using Backendless.\n", "console");    
                
        }
        catch( Exception $e ) {
            
            Log::writeError("Unfortunately, Debugging Utility disconnected unsuccessfully.\n");
            
      }
        
      
    }
    
    public function terminateRunner( $signal ) {

        // hook for termination of script, if script terminated will call method shutdown.
        Log::writeInfo("Cleaning up and disconnecting...", $target = "console" );
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
            Log::writeInfo("Runner successfully registered.");
            Config::saveKeys();
        
        } catch( Exception $e ) {
            
            Log::writeError("Runner registration failed.", $target = 'console');
            Log::writeError( $e->getMessage()  , $target = 'file' );

            self::Stop();
            exit();
          
        }
        
      }
      
    public function deployModel() {
        
        $is_empty_event_handlers_model = false;
        $is_empty_hosted_collection = false;
        
        if( $this->event_handlers_model == null || $this->event_handlers_model->getCountTimers() == 0 && $this->event_handlers_model->getCountEventHandlers() == 0 ) {

            $is_empty_event_handlers_model = true;
            
        }
        
        if( $this->hosted_collection->getCountsOfModels() == 0 || $this->hosted_collection->getCountOfEvents() == 0 ) {

            $is_empty_hosted_collection = true;
            
        }
        
        if( $is_empty_event_handlers_model && $is_empty_hosted_collection ) {

            Log::writeWarn( 'There is no code to deploy to Backendless...' );
            exit();
            
        }
        
        if( !Config::$AUTO_PUBLISH ) {
            
            Log::writeInfo( 'Deploying models to server, and starting debug...' );
            
        }else{
            
            Log::writeInfo( 'Deploying models to server...' );
            
        }
        
        try {
            
            CodeRunnerUtil::getInstance()->deployModel( $this->event_handlers_model );
            CodeRunnerUtil::getInstance()->deployModel( $this->hosted_collection, true ); 
    
            ExternalHostHolder::getInstance()->setUrls( Config::$APPLICATION_ID, CodeRunnerUtil::getInstance()->getExternalHost() );
            
            Log::writeInfo( 'Models successfully deployed...' );
                        
            if( !Config::$AUTO_PUBLISH ) {
                
                Log::writeInfo( 'Waiting for events...' );
                
            }
            
        } catch( CodeRunnerException $e ) {

           Log::writeError( 'Models deploying failed...' ); 
           Log::writeError( $e->getMessage(), $target = 'file' );
           self::Stop();
            
            
        } catch( Exception $e ) {
            
          Log::writeError( 'Models deploying failed...' );
          Log::writeError( $e->getMessage(), $target = 'file' );
          self::Stop();
          
        }
        
    }

    public function publishCode( $hosted = false ) {

        $this->resetTmpFolder( $hosted );

        if( $hosted ) {
            
            $hosted_events = $this->hosted_collection->getCountOfEvents();
            
            if( $hosted_events <= 0) { return; }
            
            Log::writeInfo("Deploying $hosted_events hosted service event to the server… ");
            
        } else {
            
            $handlers = $this->event_handlers_model->getCountEventHandlers() == 1 ? "handler" : "handlers";
            $timers = $this->event_handlers_model->getCountTimers() == 1 ? "timer" : "timers";
            
            if( ($this->event_handlers_model->getCountEventHandlers() + $this->event_handlers_model->getCountTimers()) <= 0 ) { return; }
            
            Log::writeInfo("Deploying {$this->event_handlers_model->getCountEventHandlers()} event " . $handlers . " and {$this->event_handlers_model->getCountTimers()} " . $timers . " to the server… ");
            
        }

        try{
            
            $code_zip_path = realpath( getcwd() . DS . Config::$CORE['tmp_dir_path'] );
            
            if( $hosted ) {
                 $code_zip_path .= DS . 'hosted' .DS. 'code.zip';
            }else{
                $code_zip_path .= DS . 'events' . DS . 'code.zip';
            }
            
            $this->createArchive( $code_zip_path, $hosted );

            CodeRunnerUtil::getInstance()->publish( $code_zip_path, $hosted );
            
            if( $hosted ) {
                Log::writeInfo( "Successfully deployed all hosted user code." );
            } else {
                Log::writeInfo( "Successfully deployed all event handlers and timers." );
            }

            if( ($this->event_handlers_model->getCountEventHandlers() + $this->event_handlers_model->getCountTimers()) > 5 ) {
                
                Log::writeWarn( "The deployment will result in additional charges as it exceeds the free plan limit." );
                Log::writeInfo( "See the Billing screen in Backendless Console for details." );
                Log::writeInfo( "The billing screen is available at Manage > Billing." );
                
            }
            
        } catch( CodeRunnerException $e ) {
            
            Log::writeError( $e->getMessage(), $target= 'file');
            
            Log::writeError( "Code publishing failed..", $target= 'all');
            
            $this->removeTmpFolder();
            
        }
        
    }
  
    protected function resetTmpFolder( $hosted ){
        
        $dir_path = Config::$CORE[ 'tmp_dir_path' ];
        
        if( $hosted ) {
        
            $dir_path .= DS . 'hosted';
            
        } else {
            
            $dir_path .= DS . 'events';
            
        }
        
        if( file_exists( $dir_path ) ) { 

            $this->rrmdir( $dir_path );

        }
            
        mkdir( $dir_path, $mode = 0777, $recursive = true );
        
    }
    
    protected function removeTmpFolder() {
        
        $this->rrmdir( Config::$CORE[ 'tmp_dir_path' ] );
                    
    }
    
    protected function createArchive( $code_zip_path, $hosted ) {
        
        $zip = new ZipArchive;
        
        $zip->open( $code_zip_path, ZipArchive::CREATE );
        
        $this->addFolderToArchive( $zip, realpath( getcwd() . DS . Config::$CLASS_LOCATION ) );
        $this->addFolderToArchive( $zip, realpath( getcwd() . DS . '..' . DS . 'lib' ) );
        
        if( $hosted ) {
            
            $zip->addFromString( 'model.json', $this->hosted_collection->getJson() );
            
        } else {    
            
            $zip->addFromString( 'model.json', $this->event_handlers_model->getJson( true ) );
            
        }
        
        $zip->close();
            
    }
    
    protected function addFolderToArchive( $archive, $path ) {

        $skip_sdk = 'lib/backendless';
        
         $files = new RecursiveIteratorIterator( 
                                                    new RecursiveDirectoryIterator(
                                                                                    $path,
                                                                                    RecursiveDirectoryIterator::SKIP_DOTS 
                                                                                   ) 
                                               );
        
        $class_location_folder_name = basename ( $path );
        
        foreach ( $files as $file ) {
                        
            $folder_path_inside_archive = strstr( pathinfo( $file->getPathName() )[ 'dirname' ], basename ( $path ) );
            
            if ( Config::$CORE[ 'os_type' ] == 'WIN' ) {
                
                $folder_path_inside_archive = str_replace( DS , '/', $folder_path_inside_archive );
                
            }
            
            if( strstr( $folder_path_inside_archive, $skip_sdk, $before_needle = true) === "" ) {
                
                continue;
                
            }
            
            $archive->addEmptyDir( $folder_path_inside_archive );
            
            $archive->addFile( $file->getPathName(), $folder_path_inside_archive . '/' . $file->getFileName() );
            
        }
        
    }
  
    public function doInstructions( $instruction ) {
        
      try {
          
        $this->doBuild();
        $this->deployModel();
        
        if( Config::$AUTO_PUBLISH ) {
            
            $this->publishCode();
            //$this->publishCode( $hosted = true );
            $this->removeTmpFolder();
            Log::writeInfo( "CodeRunner will shutdown now." );
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
        
        Log::writeInfo( "Build successfully event model: " . $this->event_handlers_model );
        
        $this->hosted_collection = HostedServiceParser::getInstance()->parseDebugModel();
        
        // TODO change first object to collection
        $first = $this->hosted_collection->getFirst();
        
        HostedModelHolder::setModel( $first );
        HostedModelHolder::setXMLModel( $first->getXML() );
        
        Log::writeInfo( 'Build successfully hosted services model: ' . $this->hosted_collection );
        
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
                //$predis->expire( Config::$DEBUG_ID, 0 );
                
           }
           
           unlink(".run");
           
        }
        
    }

}
