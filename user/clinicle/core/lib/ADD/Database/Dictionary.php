<?php

class ADD_Database_Dictionary extends ADD_Database {

    function createDictionary($ddata) {
        return $this->insert("dictionary",$ddata,array("date_created"));
    }
    
    function updateDictionary($existing,$new) {
        return $this->update("dictionary",$existing,$new,array("date_updated"));
    }

    function getDictionaryGroupFromItem($dictItem) {
        return $this->selectFirstByField("dictionary_group", "id", $dictItem['dictionary_group_id']);
    }

	private $_dictFields=array();
    function countDictionaryObservations($dictName, $dictItem) {
        if (is_array($dictItem)) {
            $dictItem = $dictItem['value'];
		}
		$params = array($dictItem);
		
		if(!isset($this->_dictFields[$dictName])) {
			$this->_dictFields[$dictName]=$this->fetchAll("select id from observation_field where settings like ?",array("%\"dictionary\":\"$dictName\"%"));
		}
		
		$ofs = $this->_dictFields[$dictName];
		foreach($ofs as $of) {
			$params[]=$of['id'];
		}
		$placeholders = $this->makePlaceholders($ofs);
        return $this->fetchSingleValue("select count(*) from observation o where o.value=? and o.observation_field_id in (".$placeholders.")",$params);
    }
    function _makeDictionaryItemData($dictOrName,$inputData, $dictItemId=null) {
        if (!is_array($dictOrName)) {
            $dict = $this->getDictionaryByName($dictOrName);
            if (!$dict)
                throw new ADD_DatabaseException("Dictionary '$dictOrName' has not been defined");
        }
        else {
            $dict = $dictOrName;
        }
        if($dictItemId) {
        	$item['id']=is_array($dictItemId) ? $dictItemId['id'] : $dictItemId;
		}
			
        $dictGroup = $this->obtainDictionaryGroupFromInput($dict,$inputData);
        $item['dictionary_group_id']=$dictGroup ? $dictGroup['id'] : null;
        
        list ($value, $title,$disabled,$weight) = ADD_Util::vals(array("value","title","is_disabled","weight"),$inputData);
        $item['dictionary_id']=$dict['id'];
        $item['title']=$title;

		$item['is_disabled']=$disabled;

		unset($inputData['title']);
		unset($inputData['value']);
		unset($inputData['group']);
		unset($inputData['new_group']);
		unset($inputData['group_label']);
		unset($inputData['weight']);
		
		if(count($inputData)>0) {
			$item['data']=json_encode($inputData);
		}
        if (ADD_Util::isEmpty($value)) {
            $topValue = $this->fetchSingleValue("select value from dictionary_item di where dictionary_id=? order by CAST(di.value AS UNSIGNED) DESC limit 0,1",$dict['id']);       
            $item['value']=$topValue+1;
        }
        else {
            $item['value']=$value;
        }
        if(!isset($inputData['weight'])) {
            $highestWeight = $this->fetchSingleValue("select di.weight from dictionary_item di left join dictionary d on d.id = di.dictionary_id where d.name=? order by CAST(di.weight AS UNSIGNED) DESC limit 0,1", $dict['name']);
            $newWeight = ($highestWeight ? intval($highestWeight) + 1 : 1);
            $item['weight'] = $newWeight;
        }
        else {
            $item['weight']=$weight;
        }
        return $item;
    }
    function obtainDictionaryGroupFromInput($dict,$inputData) {
        if (ADD_Util::val("group",$inputData) && !ADD_Util::val("new_group",$inputData) ) {
            $dictGroup = $this->getDictionaryGroupByName($inputData['group'],$dict['name']);
            if (!$dictGroup) throw new ADD_DictionaryNotFoundException("Dictionary group '".$inputData['group']."' was not found in dictionary '".$dict['name']."'");
            return $dictGroup;
        }
        
        if (ADD_Util::val("new_group",$inputData) != 1) return null;

        $newLabel = $inputData['group_label'];
        $newName = ADD_Util::labelToName($newLabel);
        $groupExists = $this->getDictionaryGroupByName($newName,$dict['name']);
        if ($groupExists) {
            return $groupExists;
        }
        $newGroup = array(
            "dictionary_id"=>$dict['id'],
            "name"=>$newName,
            "title"=>$newLabel
        );
        $newGroup = $this->insert("dictionary_group",$newGroup);
        return $newGroup;
    }
    
