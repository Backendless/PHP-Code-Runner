<?php
namespace backendless\core\servercode;


class AbstractContext {
    
    protected $appId;
    protected $userId;
    protected $userToken;
    protected $userRoles = [];
    protected $deviceType;
    
    public function __construct( $arguments ) {
        
        $this->appId        =   $arguments[ 'appId' ];
        $this->userId       =   $arguments[ 'userId' ];
        $this->userToken    =   $arguments[ 'userToken' ];
        $this->userRoles    =   $arguments[ 'userRoles' ];
        $this->deviceType   =   $arguments[ 'deviceType' ];
        
    }
    
    public function getAppId() {
        
        return $this->appId;
        
    }
    
    public function setAppId( $app_id) {
        
        $this->appId = $app_id;
        return $this;
        
    }

    public function getUserId() {
        
        return $this->userId;
        
    }

    public function setUserId( $user_id ) {
        
        $this->userId = $user_id;
        return $this;
         
    }

    public function getUserToken() {
        
        return $this->userToken;
        
    }
    
    public function getUserRole() {
        
        return $this->userRoles;
        
    }

    public function setUserRole( $user_roles ) {

        $this->userRoles = $user_roles;
        return $this;

    }
    
    public function setUserToken( $user_token ) {
        
        $this->userToken = $user_token;
        return $this;
        
    }
    
    public function getDeviceType() {
        
        return $this->deviceType;
        
    }
    
    public function setDeviceType( $device_type ) {
        
        $this->deviceType = $device_type;
        return $this;
        
    }
            
}
