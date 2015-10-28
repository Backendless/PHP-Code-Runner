<?php return [
    
                'lang'                      =>      'java',
                'register_model_link'       =>      '/servercode/registermodel',
                'register_runner_link'      =>      '/servercode/registerRunner',
                'unregister_runner_link'    =>      '/servercode/unregisterRunner',
                'external_host'             =>      '/servercode/externalhost',
                'publish_code'              =>      '/servercode/publishcode',
    
    
                'definition_source_path'    =>  [ 'core', 'commons', 'definition' ], //     core/commons/definition

    
                'provider'  => [
                                    'DATA'      =>  [ 'path' => 'backendless\core\extention\PersistenceExtender','asset' => true ],
                                    'FILE'      =>  [ 'path' => 'backendless\core\extention\FilesExtender', 'asset' => true ],
                                    'GEO'       =>  [ 'path' => 'backendless\core\extention\GeoExtender', 'asset' => true ],
                                    'MEDIA'     =>  [ 'path' => 'backendless\core\extention\MediaExtender', 'asset' => false ],
                                    'MESSAGING' =>  [ 'path' => 'backendless\core\extention\MessagingExtender', 'asset' => true ],
                                    'TIMER'     =>  [ 'path' => 'backendless\core\extention\TimerExtender', 'asset' => false ],
                                    'USER'      =>  [ 'path' => 'backendless\core\extention\UserExtender', 'asset' => false ],
                                    'CUSTOM'    =>  [ 'path' => 'backendless\core\extention\CustomExtender', 'asset' => false ]
                    
                               ],
    
                'shutdown_code'         =>  1 << 15,
                'local_shutdown_code'   =>  1 << 14, 
                'to_code_runner'        =>   'MAIN_EVENTS_CHANNEL',
                'tmp_dir_path'          => '../.tmp'
    
            ];
