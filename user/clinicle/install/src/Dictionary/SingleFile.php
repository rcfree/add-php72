<?php

namespace ClinicLE\Install\Dictionary;
use ClinicLE\Common\Config\AbstractConfig;
use ADD_Util;
use ADD;
use ClinicLE\Common\Exception\NullValueException;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\DataFile\DelimitedDataFile;
use ClinicLE\DataFile\BatchReader;
use Guzzle\Batch\Batch;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class SingleFile {

    private $plugins = array();
    private $dictGroup = null;
    private $dictName = null;
    private $log = null;
    private $_mod = null;
    private $_config = null;
    private $_name = null;
    private $_modPath = null;
    private $_generatedValue=1;
    private $_target;
    private $supervisor;

    /**
     * @return mixed
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }

    /**
     * @param mixed $supervisor
     */
    public function setSupervisor($supervisor)
    {
        $this->supervisor = $supervisor;
    }

    function __construct($name, AbstractConfig $config, $modPath) {
        $this->_config = $config;
        $this->_name = $name;
        $this->_modPath = $modPath;
        if($this->_config->getSetting('relative_to_home')) {
            $this->_infile = ADD::sys()->getPath()."/".$this->_config->getSetting('file');
        }
        else {
            $this->_infile = $this->_modPath . "/dictionary/" . $this->_config->getSetting('file');
        }
        if (!file_exists($this->_infile)) {
            throw new \ClinicLE\Common\Exception\FileNotFoundException("Input file '{$this->_infile}' does not exist");
        }
    }
    function getTarget() {
        return $this->_target;
    }
    function getConfig() {
        return $this->_config;
    }
    function getFields($fieldList) {
        if (empty($fieldList)) return array('value','title','dictionary','group','group_title','disabled','concept','synonyms','weight','allow_change');

        $fields = explode(",",$fieldList);
        foreach($this->plugins as $plugin) {
            $plugin->processFields($fields);
        }
        return $fields;
    }
    function getName() {
        return $this->_name;
    }
    function _loadPlugins() {
        $registry = ADD::sys()->getService(CoreServices::FACTORY_REGISTRY);
        $pluginList = $this->_config->getSettingList('plugin');
        if (empty($pluginList))
            return;
        $plugins = array();
        foreach ($pluginList as $pluginName) {
            $plugins[] = $registry->newInstance(\ClinicLE\Install\InstallFactories::DICT_PLUGIN_FACTORY,$pluginName);
        }
        $this->plugins = $plugins;
    }

    function updateSingle($refreshDict=false) {
        $add = ADD::sys();
        $log = ADD::log($this);

        $registry = ADD::sys()->getService(CoreServices::FACTORY_REGISTRY);
        $config = $this->getConfig();
        $this->_target = $registry->newInstance(\ClinicLE\Install\InstallFactories::DICT_TARGET_FACTORY,$config->getSetting('target','DictionaryTable'),array($config, $this->getSupervisor()));
        if($this->_config->getSetting('no_update')) {
            $log->info("DICTIONARY '{$this->_name}' NOT updated ('no_update' flag set in config)");
            return;
        }

        if (!$refreshDict && !$this->_target->shouldUpdateDictionary($this->_name,$this->_infile,$this->_config->getSetting('force_update'))) {
            return;
        }

        $this->_loadPlugins();

        $fields = null;
        if ($this->_config->getSetting('fields')) {
            $fields = $this->getFields($this->_config->getSetting('fields'));
        }

        foreach ($this->plugins as $plugin) {
            $plugin->afterGetFields($fields);
        }
        $dataFile = new DelimitedDataFile($this->_infile,$fields);
        //$dataFile->setIgnoreEmptyHeadings(true);
        if (!$fields && $this->_config->getSetting('heading')) {
            $dataFile->setHeaderRow(true);
        } else {
            if (empty($fields))
                $dataFile->setHeadings(array('value', 'title', 'dictionary', 'group', 'group_title', 'disabled',
                    'concept', 'synonyms'));
        }
        $batchReader = new BatchReader($dataFile);
        $batchReader->setBatchSize($add->getConfig()->getSetting("dict_batch_size",10000));

        if ($this->_config->getSetting('limit')) {
            $dataFile->setLimit($this->_config->getSetting('limit'));
        }
        if ($this->_config->getSetting('sep')) {
            $dataFile->setFieldSep($this->_config->getSetting('sep'));
        }

        foreach ($this->plugins as $plugin) {
            $plugin->beforeImport($dataFile, $this);
        }

        $dataFile->open();
        $dictItems = array();
        $lineCount = 0;

        $batchCount = $batchReader->getBatchCount();
        $updatedDicts = array();
        $this->getTarget()->beforeImport();

        while ($batch = $batchReader->nextBatch()) {
            foreach ($batch as $row) {
                if ($this->_config->getSetting('limit')) {
                    if ($lineCount >= $this->_config->getSetting('limit'))
                        break;
                }

                $processedRow = $this->_processRow($row);
                $this->appendDictItems($processedRow, $dictItems);
                $lineCount++;
            }

            if (!isset($options['n'])) {
                foreach ($dictItems as $dictName => $dictItems) {
                    $dict = $this->getTarget()->getDictionary($dictName);
                    if(!$dict) {
                        throw new \Exception("No dictionary '$dictName'");
                    }

                    $this->createItems($dict, $dictItems);
                    if(!isset($updatedDicts[$dictName])) {
                        $updatedDicts[$dictName] = $dict;
                    }
                }
            }
            $dictItems = array();

            $log->info("Line {$lineCount} (batch ".$batchReader->getLastBatchNo()."/".$batchCount.")");
        }

        if (!isset($options['n'])) {
            foreach ($dictItems as $dictName => $dictItems) {
                $dict = $this->getTarget()->getDictionary($dictName);
                $this->createItems($dict, $dictItems, $options['t']);
                if(!isset($updatedDicts[$dictName])) {
                    $updatedDicts[$dictName] = $dict;
                }
            }
        }

        $dataFile->close();


        foreach($updatedDicts as $dictName => $dict) {
            $this->getTarget()->updateDictionary($dict);
        }

        foreach ($this->plugins as $plugin) {
            if(method_exists($plugin,"afterImport")) {
                $plugin->afterImport($this);
            }
        }
    }
    function appendDictItems($processedRow, &$dictItems) {
        if (!$processedRow) {
            return;
        }
        if(!isset($processedRow["rows"])) {
            $rows = array($processedRow);
        }
        else {
            $rows = $processedRow["rows"];
        }

        $dictName = $processedRow["dictionary"];

        foreach($rows as $processedItem) {
            if($this->_config->getSetting('generate_value')) {
                $processedItem['value']=$this->_generatedValue;
                $this->_generatedValue++;
            }
            if (!isset($dictItems[$dictName])) {
                $dictItems[$dictName] = array();
            }
            $dictItems[$dictName][] = $processedItem;

        }

    }
    function createItems($dict, $currentItems, $ignoreTitles=false, $noWeightIncrement=false) {
        $target = $this->_target;
        $log = ADD::log($this);
        foreach ($currentItems as $currentItem) {
            if(!ADD_Util::val('weight',$currentItem) && !$this->_config->getSetting('ignore_weight')) {
                $currentItem['weight']=$target->incrementCurrentWeight($dict);
            }
            $currentGroup = null;
            $groupTitle = ADD_Util::val('group_title',$currentItem);
            $group = ADD_Util::val('group',$currentItem);
            if(ADD_Util::isEmpty($group) && !ADD_Util::isEmpty($groupTitle)) {
                $currentItem['group']=ADD_Util::labelToName($currentItem['group_title']);
            }
            if (!ADD_Util::isEmpty($group)) {
                $currentGroup = $target->findGroupUsingInput($currentItem, $dict);
            }
            $value = ADD_Util::val('value', $currentItem);
            if (ADD_Util::isEmpty($value))
                throw new NullValueException("DICTIONARY item does not have a 'value' (" . print_r($currentItem, 1));
            $existingItem = $target->getItemWithValue($dict, $currentItem['value']);
            if ($existingItem) {
                $target->updateCurrentItem($existingItem, $currentItem, $currentGroup);
            } else {
                $target->createNewItem($dict, $currentItem, $currentGroup);
            }
        }
    }
    function _processRow($row) {

        foreach ($this->plugins as $plugin) {
            $plugin->beforeProcessRow($row);
        }

        if (!$row)
            return null;

        if(isset($row[0])) {
            $row = array("rows"=>$row);
        }
        if (!ADD_Util::val("dictionary",$row)) {
            $row['dictionary'] = $this->_name;
        }

        $dictName = isset($row['dictionary']) ? $row['dictionary'] : null;

        foreach ($this->plugins as $plugin) {
            $plugin->afterProcessRow($row);
        }
        return $row;
    }




}