    function storeDictionaryItem($dictOrName, $inputData, $dictItem=null) {
        $di = $this->_makeDictionaryItemData($dictOrName,$inputData,$dictItem);
        
        if (!isset($di['id'])) {
            return $this->insertDictionaryItem($di);
        }
        
        return $this->updateDictionaryItem($di,null);
    }

    function deleteDictionaryGroup($dgid) {
        $dg = $this->getDictionaryGroup($dgid);
        if (!$dg)
            throw new ADD_DictionaryGroupNotFoundException("Dictionary group with ID '" . $dgid . "' does not exist");
        $this->execute("update dictionary_item set dictionary_group_id=null where dictionary_group_id=?",$dgid);
        $this->execute("delete from dictionary_group where id=?",$dgid);
        return $dg;
    }

    function createDictionaryGroup($dictGroup) {
        if (!ADD_Util::val('name', $dictGroup)) {
            $newLabel = $dictGroup['title'];
            $newName = str_replace(array('/', '[', ' ', '!', '@', '�', '$', '%', '^', '&', '*', '(', ')', '_', '+', '=', '\',' | ',', '.', '<', '>', '\\'), "_", $newLabel);
            $dictGroup['name'] = strtolower($newName);
        }
        return $this->insert("dictionary_group", $dictGroup,array("date_created"));
    }

    function updateDictionaryGroup($dictGroup) {
        $updValues = $dictGroup;
        unset($updValues['id']);
        return $this->update("dictionary_group", $dictGroup, $updValues,array("date_updated"));
    }

	/**
	 * 
	 * @param type $dict
	 * @param type $value
	 * @return type
	 */
    function getDictionaryItemWithValue($dict, $value) {
    	if(is_scalar($dict)) {
    		return $this->fetchFirst("select di.* from dictionary_item di left join dictionary d on di.dictionary_id = d.id where d.name=? and di.value=?", array($dict, $value));
    	}
		else {
        	return $this->fetchFirst("select * from dictionary_item di where di.dictionary_id=? and di.value=?", array($dict['id'], $value));
        }
    }

    function getDictionaryItemsMatchingTerm($dictName, $term, $fromStart=false) {
        $term = $this->_addWildcardsToTerm($term,$fromStart);
        return $this->fetchAll("select di.*,IFNULL(di.weight, 1000000) as tempWeight from dictionary_item di left join dictionary d on di.dictionary_id = d.id where d.name=? and di.title like ? and (di.is_deleted=0 or di.is_deleted is null) and (di.is_disabled=0 or di.is_disabled is null) order by tempWeight", array
        ($dictName, $term));
    }

    function getSeparateDictionaryItemsMatchingTerm($dictName, $term, $fromStart=false) {
        $term = $this->_addWildcardsToTerm($term,$fromStart);
        return $this->fetchAll("select di.*,IFNULL(di.weight, 1000000) as tempWeight from `".$dictName."` di  where di.title like ? and (di.is_deleted=0 or di.is_deleted is null) and (di.is_disabled=0 or di.is_disabled is null) order by tempWeight", array
        ($term));
    }
    
    function getDictionaryGroupsMatchingTerm($dictName, $term,$fromStart=false) {
        $term = $this->_addWildcardsToTerm($term,$fromStart);
        return $this->fetchAll("select dg.* from dictionary_group dg left join dictionary d on dg.dictionary_id = d.id where d.name=? and dg.title like ?", array($dictName, $term));
    }
    function _addWildcardsToTerm($term,$fromStart=false) {
        return $fromStart ? "$term%" : "%$term%";
    }
    function insertDictionaryItem($dictItem) {
        return $this->insert("dictionary_item", $dictItem,array("date_created"));
    }

    function getDictionaryGroupByName($groupName,$dictName) {
        return $this->fetchFirst("select dg.* from dictionary_group dg left join dictionary d on dg.dictionary_id = d.id where dg.name=? and d.name=?", array($groupName,$dictName));
    }
    
    function getDictionaryGroup($id) {
        return $this->selectById("dictionary_group",$id);
    }
    function deleteDictionaryItem($id) {
        $this->deleteByField("dictionary_item","id",$id);
    }
   
