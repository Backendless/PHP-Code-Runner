<?php
namespace backendless\core\runtime\task;

use backendless\core\runtime\concurrent\Runnable;
use backendless\core\parser\HostedServiceParser;
use backendless\core\processor\ResponderProcessor;
use backendless\core\commons\InvocationResult;
use backendless\core\util\PathBuilder;
use backendless\core\util\XmlManager;
use backendless\core\Config;
use backendless\core\lib\Log;
use Exception;


class HostedServiceParseTask extends Runnable
{
    
    private $rai;

    
    public function __construct( $rai ) {

        $this->rai = $rai;
        
    }

    public function runImpl() {
        
        Log::writeInfo("Called invocation task: " . $this->rai, $target = 'file' );

        if( $this->rai == null ) {
            
            Log::writeInfo("Something is null in InvocationActionTask...");
            return;
            
        }

        try{       
            
            $hosted_parser = new HostedServiceParser( 
                                                        PathBuilder::getHostedService( $this->rai->getAppVersionId(), $this->rai->getRelativePath() ), 
                                                        $this->rai->getId() 
                                                    );
            
            $hosted_parser->parseFolderWithCustomCode(); 
            
            if( $hosted_parser->isError() ) {
                
                Log::writeError( $hosted_parser->getError() );
                return ResponderProcessor::sendResult( $this->rai->getId(), $hosted_parser->getError() ); 
                
            }
            
            
            $runtime = [
                        
                        'path'  =>  "TODO",
                        'endpointURL' => Config::$CORE['hosted_service']['endpoint_url'],
                        'serverRootURL' => Config::$CORE['hosted_service']['server_root_url'],
                        'serverPort'    =>  Config::$CORE['hosted_service']['server_port'],
                        'serverName'    =>  Config::$CORE['hosted_service']['server_name'],
                        'codeFormatType'    =>  Config::$CORE['hosted_service']['code_format_type'], 
                        "generationMode"    =>  Config::$CORE['hosted_service']['generation_mode'], 
                        'randomUUID'    =>  "TODO",
                
            ];
            
            $xml_manager = new XmlManager();
            
            $invocation_result = new InvocationResult();
            $invocation_result->setArguments( ["xml" => $xml_manager->buildXml( $hosted_parser->getParsedData(), $runtime ) ] );
            
            ResponderProcessor::sendResult( $this->rai->getId(), $invocation_result );
            
            //echo $xml_manager->buildXml( $hosted_parser->getParsedData(), $runtime );
                
        } catch( Exception $e ) { 
            
            Log::writeError( $e->getMessage() );
            
        }
    
  }
      
}
