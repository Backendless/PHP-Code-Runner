<?php
namespace backendless\core\util;

use backendless\core\util\CodeRunnerUtil;


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

}
