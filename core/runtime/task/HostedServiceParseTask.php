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
        
        Log::writeInfo("Called invocation task task: " . $this->rai, $target = 'file' );

        if( $this->rai == null ) {
            
            Log::writeInfo("Something is null in InvocationActionTask...");
            return;
            
        }
        
        try{       
            
            $path_to_hosted = PathBuilder::getHostedService( $this->rai->getAppVersionId(), $this->rai->getRelativePath() );
            
            $parser = HostedServiceParser::getInstance()->parseModelRAI( $path_to_hosted ); 

            if( $parser->isError() ) {
                
                Log::writeError( $parser->getError()['msg'] );
                return ResponderProcessor::sendResult( $this->rai->getId(), $parser->getError() ); 
                
            }
            
            $runtime = [
                        
                            'path'           => $path_to_hosted,
                            'endpointURL'    => Config::$CORE['hosted_service']['endpoint_url'],
                            'serverRootURL'  => Config::$CORE['hosted_service']['server_root_url'],
                            'serverPort'     => Config::$CORE['hosted_service']['server_port'],
                            'serverName'     => Config::$CORE['hosted_service']['server_name'],
                            'codeFormatType' => Config::$CORE['hosted_service']['code_format_type'], 
                            "generationMode" => Config::$CORE['hosted_service']['generation_mode'], 
                            'randomUUID'     => mt_rand( 100000000, PHP_INT_MAX ),
                
                        ];
            
            $xml_manager = new XmlManager();
            
            $invocation_result = new InvocationResult();
            $invocation_result->setArguments( ["xml" => $xml_manager->buildXml( $parser->getParsedData(), $runtime ), "config" => $parser->getConfigListAsArray() ] );
            
//            $xml = $xml_manager->buildXml( $hosted_parser->getParsedData(), $runtime );
//            file_put_contents("../repo/e3bd3a54-9a07-6160-ff70-a824a9610800/servercode/services/E3BD3A54-9A07-6160-FF70-A824A9610800.xml", $xml);
//            echo $xml; return;
            
            ResponderProcessor::sendResult( $this->rai->getId(), $invocation_result );
            
        } catch( Exception $e ) { 
            
            Log::writeError( $e->getMessage() );
            
        }
    
  }
      
}
