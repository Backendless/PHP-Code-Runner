<?php
namespace backendless\core\commons\holder;

use backendless\core\commons\definition\DefinitionReader;
use backendless\core\lib\Log;

class EventDefinitionHolder
{
    private static $_instance;
    
    private $holder;
    private $name_holder;
    
    private function __construct() {
        
        $this->holder = [];
        $this->name_holder = [];
        
    }

    public static function getInstance() {
        
        if (!self::$_instance) {
            self::$_instance = new EventDefinitionHolder();
        }
        return self::$_instance;
        
    }
    
    public function load() {

        
        $data = DefinitionReader::getDefinition('Data');
        
        foreach ( $data as $name => $propertys) {
            
            $this->holder[$propertys['id']] = [ 'name' => $name, 'provider' => 'Data', 'generic_index' => $propertys['index'] ];
            $this->name_holder["DATA" . "_" . $name] = $propertys;
            
        }
        
        $data = DefinitionReader::getDefinition('User');
        
        foreach ( $data as $name => $propertys) {
            
            $this->holder[$propertys['id']] = [ 'name' => $name, 'provider' => 'User', 'generic_index' => $propertys['index'] ];
            $this->name_holder["USER" . "_" . $name] = $propertys;
            
        }
        
        
        $data = DefinitionReader::getDefinition('Timer');
        
        foreach ( $data as $name => $propertys) {
            
            $this->holder[$propertys['id']] = [ 'name' => $name, 'provider' => 'Timer', 'generic_index' => $propertys['index'] ];
            $this->name_holder["TIMER" . "_" . $name] = $propertys;
            
        }
        
        
        $data = DefinitionReader::getDefinition('Custom');
        
        foreach ( $data as $name => $propertys) {
            
            $this->holder[$propertys['id']] = [ 'name' => $name, 'provider' => 'Custom', 'generic_index' => $propertys['index'] ];
            $this->name_holder["CUSTOM" . "_" . $name] = $propertys;
            
        }
        
        $data = DefinitionReader::getDefinition('File');
        
        foreach ( $data as $name => $propertys) {
            
            $this->holder[$propertys['id']] = [ 'name' => $name, 'provider' => 'File', 'generic_index' => $propertys['index'] ];
            $this->name_holder["FILE" . "_" . $name] = $propertys;
            
        }
        
        $data = DefinitionReader::getDefinition('Geo');
        
        foreach ( $data as $name => $propertys) {
            
            $this->holder[$propertys['id']] = [ 'name' => $name, 'provider' => 'Geo', 'generic_index' => $propertys['index'] ];
            $this->name_holder["GEO" . "_" . $name] = $propertys;
            
        }        
        
        $data = DefinitionReader::getDefinition('Media');
        
        foreach ( $data as $name => $propertys) {
            
            $this->holder[$propertys['id']] = [ 'name' => $name, 'provider' => 'Media', 'generic_index' => $propertys['index'] ];
            $this->name_holder["MEDIA" . "_" . $name] = $propertys;
            
        }   
        
        $data = DefinitionReader::getDefinition('Messaging');
        
        foreach ( $data as $name => $propertys) {
            
            $this->holder[$propertys['id']] = [ 'name' => $name , 'provider' => 'Messaging', 'generic_index' => $propertys['index'] ];
            $this->name_holder["MESSAGING" . "_" . $name] = $propertys;
            
        }   
        
        Log::writeInfo("Loaded " . count($this->holder) . " event handler definitions", $target = 'file');
        
    }
    
    public function getDefinitionByName( $provider, $name ) {
        
        if( isset($this->name_holder[$provider . "_" . $name])) {
            return $this->name_holder[$provider . "_" . $name];
        }
        
    }
    
    public function getDefinitionById( $id ) {
        
        return $this->holder[$id];
        
    }
    
}
