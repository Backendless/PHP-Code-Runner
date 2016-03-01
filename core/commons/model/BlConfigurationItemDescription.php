<?php
namespace backendless\core\commons\model;

class BlConfigurationItemDescription                                    
{
    
  private $name;
  private $display_name;
  private $type;
  private $default_value;
  private $required;
  private $tooltip;
  
  private $options;
  private $hint;
  private $local_service_version;
  private $order = 0;
  
  public function __construct( $props, $name, $default_value ) {
      
      $this->name = $name;
      
      $this->display_name = ( isset( $props["displayName"] ) ) ? $props["displayName"] : "";
      $this->tooltip      = ( isset( $props["tooltip"] ) ) ? $props["tooltip"] : "";
      $this->required     = ( isset( $props["required"] ) ) ? false : "";
      $this->options      = ( isset( $props["options"] ) ) ? $props["options"] : [];
      $this->order        = ( isset( $props["order"] ) ) ? $props["options"] : -1;
      
      $this->default_value = ( !empty( $default_value ) ) ? $default_value : null;
      
      $this->type = "STRING";
      
      if( count( $this->options ) > 0 ) {
          
          $this->type = "CHOICE";
          
      }
      
      if( $this->default_value === true || $this->default_value === false ) {
          
          $this->type = "BOOL";
          
      }
      
      if( is_string( $this->default_value ) ) {
          
        if( strtotime( $this->default_value ) != false ) {

            $this->default_value = strtotime( $this->default_value ) * 1000;
            $this->type = "DATE";


        }
        
      }
      
  }
  
  public function getAsArray() {
      
      $data_array = [];
      
      $data_array["name"]           = $this->name;
      $data_array["displayName"]    = $this->display_name;
      $data_array["tooltip"]        = $this->tooltip;
      $data_array["required"]       = $this->required;
      $data_array["options"]        = $this->options;
      $data_array["order"]          = $this->order;
      $data_array["type"]          = $this->type;
      
      $data_array["defaultValue"]   = $this->default_value;
      
      return $data_array;
      
  }
  
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
