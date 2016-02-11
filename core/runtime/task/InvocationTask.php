<?php
namespace backendless\core\runtime\task;

use backendless\core\runtime\concurrent\Runnable;
use backendless\core\commons\holder\EventDefinitionHolder;
use backendless\core\runtime\adapter\ArgumentAdapterList;
use backendless\core\runtime\adapter\PersistenceAdapter;
use backendless\core\runtime\adapter\UserAdapter;
use backendless\core\runtime\adapter\MessagingAdapter;
use backendless\core\runtime\adapter\CustomHandlerAdapter;
use backendless\core\processor\ResponderProcessor;
use backendless\core\commons\InvocationResult;
use backendless\core\util\ClassManager;
use backendless\exception\BackendlessException;
use backendless\core\lib\Log;
use backendless\Backendless;
use ReflectionClass;
use ReflectionMethod;



class InvocationTask extends Runnable
{
    
    private static $event_definition_holder;
    private static $argument_adapter_list;

    private $rmi;
    private $event_handler;
    
    public function __construct( $rmi, $event_handler ) {

        parent::__construct();

        $this->rmi = $rmi;
        $this->event_handler = $event_handler;
        
        self::$event_definition_holder = EventDefinitionHolder::getInstance();
        
        
        self::$argument_adapter_list = new ArgumentAdapterList();
        
        self::$argument_adapter_list->registerAdapter( new PersistenceAdapter() );
        self::$argument_adapter_list->registerAdapter( new UserAdapter() );
        self::$argument_adapter_list->registerAdapter( new MessagingAdapter() );
        self::$argument_adapter_list->registerAdapter( new CustomHandlerAdapter() );
        
    }

    public function runImpl() {
        
        Log::writeInfo("Called invocation task: " . $this->rmi, $target = 'file' );

        if( $this->rmi == null || $this->event_handler == null) {
            
            Log::writeInfo("Something is null in InvocationTask...");
            return;
            
        }

        $invocation_result = new InvocationResult();
        
        try {
            
                $definition = self::$event_definition_holder->getDefinitionById( $this->rmi->getEventId() );
                
                $arguments = self::$argument_adapter_list->beforeExecuting($definition, $this->rmi, $this->rmi->getDecodedArguments() );
                
                if( $definition['name'] == 'handleEvent' ) {
                    
                    //        Object context = arguments[ 0 ];
                    //        Class runnerContextClass = classLoader.loadClass( RunnerContext.class.getName() );
                    //        List<String> userRoleList = (List<String>) runnerContextClass.getMethod( "getUserRole" ).invoke( context );
                    //
                    //        String[] userRoles = userRoleList == null ? null : userRoleList.toArray( new String[ userRoleList.size() ] );
                    //        String userId = (String) runnerContextClass.getMethod( "getUserId" ).invoke( context );
                    //        AccessValidator.validateAccess( clazz, userRoles, userId );
                    
                }
                
                $instance_class_name = $this->event_handler->getProvider();
                
                $method = self::findMethod( $instance_class_name, $definition, count( $arguments ) );
                
                // bootstrap onEnter action
                $backendless_globals = ClassManager::getClassInstanceByName("BackendlessGlobals");
                $backendless_globals->onEnter( $instance_class_name, $method, $arguments );
                // end bootstrap onEnter action
                
                // switch sdk from rest mode to bl 
                Backendless::switchOnBlMode();
                
                $reflection_method = new ReflectionMethod($instance_class_name, $method);
                
                $result = $arguments; // invokeArgs pass $arguments as link and we get changed data after invoke
                
                $returned_result = $reflection_method->invokeArgs( new $instance_class_name(), $result );
                
                if( $returned_result !== null ) {
                    
                    $result = $returned_result;
                    
                }
                
                // bootstrap onExit action
                $backendless_globals->onExit( $instance_class_name, $method, $result );
                // end bootstrap onExit action
                
                if( $this->rmi->isAsync() ) {
                    
                    return;
                    
                }

                $arguments = self::$argument_adapter_list->afterExecuting( $definition, $this->rmi, $arguments, $result );
                
                
                if( is_a( $arguments[0], "\backendless\core\servercode\RunnerContext" ) ){
                    
                     $arguments[0] = $arguments[0]->getConvertedToArray();
                    
                }

                $invocation_result->setArguments( $arguments );
                
                    
                ResponderProcessor::sendResult( $this->rmi->getId(), $invocation_result );
                
        } catch( Exception $e ) { 
            
            Log::writeError( $e->getMessage() );
            
        } catch ( BackendlessException $e ) {
            
            Log::writeError( "In Backendless SDK occurred error with message: \"" . $e->getMessage() ."\"" );
            exit();
            
        }
    
  }

    private static function findMethod( $class, $definition, $args_size ) {
        
        $reflection = new ReflectionClass( $class );
        
        $methods = $reflection->getMethods();
        
        foreach( $methods as $method) {
            
            if( $method->name !== $definition['name'] ) {
                
                continue;
                
            }
            
            if( $definition['name'] == 'handleEvent' ) {
                
                return $method->name;
                
            }
            
            if( count($reflection->getMethod($method->name)->getParameters()) != $args_size && $definition['name'] != 'handleEvent' ) {
            
                continue;
              
            }
            
            return $method->name;
            
        }
        
        return null;
        
    }


    public function __toString() {

        return "InvocationTask[ application id:" . $this->application_id . " version id:" . $this->getAppVersionId() . " ]";

    }

    public function getTimeout() {

        return $this->rmi->getTimeout();

    }

    public function setTimeout( $timeout ) {

        parent::setTimeout($timeout);
        $this->rmi->setTimeout($timeout);
        
    }
    
    public function getAppVersionId() {
        
        return $this->rmi->getAppVersionId();
        
    }

    public function getApplicationId() {
        
        return (!isset($this->rmi) ) ? null : $this->rmi->getApplicationId();
        
    }

    public function cleanUp() {
    
        $this->rmi = null;
        $this->event_handler = null;
    
    }
    
}
