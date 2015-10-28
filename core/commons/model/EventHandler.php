<?php
namespace backendless\core\commons\model;


class EventHandler
{
    private $id;
    private $async;
    private $target;
    private $timer;
    private $provider;

    public function getId() {
        
      return $this->id;
      
    }

    public function setId( $id ) {
        
      $this->id = $id;
      
    }

    public function isAsync() {
        
      return $this->async;
      
    }

    public function setAsync( $async ) {
        
      $this->async = $async;
      
    }

    public function isTimer() {
        
      return $this->timer;
      
    }

    public function setTimer( $timer ) {
        
      $this->timer = $timer;
      
    }

    public function getTarget() {
      
        return $this->target;
    }

    public function setTarget( $target ) {
        
      $this->target = $target;
      
    }

    public function getProvider() {
        
      return $this->provider;
      
    }

    public function setProvider( $provider ) {
        
      $this->provider = $provider;
      
    }
    
    public function getVars(){
        
        return get_object_vars($this);
        
    }

}

