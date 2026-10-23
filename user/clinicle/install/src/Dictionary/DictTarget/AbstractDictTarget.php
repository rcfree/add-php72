<?php
namespace ClinicLE\Install\Dictionary\DictTarget;
use CLE;
use ClinicLE\Common\Util;
use ClinicLE\Install\Dictionary\Supervisor;
use ClinicLE\Install\Exception\DictItemDefinitionException;

abstract class AbstractDictTarget {
    protected $_config;
    protected $_currentWeight=array();
    protected $includeDataField=true;
    protected $log;
    private $supervisor;
    private $fileHash;

    /**
     * @return mixed
     */
    public function getFileHash()
    {
        return $this->fileHash;
    }

    /**
     * @return \ClinicLE\Install\Dictionary\Supervisor
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }

    function __construct($config, Supervisor $supervisor=null) {
        $this->_config = $config;
        $this->log = CLE::log($this);
        $this->supervisor = $supervisor;
    }

    abstract function createNewItem($dict,$dictItem, $dg);
    abstract function updateCurrentItem($currentItem,$itemData, $latestGroup);
    abstract function findGroupUsingInput($currentItem,$dict);
    abstract function getItemWithValue($dict,$value);
    function hasDictionaryChanged($dict,$fileName,$forceUpdate) {
        $newFileHash = md5_file($fileName);

        $log = $this->log;
        if (!$dict) {
            return false;
        }
        if($forceUpdate) {
            $log->info("DICTIONARY '".$dict['name']."' updated ('force_update' flag set in config)");
            return true;
        }
        if($this->_config->get("no_update")) {
            return false;
        }

        $fileHash = $dict['file_hash'];
        if ($newFileHash != $fileHash) {
            $log->info("DICTIONARY hash for file $fileName has changed from '$fileHash' to '$newFileHash'");
            $this->fileHash = $newFileHash;
            return true;
        }


        $log->info("DICTIONARY file for file $fileName has not changed");
        $this->fileHash = $fileHash;
        return false;
    }

    function incrementCurrentWeight($dict) {
        $dictName = $dict['name'];
        if(!isset($this->_currentWeight[$dictName])) $this->_currentWeight[$dictName]=0;
        $this->_currentWeight[$dictName]++;
        return $this->_currentWeight[$dictName];
    }


    function shouldUpdateDictionary($name,$fileName,$forceUpdate) {
        $dict = CLE::db('dictionary')->getDictionaryByName($name);

        if ($dict && !$this->hasDictionaryChanged($dict, $fileName, $forceUpdate)) {
            return false;
        }
        return true;
    }

    public function isItemSame($currentItem, $itemData, $latestGroup, $log) {
        $newData = array();
        $log = $this->log;

        if (utf8_encode($currentItem['title'])!=utf8_encode($itemData['title'])) {
            $newData["title"]=$itemData['title'];
            $allowChange = Util::val('allow_change',$itemData) || $this->_config->getSetting('allow_change');
            if(Util::isEmpty($allowChange)) {
                throw new DictItemDefinitionException("DICTIONARY item with value '".$currentItem['value']."' and title '".utf8_encode($currentItem["title"])."' has changed to '".utf8_encode($itemData['title'])."' with no 'allow_change' flag");
            }
            //$log->warn("DICTIONARY item with value '".$currentItem['value']." and title '".$currentItem["title"]."'  has changed to '".$itemData['title']."'");
            return array(true,$newData);
        }

        if($this->includeDataField===true) {
            if (array_key_exists('data', $itemData) && Util::val('data', $itemData) != Util::val('data', $currentItem)) {
                $newData['data'] = $itemData['data'];
                return array(true, $newData);
            }
        }
        if($latestGroup===null && Util::val("dictionary_group_id",$newData)!=null) {
            $newData['dictionary_group_id']=null;
            return array(true,$newData);
        }

        if(array_key_exists('dictionary_group_id',$itemData) && $currentItem['dictionary_group_id']!=$latestGroup['id']) {
            $log->info("DICTIONARY item group has changed from ".json_encode($currentItem)." to ".$itemData['group']);
            $newData['dictionary_group_id']=$latestGroup['id'];
            return array(true,$newData);
        }

        if(!array_key_exists('dictionary_group_id',$itemData) && $latestGroup) {
            $log->info("DICTIONARY item group has been set to ".$itemData['group']);
            $newData['dictionary_group_id']=$latestGroup['id'];
            return array(true,$newData);
        }

        if (array_key_exists('disabled',$itemData) && $itemData['disabled']!=$currentItem['is_disabled']) {
            //$log->info("DICTIONARY item disabled status has changed to '".$itemData['disabled']."'");
            $newData['is_disabled']=Util::isEmpty($itemData['disabled']) ? 0 : 1;
            return array(true,$newData);
        }
        if (array_key_exists('deleted',$itemData) && $itemData['deleted']!=$currentItem['is_deleted']) {
            $log->info("DICTIONARY item deleted status has changed to '".$itemData['deleted']."'");
            $newData['is_deleted']=Util::isEmpty($itemData['deleted']) ? 0 : 1;
            return array(true,$newData);
        }
        if (array_key_exists('weight',$itemData) && $itemData['weight']!=$currentItem['weight']) {
            $log->info("DICTIONARY item weight has changed from '".$currentItem['weight']."' to '".$itemData['weight']."'");
            $newData['weight']=$itemData['weight'];
            return array(true,$newData);
        }
        return array(false,$newData);
    }

    function getDictionary($dictName) {
        $log = $this->log;
        if (empty($dictName)) {
            throw new \Exception("No dictionary name was provided using the -d option or as a field in the input file");
        }
        if (isset($dictMap[$dictName])) {
            return $dictMap[$dictName];
        }

        $ddb = CLE::sys()->db('dictionary');
        $adb = CLE::sys()->db('audit');
        $dict = $ddb->getDictionaryByName($dictName);
        if ($dict && ($this->_config->getSetting('retain_filehash') || $this->getFileHash() == $dict['file_hash'])) {
            return $dict;
        }

        if (!$dict) {
            $ddata = array(
                'name' => $dictName,
                'file_hash' => $this->getFileHash()
            );
            $dict = $ddb->createDictionary($ddata);
            $adb->auditDictionary("create", $dict);
            $log->info("DICTIONARY - New dictionary '$dictName' created");
        } else {
            $log->info("DICTIONARY - Existing dictionary '$dictName' found");
        }
        $dictMap[$dictName] = $dict;
        return $dict;
    }
    function updateDictionary($dict) {
        $adb = CLE::sys()->db('audit');
        $ddb = CLE::sys()->db('dictionary');

        if($this->getFileHash()==Util::val('file_hash',$dict)) {
            return;
        }

        $this->log->info("Dictionary file hash changed and updated");
        $updateData = $this->_config->getSetting('retain_filehash') ? array() : array("file_hash" => $this->getFileHash());
        $dict = $ddb->updateDictionary($dict, $updateData);
        $adb->auditDictionary("update", $dict);
    }
    function beforeImport() {

    }
}

