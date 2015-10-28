<?php
namespace backendless\core\processor;

use backendless\core\Config;
use backendless\core\RedisManager;
use backendless\core\processor\MessageDispatcher;
use backendless\core\lib\HttpRequest;
use backendless\core\lib\Log;
use backendless\core\commons\exception\CodeRunnerException;


class MessageProcessor
{
      
    protected $dispatcher;
    protected static $redis_manager;

    public function __construct() {
        
        $this->dispatcher = new MessageDispatcher();
        
        if( !self::$redis_manager ) {
            
            self::$redis_manager = RedisManager::getInstance();
            
        }
        
    }

    public function run() {
        
        $http_request = new HttpRequest();
        
        $target = Config::$CORE['processing_driverHostPort'] .'/getRequest?'
                                                                         . 'coderunnerId=' . Config::$CORE['processing_coderunnerId'] .''
                                                                         . '&requestId=' . Config::$CORE['processing_requestId']
                                                                         . '&lang=PHP';
        
        $http_request->setTargetUrl( $target )
                     ->setHeader('Content-type', 'application/json')
                     ->request( '', 'GET' );

        if( $http_request->getResponseCode() == 200 ) {

            Log::writeInfo( "Data received from java driver" . $http_request->getResponce(), $target = 'file' );
                        
            $this->dispatcher->onMessageReceived( $http_request->getResponce() );  
            
            //$example =  '{"___jsonclass":"com.backendless.coderunner.commons.protocol.RequestMethodInvocation","appVersionId":"TEST-7379B69C-D7FE-C34C-FF50-C08463D8A400","relativePath":"files/servercode/PHP/v1","async":false,"eventId":100.0,"arguments":[91.0,123.0,34.0,95.0,95.0,95.0,106.0,115.0,111.0,110.0,99.0,108.0,97.0,115.0,115.0,34.0,58.0,34.0,99.0,111.0,109.0,46.0,98.0,97.0,99.0,107.0,101.0,110.0,100.0,108.0,101.0,115.0,115.0,46.0,115.0,101.0,114.0,118.0,101.0,114.0,99.0,111.0,100.0,101.0,46.0,82.0,117.0,110.0,110.0,101.0,114.0,67.0,111.0,110.0,116.0,101.0,120.0,116.0,34.0,44.0,34.0,100.0,101.0,118.0,105.0,99.0,101.0,84.0,121.0,112.0,101.0,34.0,58.0,34.0,82.0,69.0,83.0,84.0,34.0,44.0,34.0,117.0,115.0,101.0,114.0,84.0,111.0,107.0,101.0,110.0,34.0,58.0,110.0,117.0,108.0,108.0,44.0,34.0,112.0,114.0,101.0,109.0,97.0,116.0,117.0,114.0,101.0,82.0,101.0,115.0,117.0,108.0,116.0,34.0,58.0,110.0,117.0,108.0,108.0,44.0,34.0,109.0,105.0,115.0,115.0,105.0,110.0,103.0,80.0,114.0,111.0,112.0,101.0,114.0,116.0,105.0,101.0,115.0,34.0,58.0,110.0,117.0,108.0,108.0,44.0,34.0,97.0,112.0,112.0,73.0,100.0,34.0,58.0,34.0,69.0,51.0,66.0,68.0,51.0,65.0,53.0,52.0,45.0,57.0,65.0,48.0,55.0,45.0,54.0,49.0,54.0,48.0,45.0,70.0,70.0,55.0,48.0,45.0,65.0,56.0,50.0,52.0,65.0,57.0,54.0,49.0,48.0,56.0,48.0,48.0,34.0,44.0,34.0,117.0,115.0,101.0,114.0,82.0,111.0,108.0,101.0,34.0,58.0,91.0,34.0,78.0,111.0,116.0,65.0,117.0,116.0,104.0,101.0,110.0,116.0,105.0,99.0,97.0,116.0,101.0,100.0,85.0,115.0,101.0,114.0,34.0,93.0,44.0,34.0,117.0,115.0,101.0,114.0,73.0,100.0,34.0,58.0,110.0,117.0,108.0,108.0,125.0,44.0,123.0,34.0,95.0,95.0,95.0,106.0,115.0,111.0,110.0,99.0,108.0,97.0,115.0,115.0,34.0,58.0,34.0,79.0,114.0,100.0,101.0,114.0,34.0,44.0,34.0,95.0,95.0,95.0,99.0,108.0,97.0,115.0,115.0,34.0,58.0,34.0,79.0,114.0,100.0,101.0,114.0,34.0,44.0,34.0,99.0,117.0,115.0,116.0,111.0,109.0,101.0,114.0,110.0,97.0,109.0,101.0,34.0,58.0,34.0,66.0,111.0,98.0,34.0,125.0,93.0],"id":"6C5F633C-C792-EE69-FF35-E877B21A2200","applicationId":"E3BD3A54-9A07-6160-FF70-A824A9610800","timeout":5000.0,"target":"Order","timestamp":1.439825950904E12}';
            //$example = '{"___jsonclass":"com.backendless.coderunner.commons.protocol.RequestMethodInvocation","appVersionId":"0B60E43E-B0DC-EBCF-FF95-37AFBA84FE00","async":false,"eventId":100.0,"relativePath":"servercode/PHP/v1","arguments":[91.0,123.0,34.0,95.0,95.0,95.0,106.0,115.0,111.0,110.0,99.0,108.0,97.0,115.0,115.0,34.0,58.0,34.0,99.0,111.0,109.0,46.0,98.0,97.0,99.0,107.0,101.0,110.0,100.0,108.0,101.0,115.0,115.0,46.0,115.0,101.0,114.0,118.0,101.0,114.0,99.0,111.0,100.0,101.0,46.0,82.0,117.0,110.0,110.0,101.0,114.0,67.0,111.0,110.0,116.0,101.0,120.0,116.0,34.0,44.0,34.0,100.0,101.0,118.0,105.0,99.0,101.0,84.0,121.0,112.0,101.0,34.0,58.0,34.0,82.0,69.0,83.0,84.0,34.0,44.0,34.0,117.0,115.0,101.0,114.0,84.0,111.0,107.0,101.0,110.0,34.0,58.0,34.0,55.0,67.0,53.0,70.0,54.0,51.0,50.0,50.0,45.0,55.0,50.0,55.0,55.0,45.0,48.0,53.0,56.0,50.0,45.0,70.0,70.0,56.0,65.0,45.0,70.0,51.0,48.0,65.0,57.0,70.0,66.0,69.0,65.0,52.0,48.0,48.0,34.0,44.0,34.0,112.0,114.0,101.0,109.0,97.0,116.0,117.0,114.0,101.0,82.0,101.0,115.0,117.0,108.0,116.0,34.0,58.0,110.0,117.0,108.0,108.0,44.0,34.0,109.0,105.0,115.0,115.0,105.0,110.0,103.0,80.0,114.0,111.0,112.0,101.0,114.0,116.0,105.0,101.0,115.0,34.0,58.0,110.0,117.0,108.0,108.0,44.0,34.0,97.0,112.0,112.0,73.0,100.0,34.0,58.0,34.0,50.0,69.0,54.0,53.0,70.0,66.0,69.0,52.0,45.0,70.0,68.0,56.0,51.0,45.0,49.0,68.0,54.0,65.0,45.0,70.0,70.0,69.0,53.0,45.0,66.0,50.0,54.0,48.0,52.0,65.0,50.0,49.0,50.0,50.0,48.0,48.0,34.0,44.0,34.0,117.0,115.0,101.0,114.0,82.0,111.0,108.0,101.0,34.0,58.0,91.0,34.0,65.0,117.0,116.0,104.0,101.0,110.0,116.0,105.0,99.0,97.0,116.0,101.0,100.0,85.0,115.0,101.0,114.0,34.0,93.0,44.0,34.0,117.0,115.0,101.0,114.0,73.0,100.0,34.0,58.0,34.0,69.0,55.0,54.0,51.0,56.0,53.0,56.0,52.0,45.0,51.0,51.0,69.0,56.0,45.0,48.0,70.0,68.0,50.0,45.0,70.0,70.0,49.0,53.0,45.0,51.0,54.0,57.0,66.0,50.0,52.0,51.0,50.0,49.0,50.0,48.0,48.0,34.0,125.0,44.0,123.0,34.0,95.0,95.0,95.0,106.0,115.0,111.0,110.0,99.0,108.0,97.0,115.0,115.0,34.0,58.0,34.0,79.0,114.0,100.0,101.0,114.0,34.0,44.0,34.0,95.0,95.0,95.0,99.0,108.0,97.0,115.0,115.0,34.0,58.0,34.0,79.0,114.0,100.0,101.0,114.0,34.0,125.0,93.0],"id":"7B82E87C-9643-5132-FF31-D0952702EB00","applicationId":"2E65FBE4-FD83-1D6A-FFE5-B2604A212200","lang":"PHP","timeout":5000.0,"target":"Order","timestamp":1.441703027359E12}';
            //$this->dispatcher->onMessageReceived( $example ); 

        } else {
        
            $msg = "CodeRunner get task fail, HTTP response code: " . $http_request->getResponseCode() . " response status: " . $http_request->getResponseStatus();

            Log::writeError( $msg, $target = 'file' );

            throw new CodeRunnerException( $msg );
            
        }
        
    }
    
}