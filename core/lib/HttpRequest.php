<?php
namespace backendless\core\lib;
use backendless\core\lib\Log;

class HttpRequest
{
    protected $target_url;
    protected $request_headers;
    
    protected $response_headers;
    protected $response;
    
    protected $response_code;
    protected $response_status;

    public function __construct() {
        
        $this->headers = [];
        
    }
    
    private function resetResponse() {
        
        $this->response = null;
        $this->response_code = null;
        $this->response_status = null;
        $this->response_headers = null;
        
    }

    public function setTargetUrl( $target ) {
        
        $target = trim($target);
        
        if( ! preg_match("/http:\/\/|https:\/\//", $target, $matches) ) {
            
            $target = "http://" . $target;
            
        }
        
        $this->target_url = $target;
        
        return $this;
        
    }
    
    public function setHeader( $name, $val ) {
      
        $this->request_headers[$name] = $val;
        
        return $this;
        
    }
    
    public function request( $content, $method ='POST' ) {
        
        $this->resetResponse();
        
        //$this->request_headers['Content-length'] = mb_strlen( $content , '8bit' );    
                    
        $headers = array_map( 
                                function ($v, $k) { 
                                    return sprintf("%s: %s", $k, $v); 
                                    
                                }, 
                                $this->request_headers,
                                array_keys($this->request_headers)
                            );
        
        
        $opts = [                            
                'http' => [
                            'ignore_errors' => true,
                            'method' => $method,
                            'header' => implode("\r\n", $headers), 
                            'content' => $content,
                            'timeout' => 10 
                          ],
            
                'ssl' => [
                            'verify_peer' => false,                                                                             //1
                            'allow_self_signed' => false,
                            //'cafile' => '/etc/ssl/certs/ca-certificates.crt', // <-- EDIT FOR NON-DEBIAN/UBUNTU SYSTEMS       //1    
                    
                         ]
                ];
        
        
        $url_info = parse_url( $this->target_url );
        
        if( isset( $url_info["port"] ) ) {
            
            //$opts[ 'socket'] = [ 'bindto' => '0:'.$url_info["port"] ];
            $opts[ 'socket'] = [ 'bindto' => '0:0' ];
            
        }
        
        $context = stream_context_create( $opts );

        Log::writeInfo( "HTTP request to: $this->target_url with headers: " . json_encode($this->request_headers) ,  "file");        
        
        $this->response = @file_get_contents( $this->target_url, false, $context );
        
        $this->response_headers = $http_response_header;
        
        $this->analyzeResponce();
        
    }
    
    protected function analyzeResponce() {
        
        if( $this->getResponseCode() != 200 ) {
            
            Log::writeError( "Fail HTTP request to: $this->target_url  Response code: " . $this->getResponseCode() . " Response status: " . $this->getResponseStatus(). " Response body: \"" . $this->getResponce() . "\"", "file");        
            
            $msg = '';
            
            if( $this->isJson( $this->getResponce() ) ) {
                
                $json_array = json_decode( $this->getResponce(), true );
                
                $msg = "Code: " . $json_array["code"] . " Message: " . $json_array['message'];
                
            } else {
                
                $msg = $this->getResponce();
                
            }
            
            Log::writeError( "Server response: \"" . $msg . "\"" );
           
        } else {
            
            Log::writeInfo( "Success HTTP request to: $this->target_url", "file");        
            
        }
        
    }
    
    public function getResponseCode(){

        if( isset( $this->response_code ) ) {
            
            return $this->response_code;
            
        } else{
            
            $this->parseResponseCode();
        }
        
        return $this->response_code;
        
    }
    
    public function getResponseStatus() {
        
        if( isset($this->response_status) ) {
            return $this->response_status;
        } else{
            $this->parseResponseCode();
        }
        
        return $this->response_status;
        
    }
    
    protected function parseResponseCode() {
        
        foreach ($this->response_headers as $key => $header) {
            
            if (strpos($header, 'HTTP') !== FALSE) {
                list(,  $this->response_code, $this->response_status) = explode(' ', $header);
                
            }
        }
        
    }
    
    public function getResponseHeader( $header ) {
        
        if( isset($this->response_headers) ) {
            
            foreach ($this->response_headers as $key => $response_header) {
            
                if ( stripos($response_header, $header) !== false ) {

                    list($headername, $headervalue) = explode(":", $response_header);
                    return trim($headervalue);

                }
            }
        }
        
        return null;
        
    }
    
    public function getResponce() {
        
        return $this->response;
        
    }
    
    public function isJson( $string ) {
        
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
        
    }
  
}