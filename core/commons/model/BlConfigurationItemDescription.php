<?php
namespace backendless\core\commons\model;

class BlConfigurationItemDescription                                    
{
    
  private $name;
  private $display_name;
  private $type;
  private $default_value;
  private $required;
  
  private $options;
  private $hint;
  private $local_service_version;
  private $order = 0;
  
  public function setName( $name ) {
  
      $this->name = $name;
      return $this;
    
  }
  
  public function getName(){
      
      return $this->name;
      
  }

  public function setDisplayName( $display_name ) {
      
      $this->display_name = $display_name;
      return $this;
      
  }
  
  public function getDisplayName( ) {
      
      return $this->display_name;
      
  }
  
  public function setType( $type) {
      
      $this->type = $type;
      return $this;
      
  }
  
  public function getType() {
      
      return $this->type;
      
  }
  
  public function setDefaultValue( $default_value ) {
      
      $this->default_value = $default_value;
      return $this;
      
  }
  
  public function getDefaultValue() {
      
      return $this->default_value;
      
  }
  
  public function setRequired( $required ) {
      
      $this->required = $required;
      return $this;
      
  }  
  
  public function getRequired( ) {
      
      return $this->required;
      
  }  
    
}
