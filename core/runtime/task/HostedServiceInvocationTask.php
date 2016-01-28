<?php
namespace backendless\core\runtime\task;

use backendless\core\runtime\concurrent\Runnable;
use backendless\core\processor\ResponderProcessor;
use backendless\core\lib\Log;
use backendless\core\commons\InvocationResult;
use backendless\core\GlobalState;
use backendless\core\util\PathBuilder;
use ReflectionMethod;
use backendless\Backendless;
use backendless\commons\InvocationContext;
use backendless\core\commons\holder\HostedModelHolder;
use backendless\core\util\HostedMapper;
use Exception;


class HostedServiceInvocationTask extends Runnable
{
    
    private $sdk_loader; 
    private $rsi;
    private static $xml_model;

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
            
            if( GlobalState::$TYPE == 'CLOUD') {
            
                $xml_path = realpath( PathBuilder::getHostedService( $this->rsi->getAppVersionId(), $this->rsi->getRelativePath() ) . DS . ".." ) . DS . $this->rsi->getAppVersionId() . ".xml";
            
                HostedModelHolder::setXMLModel( file_get_contents( $xml_path ) ); // load xml from file to holder
                
            }
            
            $this->initSdk();
            
            $instance_class_name = $this->rsi->getClassName();
            $arguments = $this->rsi->getArguments();
            
            $hosted_mapper = new HostedMapper();
            
            $hosted_mapper->prepareArguments(   
                                                $arguments,
                                                $this->rsi->getMethod()    
                                            );
            if( $hosted_mapper->isError() ) {
                
                Log::writeError( $hosted_mapper->getError()['msg'] );
                return ResponderProcessor::sendResult( $this->rsi->getId(), $hosted_mapper->getError() );
                
            }
            
            $reflection_method = new ReflectionMethod( $this->rsi->getClassName(), $this->rsi->getMethod() );

            $result = $reflection_method->invokeArgs( new $instance_class_name(), $arguments );

            $invocation_result = new InvocationResult();
            $hosted_mapper->prepareResult( $result );
            $invocation_result->setArguments( $result );
            
            ResponderProcessor::sendResult( $this->rsi->getId(), $invocation_result );
            
                
        } catch( Exception $e ) { 
            
            Log::writeError( $e->getMessage() );
            
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
