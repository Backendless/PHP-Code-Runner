<?php
namespace backendless\core\holder;

use backendless\core\runtime\CodeExecutor;
use backendless\core\util\CodeManagement;
use backendless\lib\Log;


class CodeExecutorFactory
{
  

    public function createExecutor( $application_id, $app_version_id ) {
        
        $executor = new CodeExecutor( $application_id, $app_version_id );
        
        try {
            
          // code download java driver on server side  
          //CodeManagement::getInstance()->downloadCode( $application_id, $app_version_id );/// 
          $executor->init();
          
        } catch( Exception $e ) {
            
          Log::writeError( "Can not create executor for appVersionId: " . $app_version_id);
          Log::writeError( "Can not create executor msg:" . $e->getMessage() , $target = "file" );
          
        }
        
        return $executor;
      
    }
    
}
