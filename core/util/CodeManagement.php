<?php
namespace backendless\core\util;

//use backendless\core\Config;
//use backendless\core\lib\Log;
use backendless\core\util\CodeRunnerUtil;
//use ZipArchive;


class CodeManagement
{
    private static $instance;

    private static $code_runner_util;

    private function __construct() {

        self::$code_runner_util = CodeRunnerUtil::getInstance();

    }
    
  public static function getInstance() {
      
      if( ! self::$instance ) {
          
          self::$instance = new CodeManagement();
                  
      }
      
      return self::$instance;
            
  }

// cleanUpCode, downloadCode unZip must do java driver on  server side
  
//    public function cleanUpCode( $app_version_id ) {
//        
//        $path = Config::$REPO_PATH . $app_version_id;  //  ../repo/v1 TODO: determine the path that will actually be used
//      
//        try {
//            
//            rmdir( $path );
//            Log::writeInfo( "Directory $path removed", $target = "file" );
//            
//        } catch( Exeption $e ) {
//            
//            Log::writeError( "Directory remove $path failed", $target = "file" );
//            
//        }
//        
//    }
    
//    public function downloadCode( $application_id, $app_version_id ) {
//        
//        Log::writeInfo( "Downloading code for appVersionId:" . $app_version_id, $target = "file" );
//       
//        //TODO:: implement and degug this method
//        
//        try {
//            
//            $classes_zip = self::$code_runner_util->downloadServerCodeFile( $application_id, $app_version_id, "class" );
//            $libs_zip = self::$code_runner_util->downloadServerCodeFile( $application_id, $app_version_id, "libs" ); 
//            $model = self::$code_runner_util->downloadServerCodeFile( $application_id, $app_version_id, "model" );
//
//            $path = Config::$REPO_PATH . $app_version_id;
//
//            rmdir( $path );
//            mkdir( $path, $mode = 0755, true);
//            
//            $this->unZip( $classes_zip, 'classes' );
//            $this->unZip( $libs, 'lib' );
//  
//     //     Files.move( model.toPath(), new File( dir, "model.json" ).toPath(), StandardCopyOption.REPLACE_EXISTING );
//        
//            Log::writeInfo("Code for appVersionId: $app_version_id downloaded successfully", $target = "file" );
//            
//        } catch( Exception $e ) {
//            
//            Log::writeInfo( "Can not download server code for appVersionId:" . $app_version_id, $target = "file" );            
//            Log::writeError( $e->getMessage() , $target = "file" );            
//            
//        }
//        
//    }
    
    
//    private function unZip( $file_path, $new_folder) {
//        
//        $zip = new ZipArchive();
//            
//        $res = $zip->open( $file_path );
//        
//        if( $res === true ) {
//            
//            $zip->extractTo( $file_path . DS . $new_folder );
//            $zip->close();
//            
//        } else {
//            
//            Log::writeError( "Can not unzip : " .$file_path , $target = 'file' );
//            
//        }
//        
//    }
    
}
