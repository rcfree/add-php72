<?php
/**
 * Description of Load
 *
 * @author rcf8
 */

namespace ClinicLE\Manage\Command;
class ADD_Manage_Migrate implements ADD_ManageInterface
{   
    private $_migConfig = null;
    private $_newConfig = null;
    private $_modName = null;
    
    function getOptsList() {
        return "m::";
    }
    function getDescription() {
        return "Migrate module";
    }
    function manage($options) {
        $add = ADD::sys();
        $add->initCoreModules();
        //$add->initModules();
        $this->_modName = ADD_Util::val('m',$options);
        
        $ml = new ADD_ModuleLoad($add->config("source_module_path"));
        $ml->load($add->getModules(),array($this->_modName));
        
        $module = $add->getModule($this->_modName);
        if (!$module) throw new Exception("Module '{$this->_modName}' was not loaded into the current ADD system");
        
        $srcModPath = $add->getSourceModulePath($this->_modName);
        $workModPath = $add->getWorkModulePath($this->_modName);
        
        $oldVer = ADD_Util::val('o',$options);
        if(!$oldVer) list($oldVer,$oldDate) = ADD_Util::getVersionInfo($workModPath);
        
        list($newVer,$newDate) = $module->version();
        
        
        if ($oldVer==$newVer) throw new Exception("Migration should be done before loading a module. Cannot migrate module '".$this->_modName."' to the same as the current version (v".$newVer.").");
        
        
        $log = ADD::log();
        $log->info("Migrating '".$options['m']."' from v".$oldVer." to v".$newVer);
        
        $newConfig = new ADD_Config();
        $newConfig->loadFile("$srcModPath/page.ini");
        
        $migConfig = new ADD_Config();
        $migFileName = $module->getPath()."/migrate/migrate".str_replace(".","_",$oldVer).".ini";
        if (!$migConfig->loadFile($migFileName)) {
            throw new Exception("Migration file '$migFileName' not found");
        }
        $this->_migConfig = $migConfig;
        $this->_newConfig = $newConfig;
        
        foreach($migConfig->getSections() as $name=>$migSection) {
            $newSection = $newConfig->getSetting($name);
            $migrate = ADD_Util::loadClass("ADD_Migrate_".$migSection->getSetting('class'),$this->_modName, $migSection);
            $migrate->migrate($newConfig);
        }
    }
}
