<?php return [
    
                'lang'                      =>      'PHP',
    
                'register_runner_link'      =>      '/servercode/registerRunner',
                'unregister_runner_link'    =>      '/servercode/unregisterRunner',
                'external_host'             =>      '/servercode/externalhosts',
    
                // events service
                'register_model_link'       =>      '/servercode/registermodel',
                'publish_code'              =>      '/servercode/publishcode',
    
                // hosted service
                'register_hosted_model_link'=>      '/servercode/services/debug',
                'delete_hosted_model_link'  =>      '/servercode/services/debug',
                'hosted_publish_code'       =>      '/servercode/services',
    
    
    
                // interface class name for hosted service
                "hosted_interface_name"     =>      "IBackendlessService",
    
                //naming java classes 
                "invocation_result"         =>      "com.backendless.coderunner.commons.protocol.InvocationResult",
                "runner_context"            =>      "com.backendless.servercode.RunnerContext",
                "execution_result"          =>      "com.backendless.servercode.ExecutionResult",
                "exception_wrapper"         =>      "com.backendless.commons.exception.ExceptionWrapper",
    
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

    
                'hosted_service' => [
                    
                                        'endpoint_url'  =>  "http://localhost:9000",
                                        'server_root_url'   =>  "http://localhost:9000",
                                        'server_port'   =>  "8080",
                                        'server_name'   =>  "localhost",
                                        'code_format_type'  =>  "11", 
                                        "generation_mode"    =>  "FULL", 
                    
                                        "service_arg_mapping" => 'serviceArgMapping',
                                        "data_model_properties" =>'dataModelProperties',
                    
                                    ],

                'logging_in_cloud_mode'   => false
    
            ];
