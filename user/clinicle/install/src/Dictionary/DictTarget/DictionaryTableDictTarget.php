<?php
namespace ClinicLE\Install\Dictionary\DictTarget;
use CLE;
use ClinicLE\Common\Util;

class DictionaryTableDictTarget extends AbstractDictTarget {
		
	public function createNewItem($dict,$dictItem, $dg) {
		$ddb = CLE::sys()->db('dictionary');
		$adb = CLE::sys()->db('audit');
		
		$didata = array(
			'dictionary_id'=>$dict['id'],
			'value'=>$dictItem['value'],
			'title'=>$dictItem['title'],
		);
		if (isset($dictItem['concept'])) $didata['concept']=$dictItem['concept'];
		if (isset($dictItem['data'])) $didata['data']=$dictItem['data'];
		if (array_key_exists('weight',$dictItem)) $didata['weight']=$dictItem['weight'];
		$didata['is_disabled']=Util::val('disabled',$dictItem,0);
		$didata['is_deleted']=Util::val('deleted',$dictItem,0);
		$didata['dictionary_group_id']=$dg ? $dg['id'] : null;
		//CLE::log()->info("DICTIONARY ITEM created ".json_encode($didata));
		$di = $ddb->insertDictionaryItem($didata);
		$adb->auditDictItem("create",$di);
	}

    public function extractSubset($data, $keys,$default = null) {
        $subset = array();
        foreach($keys as $key) {
            if(!isset($data[$key])) {
                $subset[$key]=$default;
                continue;
            }
            $subset[$key]=$default;
        }
        return $subset;
    }
	public function updateCurrentItem($currentItem,$itemData, $latestGroup) {
		$ddb = CLE::sys()->db('dictionary');
		
		$log = CLE::log($this);
		
        list($changed,$newData) = $this->isItemSame($currentItem, $itemData, $latestGroup, $log);
		if(!$changed) {
            return;
        }

        $oldDi = $currentItem;
        $adb = CLE::sys()->db('audit');
        $currentItem = $ddb->updateDictionaryItem($currentItem,$newData);
        $adb->auditDictItem("update",$currentItem,$oldDi);
	}
	
	public function findGroupUsingInput($currentItem,$dict) {
		$log = CLE::log($this);
		$ddb = CLE::db('dictionary');
		$adb = CLE::db('audit');
		$existingGroup = $ddb->getDictionaryGroupByName($currentItem['group'],$dict['name']);	
		if (!$existingGroup) {
			$log->info("DICTIONARY new dictionary group '".$currentItem['group']."' created");
			$groupData = array(
				"name"=>$currentItem['group'],
				"title"=>Util::val('group_title',$currentItem),
				"weight"=>Util::val('group_weight',$currentItem),
				"dictionary_id"=>$dict['id']
			);
			$existingGroup = $ddb->createDictionaryGroup($groupData);
			$adb->auditDictGroup("create",$existingGroup);
		}
		else {
			if(isset($currentItem['group_title'])) {
				$changed = false;
				if (Util::val('weight',$existingGroup)!=Util::val('group_weight',$currentItem)) {
					$existingGroup['weight']=$currentItem['group_weight'];
					$changed=true;
				}
				if (Util::val('title',$existingGroup)!=$currentItem['group_title']) {
					$existingGroup['title']=$currentItem['group_title'];
					$changed=true;
				}
				
				if($changed){
					$existingGroup = $ddb->updateDictionaryGroup($existingGroup);
					$adb->auditDictGroup("update",$existingGroup);
					$log->info("DICTIONARY group '".$currentItem['group']."' title updated");
				}
			}
		}
		return $existingGroup;
	}
	function getItemWithValue($dict,$value) {
		return CLE::db('dictionary')->getDictionaryItemWithValue($dict, $value);
	}
	
	
}
	