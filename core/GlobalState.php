<?php 
namespace backendless\core;


class GlobalState
{
    
    public static $STATE  = 'NONE';   // possible values NONE, REGISTERED, DEPLOY, PUBLISH

    public static $TYPE   = 'LOCAL';  // possible values LOCAL, CLOUD

    private function __construct(){

    }
  
}
