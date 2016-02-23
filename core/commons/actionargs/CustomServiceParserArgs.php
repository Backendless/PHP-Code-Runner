<?php
namespace backendless\core\commons\actionargs;

class CustomServiceParserArgs {

  private   $file_name;
  private   $file_type;

  public function __construct( $args_array ) {
      
      $this->setFileName( $args_array['fileName'] )
           ->setFileType( $args_array['fileType'] );
      
  }

  public function getFileName() {
      
    return $this->file_name;
    
  }

  public function setFileName( $fileName ) {
      
      $this->file_name = $fileName;
      return $this;
      
  }
  
  public function getFileType() {
      
    return $this->file_type;
    
  }

  public function setFileType( $file_type ) {
      
    $this->file_type = $file_type;
    return $file_type;
    
  }
  
}