<?php return [
    
                'lang'                      =>      'PHP',
                'register_model_link'       =>      '/servercode/registermodel',
                'register_runner_link'      =>      '/servercode/registerRunner',
                'unregister_runner_link'    =>      '/servercode/unregisterRunner',
                'external_host'             =>      '/servercode/externalhosts',
                'publish_code'              =>      '/servercode/publishcode',
    
    
                "___jsonclass"              =>      "com.backendless.coderunner.commons.protocol.InvocationResult",
    
                'definition_source_path'    =>  [ 'core', 'commons', 'definition' ], //     core/commons/definition

    
                'provider'  => [
                                    'DATA'      =>  [ 'path' => 'backendless\core\extention\BasePersistenceEventHandler','asset' => true ],
                                    'FILE'      =>  [ 'path' => 'backendless\core\extention\BaseFilesEventHandler', 'asset' => true ],
                                    'GEO'       =>  [ 'path' => 'backendless\core\extention\BaseGeoEventHandler', 'asset' => true ],
                                    'MEDIA'     =>  [ 'path' => 'backendless\core\extention\BaseMediaEventHandler', 'asset' => false ],
                                    'MESSAGING' =>  [ 'path' => 'backendless\core\extention\BaseMessagingEventHandler', 'asset' => true ],
                                    'TIMER'     =>  [ 'path' => 'backendless\core\extention\BaseTimer', 'asset' => false ],
                                    'USER'      =>  [ 'path' => 'backendless\core\extention\BaseUserEventHandler', 'asset' => false ],
                                    'CUSTOM'    =>  [ 'path' => 'backendless\core\extention\BaseCustomEventHandler', 'asset' => false ]
                    
                               ],
    
                'shutdown_code'         =>  1 << 15,
                'local_shutdown_code'   =>  1 << 14, 
                'to_code_runner'        =>   'MAIN_EVENTS_CHANNEL',
                'tmp_dir_path'          => '../.tmp',
                'is_cloud_debug_mode'   => false
    
            ];
