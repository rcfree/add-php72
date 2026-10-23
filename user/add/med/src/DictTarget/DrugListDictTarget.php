<?php
namespace ADD\Med\DictTarget;
use ClinicLE\Install\Dictionary\DictTarget\DictionaryTableDictTarget;

class DrugListDictTarget extends DictionaryTableDictTarget {

    function shouldUpdateDictionary($name,$fileHash,$forceUpdate) {
        $shouldUpdate = parent::shouldUpdateDictionary($name,$fileHash,$forceUpdate);
        if($shouldUpdate) {
            return true;
        }
        $config = $this->getSupervisor()->getConfig();
        $drugDesc = $config->getSection("drug_descriptions");
        $drugDesc->set("no_update","1");
        $this->log->info("No need to update drug descriptions as file has not changed");
        return false;
    }

}
	
