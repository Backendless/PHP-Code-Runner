<?php
namespace backendless\core\lib;


class Log {
    
    protected static $app_log_file = "application.log";
    protected static $app_log_dir = "log";
    protected static $is_colored_glob = true;
    
    protected static $log_path;

    protected static $is_write = true;
    
    protected static $colors = [ 'blue' => '0;34', 'yellow' => '1;33', 'red' => '0;31' ];
    
    public static function init( $os_type = '', $mode, $is_debug ) {
        
        self::$log_path =  BP . DS . self::$app_log_dir ;
        
        if( !file_exists( self::$log_path ) ) {
            
            mkdir( self::$log_path );
            
        }
        
        if( $os_type == "WIN") {
            
            self::$is_colored_glob = false;
            
        }
        
        if( $mode == 'CLOUD' && $is_debug == false ) {
            
            self::$is_write = false;
            
        }
        
    }

    public static function write( $msg, $target = 'all', $colored = true ) {
        
        self::doWrite( $msg, $target, "\r", $colored, 'none');
                          
    }
    
    public static function writeInfo( $msg, $target = 'all', $colored = true ) {
        
        self::doWrite( $msg, $target, "[INFO]", $colored, 'blue');
                          
    }
    
    public static function writeWarn( $msg, $target = 'all', $colored = true ) {
        
        self::doWrite( $msg, $target, "[WARN]", $colored, 'yellow');
                          
    }
    
    public static function writeError( $msg, $target = 'all', $colored = true ) {
        
        self::doWrite( $msg, $target, "[ERROR]", $colored, 'red');
                          
    }
    
    public static function writeTrace( $msg, $target = 'file', $colored = false ) {
        
        self::doWrite( $msg, $target, "[ERROR]", $colored, 'red');
                          
    }
    
    protected static function doWrite( $msg, $target, $msg_prefix, $colored, $color ) {
        
        if( $colored && self::$is_colored_glob ) {
            
            $msg_colored_prefix = self::addColor($msg_prefix, $color );
            
        }else{
            
            $msg_colored_prefix = $msg_prefix;
            
        }
        
        $space = '';
        
        if( strlen($msg_prefix) > 1 ) {
            
            $space = ' ';
            
        }
        
        $msg_colored = $msg_colored_prefix . $space . $msg;
        $msg = $msg_prefix . $space . $msg;
        
        if( $target == 'all') {
            
            self::writeToConsole($msg_colored);
            self::writeToFile($msg);
            
        }elseif( $target == 'console') {
            
            self::writeToConsole($msg_colored);
            
        }elseif($target == 'file') {
            
            self::writeToFile($msg);
            
        }
    }
    
    protected static function addColor( $string, $color ) {
        
        $colored_string = $string;
        
        if( isset(self::$colors[$color]) ) {
            
            $color_code = self::$colors[$color];
            $colored_string = "\033[" . $color_code . "m" . $string . "\033[0m";
        }
        
        return $colored_string;
        
    }
    
    protected static function writeToConsole( $msg ) {
        
        if( ! self::$is_write ) { return; } 
        
        $new_line = substr( $msg, -6);
        
        if( $new_line != '!<new>') {
            
            echo $msg . "\n";
            
        }else {
            
            echo substr( $msg, 0, -6);
            
        }
        
        
    }

    protected static function writeToFile( $msg ) {
        
        if( ! self::$is_write ) { return; } 
        
        $log_file_path = self::$log_path . DS . self::$app_log_file;
        
        if( !file_exists($log_file_path) ) {
            
            file_put_contents($log_file_path, '');
            
        }
        
        file_put_contents( $log_file_path, date("Y-m-d H:i:s" ,time()) ." " . $msg . "\n", FILE_APPEND );
        
    }
    
    public static function writeToLogFile( $msg ) {
        
        if( ! self::$is_write ) { return; } 
        
        $log_file_path = self::$log_path . DS . self::$app_log_file;
        
        file_put_contents( $log_file_path, $msg . "\n", FILE_APPEND );
        
    }
    
}