    function _getDictionaryItems($dictName) {
        $dictItems = $this->fetchAll(
                "select di.* from dictionary_item di left join dictionary d on di.dictionary_id = d.id where d.name=? and di.is_deleted=0 order by di.weight asc", array($dictName)
        );
        return $dictItems;
    }
	
	function getDictionaryItems($dictName) {
        return $this->getFromCacheFunction("_getDictionaryItems", array($dictName));
    }

    function hasDictionary($dictName) {
        $countDict = $this->fetchSingleValue("select count(*) from dictionary where name=?", $dictName);
        return $countDict > 0 ? true : false;
    }

    function getDictionaryGroupsAsOptions($dictName) {
        $dictGroups = $this->fetchAll(
                "select dg.name, dg.title from dictionary_group dg left join dictionary d on dg.dictionary_id = d.id where d.name=?", array($dictName)
        );

        $grps = array();
        foreach ($dictGroups as $grp) {
            $grps[$grp['name']] = $grp['title'];
        }
        return $grps;
    }

    function getDictionaryGroupItemsAsOptions($dgName) {
        $dictItems = $this->fetchAll(
                "select di.value, di.title from dictionary_item di left join dictionary_group on di.dictionary_group_id = dg.id where dg.name=?", array($dgName)
        );
        $diList = array();
        foreach ($dictItems as $dictItem) {
            $diList[$dictItem['value']] = $dictItem['title'];
        }
    }

    function getDictionaryGroups($dictName) {
        return $this->fetchAll(
        	"select dg.* from dictionary_group dg left join dictionary d on dg.dictionary_id = d.id where d.name=?  order by dg.weight", array($dictName)
        );
    }
	function getDictionaryItemsInGroup($dictName,$dictGroupName) {
		return $this->getFromCacheFunction("_getDictionaryItemsInGroup", array($dictName,$dictGroupName));
	}
    function _getDictionaryItemsInGroup($dictName, $dictGroupName) {
        $sql = "select di.* from dictionary_item di left join dictionary d on di.dictionary_id = d.id left join dictionary_group dg on di.dictionary_group_id = dg.id where d.name=? and dg.name=? and di.is_deleted=0 order by di.title ASC";
        $params = array($dictName, $dictGroupName);

        return $this->fetchAll($sql,$params);
    }
    
    function getDictionaryItemsWithNoGroup($dictName) {
        $sql = "select di.* from dictionary_item di left join dictionary d on di.dictionary_id = d.id where d.name=? and di.dictionary_group_id is null and di.is_deleted=0";
        $params = array($dictName);
        return $this->fetchAll($sql,$params);
    }
    
    function getDictionaryItemsInGroups($dictName, $dictGroups) {
        $items = array();
        foreach($dictGroups as $dictGroup) {
            $items = array_merge($items,$this->fetchAll(
                "select di.* from dictionary_item di left join dictionary d on di.dictionary_id = d.id left join dictionary_group dg on di.dictionary_group_id = dg.id where d.name=? and dg.name=?", array($dictName, $dictGroup)
            ));
        }
        return $items;
    }
    
    function getDictionary($id) {
        return $this->selectById("dictionary",$id);
    }
    function getDictionaryByName($name) {
        return $this->selectFirstByField("dictionary", "name", $name);
    }

    function getDictionaryItem($id) {
        return $this->selectById("dictionary_item",$id);
    }

    function getDictionaryItemByTitle($dictName, $title) {
        return $this->fetchFirst("select di.* from dictionary_item di left join dictionary d on di.dictionary_id = d.id where d.name=? and di.title=?", array($dictName, $title));
    }
    
    function updateDictionaryItem($dictItem,$newData=null) {
    	return $this->update("dictionary_item", $dictItem,$newData,array("date_updated"));
    }
	function getDictionaryItemsMatchingWords($dictName, $words) {
        $wordList = str_getcsv($words,' ');
        $params = array($dictName);
        $wordClauses = array();
        foreach($wordList as $word) {
        	$params[] = $this->_addWildcardsToTerm($word,false);
			$wordClauses[] = "di.title like ?";
		}
		$where = "where d.name=? and di.is_deleted=0 ";
		$where.=" and (".implode(" and ",$wordClauses).")";
		return $this->fetchAll("select di.* from dictionary_item di left join dictionary d on di.dictionary_id = d.id $where order by di.title asc", $params);
    }
}
