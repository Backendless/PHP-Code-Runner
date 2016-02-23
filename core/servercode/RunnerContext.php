<?php
namespace backendless\core\servercode;

use backendless\core\Config;


class RunnerContext 
{
    private $appId;
    private $userToken;
    private $deviceType;
    private $userId;
    private $missingProperties;
    private $prematureResult;
    private $userRole = [];


    public function __construct( $arguments ) {
        
        $this->appId               =   $arguments["appId"];
        $this->userToken           =   $arguments["userToken"];
        $this->deviceType          =   $arguments["deviceType"];
        $this->userId              =   $arguments["userId"];
        $this->missingProperties   =   $arguments["missingProperties"];
        $this->prematureResult     =   $arguments["prematureResult"];
        $this->userRole            =   $arguments["userRole"];
        
    }

    public function getAppId() {
        
        return $this->appId;
        
    }
    
    public function setAppId( $app_id) {
        
        $this->appId = $app_id;
        
    }

    public function getUserId() {
        
        return $this->userId;
        
    }

    public function setUserId( $user_id ) {
        
        $this->userId = $user_id;
        
    }

    public function getUserToken() {
        
        return $this->userToken;
        
    }
    
    public function getUserRole() {
        
        return $this->userRole;
        
    }

    public function setUserRole( $user_roles ) {

        $this->userRole = $user_roles;

    }
    
    public function setUserToken( $user_token ) {
        
        $this->userToken = $user_token;
        
    }
    

    public function getDeviceType() {
        
        return $this->deviceType;
        
    }
    
    public function setDeviceType( $device_type ) {
        
        $this->deviceType = $device_type;
        
    }

    public function getMissingProperties() { // return map (array)
        
        return $this->missingProperties;
        
    }
    
    public function setMissingProperties( $missing_properties ) { // set map (array)
        
        $this->missingProperties = $missing_properties;
        
    }
    
    public function getPrematureResult() { // return object
        
        return $this->prematureResult;
        
    }

    public function setPrematureResult( $premature_result ) {
        
        $this->prematureResult = $premature_result;
        
    }
    
    public function getConvertedToArray(){
        
        $properties =  ["___jsonclass" => Config::$CORE["runner_context"] ];
        
        return array_merge( $properties, get_object_vars($this) );
        
    }
    
}
