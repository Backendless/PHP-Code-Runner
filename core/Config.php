<?php 
namespace backendless\core;

class Config
{
    
    private static $RUNNER_PROPERTIES_FILE = "config.php";
    private static $CORE_CONFIG_FILE = "core_conf.php";
    private static $VERSION_FILE = "version.php";

    public static $need_save_keys = false;
    public static $APPLICATION_ID;
    public static $SECRET_KEY;
    public static $APP_VERSION;

    public static $CLASS_LOCATION;

    public static $SERVER_URL;
    public static $REDIS_HOST;
    public static $REDIS_PORT;

    public static $EXECUTOR_CORE_POOL_SIZE;

    public static $REPO_PATH;
    public static $FULL_REPO_PATH;

    public static $AUTO_PUBLISH;

    public static $ALLOWED_HOSTS;
    
    public static $RELATIVE_PATH = null;
    
    public static $STATUS = "unregistered";

    public static $TASK_APPLICATION_ID;
    
    public static $DEBUG_ID;
    public static $DEBUG_PID;
    
    public static $CORE;
    
    public static $VERSION;

    public static function loadConfig() {
      
        $config = include BP . DS . self::$RUNNER_PROPERTIES_FILE;

        self::$APPLICATION_ID = ( is_string( $config['application_id']) ) ? trim( $config['application_id'] ) :  $config['application_id'];
        self::$SECRET_KEY = ( is_string( $config['application_secret_key']) ) ? trim( $config['application_secret_key'] ) : $config['application_secret_key'];
        
        self::$APP_VERSION = ( isset($config['application_version']) ) ?  trim( $config['application_version'] ) : 'v1';
        
        if( self::$APP_VERSION == '<application_version>' || self::$APP_VERSION == '' ) { self::$APP_VERSION = null; }

        self::$CLASS_LOCATION = ( isset($config['location_classes']) ) ?  trim( $config['location_classes'] ) : '..' . DS .'classes';
        self::$CLASS_LOCATION = str_replace( '/', DS, self::$CLASS_LOCATION );

        self::$SERVER_URL = ( isset($config['system_server_url']) ) ?  trim( $config['system_server_url'] ) : 'api.backendless.com';
        self::$REDIS_HOST = ( isset($config['system_redis_host']) ) ?  trim( $config['system_redis_host'] ) : 'cl.backendless.com';      
        self::$REDIS_PORT = ( isset($config['system_redis_port']) ) ?  trim( $config['system_redis_port'] ) : '6379';      

        self::$EXECUTOR_CORE_POOL_SIZE = ( isset($config['system_pool_core']) ) ?  trim( $config['system_pool_core'] ) : '20';

        self::$REPO_PATH = ( isset($config['system_repo_path']) ) ?  trim( $config['system_repo_path'] ) : '..' . DS . 'repo' . DS;
        self::$REPO_PATH = str_replace( '/', DS, self::$REPO_PATH );

        self::$FULL_REPO_PATH = " not set in class Config.php"; 

        GlobalState::$TYPE = ( isset($config['system_type']) ) ?  trim( $config['system_type'] ) : 'LOCAL';  
        
        if( isset( $config['enterprise_allowed_hosts'] ) ) {

            if( is_array($config['enterprise_allowed_hosts']) ) {

              foreach ( $config['enterprise_allowed_hosts'] as $host_number => $host) {
                  $config['enterprise_allowed_hosts'][$host_number] = trim($host);
              }

              self::$ALLOWED_HOSTS = $config['enterprise_allowed_hosts'];

            }else{

                self::$ALLOWED_HOSTS = trim($config['enterprise_allowed_hosts']);
            }

        }else{

            self::$ALLOWED_HOSTS = 'api.backendless.com:9000';

        }

        self::$VERSION = include BP . DS ."core" . DS . self::$VERSION_FILE;
        
        self::loadCoreConfig();
        self::detectOsType();

  }

    public static function saveKeys() {

        if( self::$need_save_keys == true ) {
            
            $config = include BP . DS . self::$RUNNER_PROPERTIES_FILE;

            $config['application_id'] = self::$APPLICATION_ID;
            $config['application_secret_key'] = self::$SECRET_KEY;
            $config['application_version']  = self::$APP_VERSION;

            file_put_contents( BP . DS . self::$RUNNER_PROPERTIES_FILE, "<?php return " . var_export( $config, true ) . "; \n" );
        }

    }
  
    public static function loadCoreConfig() {

        self::$CORE = include BP . DS ."core" . DS . self::$CORE_CONFIG_FILE;

    }
    
    private static function detectOsType() {
        
        if ( strncasecmp(PHP_OS, 'WIN', 3) != 0 ) {
            
            self::$CORE['os_type'] = '*nix';
            
        }else {
        
            self::$CORE['os_type'] = 'WIN';
            
        }
        
    }
    
    public static function setServerUrl( $url ) {
        
        self::$SERVER_URL = $url;
        
    } 
    
}
