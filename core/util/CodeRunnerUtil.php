<?php
namespace backendless\core\util;

use backendless\core\Config;
use backendless\core\commons\exception\CodeRunnerException;
use backendless\core\lib\HttpRequest;
use backendless\core\lib\Log;
use ReflectionClass;
use ReflectionProperty;


class CodeRunnerUtil
{
    
    static protected  $_instance;

    private static $APP_ID_KEY = "application-id";
    private static $SECRET_KEY = "secret-key";
    private static $VERSION = "AppVersion";

    private function __construct() {
        
    }
    
    static public function getInstance() {

        if ( !self::$_instance ) {
            
            self::$_instance = new CodeRunnerUtil();
            
        }

        return self::$_instance;
    }
    
    public function registerCodeRunner() {
     
        $target = Config::$SERVER_URL . Config::$CORE['register_runner_link'];

        $http_request = new HttpRequest();

        $http_request->setTargetUrl($target)
                     ->setHeader(self::$APP_ID_KEY, Config::$APPLICATION_ID)
                     ->setHeader(self::$SECRET_KEY, Config::$SECRET_KEY)
                     ->setHeader(self::$VERSION, Config::$APP_VERSION)
                     ->setHeader('Content-type', 'application/json')
                     ->request( json_encode( array('lang' => Config::$CORE['lang']) ) );
        
        if( $http_request->getResponseCode() != 200 ) {

            $msg = "CodeRunner registration fail, HTTP response code: " . $http_request->getResponseCode() . " response status: " . $http_request->getResponseStatus();

            Log::writeError($msg, $target = 'file');

            throw new CodeRunnerException( $msg );

        } else {
            
            Config::$STATUS = "registered";
            
            Config::$DEBUG_ID = json_decode($http_request->getResponce(), true)['debugId'];
            
            // TODO: delete after test
            Config::$DEBUG_ID = "51591778-2B61-B82F-FF33-B7B5F460FD00:8C902CEE-643E-C017-FF7D-C05ACC97C600:CodeRunnerDebug-TEST-DIMA";
            
            if( !isset( Config::$DEBUG_ID )  ) {
                
                $msg = "CodeRunner can't get  debugid.";
                        
                Log::writeError($msg, $target = 'all' );
                
                exit();
            }
                            
        }
    }
  
    public function unRegisterCodeRunner() {
  
        $target = Config::$SERVER_URL . Config::$CORE['unregister_runner_link'];

        $http_request = new HttpRequest();

        $http_request->setTargetUrl($target)
                     ->setHeader( self::$APP_ID_KEY, Config::$APPLICATION_ID )
                     ->setHeader( self::$SECRET_KEY, Config::$SECRET_KEY )
                     ->setHeader( self::$VERSION, Config::$APP_VERSION )
                     ->setHeader( 'Content-type', 'application/json' )
                     ->request( '', $method = 'GET' );

        if( $http_request->getResponseCode() != 200 ) {

          $msg = "CodeRunner disconnected unsuccessfully, HTTP response code: " . $http_request->getResponseCode() . " response status: " . $http_request->getResponseStatus();  

          Log::writeError($msg, $target='file');

          throw new CodeRunnerException($msg);

        }
    
  }
  
    public function deployModel( $model ) {
          
        $target = Config::$SERVER_URL . Config::$CORE['register_model_link'];

        $http_request = new HttpRequest();
        
//        $json = '{  
//   "applicationId":"E3BD3A54-9A07-6160-FF70-A824A9610800",
//   "appVersionId":"v1",
//   "handlers":[  
//      {  
//         "id":101,
//         "async":"true",
//         "target":"Order",
//         "timer":false,
//         "provider":"com.backendless.ordermanagement.events.persistence_service.OrderTableEventHandler"
//      },
//      {  
//         "id":100,
//         "async":false,
//         "target":"Order",
//         "timer":false,
//         "provider":"com.backendless.ordermanagement.events.persistence_service.OrderTableEventHandler"
//      }
//   ]
//}';
        
        $http_request->setTargetUrl($target)
                     ->setHeader(self::$APP_ID_KEY, Config::$APPLICATION_ID)
                     ->setHeader(self::$SECRET_KEY, Config::$SECRET_KEY)
                     ->setHeader(self::$VERSION, Config::$APP_VERSION)
                     ->setHeader('Content-type', 'application/json')
                     ->request( $model->getJson()  );
                     
         
        if( $http_request->getResponseCode() != 200 ) {

            $msg = "Model deploying failed, HTTP response code: " . $http_request->getResponseCode() . " response status: " . $http_request->getResponseStatus();  

            Log::writeError($msg);
      
            throw new CodeRunnerException($msg);
      
        }

    }

    public function publish( $code_zip_path ) {
        
        $target = Config::$SERVER_URL . Config::$CORE['publish_code'] . "/" . Config::$CORE['lang'];

        $http_request = new HttpRequest();

        $multipart_boundary ="------BackendlessFormBoundary" . md5(uniqid()) . microtime(true);

        $file_contents = file_get_contents( $code_zip_path );
        
        //var_dump($code_zip_path);

        $content =  "--". $multipart_boundary ."\r\n".
                     "Content-Disposition: form-data; name=\"code\"; filename=\"".basename($code_zip_path)."\"\r\n".
                     "Content-Type: application/zip\r\n\r\n".
                     $file_contents."\r\n";

        $content .= "--".$multipart_boundary."--\r\n";
       
        $http_request->setTargetUrl($target)
                     ->setHeader(self::$APP_ID_KEY, Config::$APPLICATION_ID)
                     ->setHeader(self::$SECRET_KEY, Config::$SECRET_KEY)
                     ->setHeader(self::$VERSION, Config::$APP_VERSION)
                     ->setHeader('Content-type', ' multipart/form-data; boundary=' .$multipart_boundary )
                     ->request( $content  );
         
        if( $http_request->getResponseCode() != 200 ) {

            $msg = "Deploying code failed, HTTP response code: " . $http_request->getResponseCode() . " response status: " . $http_request->getResponseStatus();  

            Log::writeError($msg);
      
            throw new CodeRunnerException($msg);
      
        }
        
  }

    public function downloadServerCodeFile( $applicationId, $appVersionId, $type ) {
        
//        WebTarget target = client.target( Config.SERVER_URL ).path( "/servercode/" + type + "/" + appVersionId + "/" + Lang.JAVA );
//        Response response = target.request().header( APP_ID_KEY, applicationId ).get();
//        if( response.getStatus() != 200 )
//        {
//          throw new CodeRunnerException( "Can not download file from url: " + target.getUri().toString() );
//        }
//        return response.readEntity( File.class );
//      }
//
    }    

    public function getExternalHost() {
        
        $target = Config::$SERVER_URL . Config::$CORE['external_host'];
          
        $http_request = new HttpRequest();
        
        $http_request->setTargetUrl($target)
                     ->setHeader(self::$APP_ID_KEY, Config::$APPLICATION_ID)
                     ->setHeader(self::$SECRET_KEY, Config::$SECRET_KEY)
                     ->setHeader(self::$VERSION, Config::$APP_VERSION)
                     ->setHeader('Content-type', 'application/json')
                     ->request( '', $method = "GET" );

        if( $http_request->getResponseCode() != 200 ) {
            
                return [];
                
        }
        
        return json_decode( $http_request->getResponce(), true );

    }
  
}