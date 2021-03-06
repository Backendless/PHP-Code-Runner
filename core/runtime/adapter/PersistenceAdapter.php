<?php
namespace backendless\core\runtime\adapter;

use backendless\core\servercode\RunnerContext;
use backendless\core\util\ClassManager;
use ReflectionClass;
use backendless\core\util\ReflectionUtil;
use backendless\core\servercode\ExecutionResult;
use backendless\core\Config;


class PersistenceAdapter
{
    
    private static $ALL_CONTEXT = "*";

    public function adaptBeforeExecuting( $definition, $rmi, $arguments ) {
        
        if ( $definition['provider'] !== 'Data' ) {
            return $arguments;
        }

        if( $definition['name'] == 'beforeFindById' || $definition['name'] == 'afterFindById' ) {
            
            if( isset($arguments[2]) ) {
                
                $objects = $arguments[2];
                
                if( $objects != null && count($objects) == 0 ) {
                    
                    $arguments[2] = '';
                    
                }
                
            }

            
        } else if( $definition['name'] == 'beforeLoadRelations' || $definition['name'] == 'afterLoadRelations' ) {
            
            if( isset($arguments[3]) ) {
                
                $objects = $arguments[3];
                
                if( $objects != null && count($objects) == 0 ) {
                    
                    $arguments[3] = '';
                    
                }
                
            }
            
        } 
        
        // convert data to ExecutionResult class
        
        foreach ( $arguments as $arg_index=>$arg_val) {
            
            if( isset( $arg_val["___jsonclass"]) ) {
                
                if( $arg_val["___jsonclass"] == Config::$CORE["execution_result"] ) {
                    
                    $execution_result = new ExecutionResult();
                    $execution_result->setException( $arguments[$arg_index]["exception"] );
                    $execution_result->setResult( $arguments[$arg_index]["result"] );
                    $arguments[$arg_index] = $execution_result;
                    
                }
            }
            
        }
        
        $generic_index = $definition['generic_index']; // int 1

        if( $generic_index == null ) {
            
            return $arguments;
            
        }
        
        $arguments[0] = new RunnerContext( $arguments[0] );
        
        $declared_properties = $arguments[ $generic_index ];
        
        if( $rmi->getTarget()  === self::$ALL_CONTEXT ) {
      
            $arguments[ $generic_index ] = $declared_properties;
      
            return $arguments;
      
        }

        // model creation and fill data to  properties
        
        $arguments[1] = ClassManager::getClassInstanceByName( $declared_properties['___class'] );
        ReflectionUtil::fillClassProperties( $arguments[1], $declared_properties );
        
        //check extra data in declared_properties  

        $model_prop = ( new ReflectionClass( ClassManager::getFullClassName( $declared_properties['___class'] ) ) )->getProperties();

        $missing_properties = $declared_properties;
        
        foreach ( $model_prop as $property ) {

            $property->setAccessible( true );
                
            if( array_key_exists( $property->name, $declared_properties ) ) {
                
                unset( $missing_properties[ $property->name ] );
                        
            }
            
           
        }
        
        $arguments[0]->setMissingProperties( $missing_properties );

        return $arguments;
      
    }


    public function adaptAfterExecuting( $definition, $rmi, $arguments, $result ) {
  
        
        if ( $definition['provider'] !== 'Data' ) {
            
            return $arguments;
            
        }

        if( $rmi->getTarget() === self::$ALL_CONTEXT  ) {
            
           return $arguments;
           
        }
        
        $generic_index = $definition['generic_index'];

        if( $generic_index == null ) {  
            
           return $arguments;
           
        }
        
        $arguments[ $generic_index ] = array_merge( $arguments[0]->getMissingProperties(), ReflectionUtil::getClassPropertiesAsArray( $result[1]) );
        
        return $arguments;
        
    }
  
}
