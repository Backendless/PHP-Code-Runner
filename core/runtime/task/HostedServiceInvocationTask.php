<?php
namespace backendless\core\runtime\task;

use backendless\core\runtime\concurrent\Runnable;
use backendless\core\processor\ResponderProcessor;
use backendless\core\lib\Log;
use backendless\core\util\ClassManager;
use backendless\core\util\PathBuilder;
use ReflectionMethod;
use backendless\Backendless;
use backendless\commons\InvocationContext;
use backendless\core\util\HostedMapper;
use Exception;


class HostedServiceInvocationTask extends Runnable
{
    
    private $sdk_loader; 
    private $rsi;

    
    public function __construct( $rsi ) {

        $this->rsi = $rsi;
        $this->sdk_loader = DS . "backendless" . DS . "autoload.php";
        
    }

    public function runImpl() {
        
        Log::writeInfo("Called invocation task: " . $this->rsi, $target = 'file' );

        if( $this->rsi == null ) {
            
            Log::writeInfo("Something is null in InvocationActionTask...");
            return;
            
        }

        try{       
            
            $this->initSdk();
            $this->includeServiceClass();
            
            $hosted_mapper = new HostedMapper();
            
            $arguments = $this->rsi->getArguments();
            
            $hosted_mapper->prepareArguments( 
                                                $arguments, 
                                                PathBuilder::getHostedService( $this->rsi->getAppVersionId(), $this->rsi->getRelativePath() ),
                                                $this->rsi->getAppVersionId(),
                                                $this->rsi->getMethod()    
                                            );
            
            if( $hosted_mapper->isError() ) {
                
                Log::writeError( $hosted_mapper->getError() );
                return ResponderProcessor::sendResult( $this->rsi->getId(), $hosted_mapper->getError() );
                
            }

            $reflection_method = new ReflectionMethod( $this->rsi->getClassName(), $this->rsi->getMethod() );

            $instance_class_name = $this->rsi->getClassName();
            
            $result = $reflection_method->invokeArgs( new $instance_class_name(), $arguments );
            
            $hosted_mapper->prepareResult( $result );
            
            ResponderProcessor::sendResult( $this->rsi->getId(), $result );
            
                
        } catch( Exception $e ) { 
            
            Log::writeError( $e->getMessage() );
            
        }
    
  }
  
    private function includeServiceClass() {
      
        $path = ClassManager::getPathByName( $this->rsi->getClassName() );

        if( $path != null ) {

            include $path;

        } else {

            $msg = "CodeRunner can't find RSI class: "; 

            throw new Exception( $msg . $this->rsi->getClassName() );

            ResponderProcessor::sendResult( $this->rsi->getId(), [ 'code' => 404, 'msg' => $msg ] );

        }
      
    }
  
    private function initSdk() {

        $invocation_context = new InvocationContext( $this->rsi->getInvocationContext() );
        $init_app_data = $this->rsi->getInitAppData();

        Backendless::setUrl( $init_app_data->getUrl() );
        Backendless::initApp( $invocation_context->app_id, $init_app_data->getSecretKey(),  $init_app_data->getAppVersionName() );
        Backendless::switchOnBlMode();
        Backendless::setInvocationContext( $invocation_context );

    }
     
}
