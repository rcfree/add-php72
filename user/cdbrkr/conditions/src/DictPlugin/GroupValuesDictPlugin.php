<?php
namespace Codebreaker\Conditions\DictPlugin;
use ClinicLE\Install\Dictionary\DictPluginInterface;
use ClinicLE\Common\Util;
use CLE;

class GroupValuesDictPlugin implements DictPluginInterface {
	private $_groupCount = array();	
	function beforeImport() {
	}
    function beforeProcessRow(&$row) {
    	$log = CLE::log($this);
    	$stermList=Util::val('search_terms',$row);
    	$groupValueList=Util::val('group_values',$row);
		
    	if(strlen($stermList)==0 && strlen($groupValueList)==0) throw new Exception("No search terms for row:".print_r($row));
		$codeIds = array();
		
		if(strlen($groupValueList)>0) {
			foreach(explode(";",$groupValueList) as $groupValue) {
				$item = $this->getDictionaryItemWithValue($groupValue);
				if(!$item) {
					$log->warn("Code '$groupValue' in ICD10 group '".$row['title']."' was not found");
					continue;
				}
				$codeIds[]=$item['id'];
			}
		}
		
    	if(strlen($stermList)>0) {
    		$sterms = explode(";",$stermList);

			foreach($sterms as $sterm) {	
	    		$items = $this->getDictionaryItemsMatchingWords($sterm);
				foreach($items as $item) {
					$codeIds[]=$item['id'];
				}
			}
			
			
		}
		$row['code_ids']=$codeIds;
		$log->info("Added code IDs '".implode(",",$row['code_ids'])."' to group '".$row['title']."'");
		$row['data']=Util::serialize($row);
		$area = $this->getAreaWithValue($row['group']);
		if(!$area) {
			$area = $this->insertArea($row['group'],$row['group_title']);
		}
		$row['icd10_area_id']=$area['id'];
    }
    function afterProcessRow(&$row) {
   
    }
    function afterGetFields(&$row) {}
    function afterImport($core) {
        
    }
    
    function processFields(&$fields) {
        
    }
	function getDictionaryItemsMatchingWords($words) {
        $wordList = str_getcsv($words,' ');
        $params = array();
        $wordClauses = array();
        foreach($wordList as $word) {
        	$params[] = $this->_addWildcardsToTerm($word,false);
			$wordClauses[] = "di.title like ?";
		}
		$where = "where di.is_deleted=0 ";
		$where.=" and (".implode(" and ",$wordClauses).")";
		return CLE::db('BASE')->fetchAll("select di.* from icd10_code di $where order by di.title asc", $params);
    }
	function getDictionaryItemWithValue($value) {
        $params = array($value);
        $where = "where di.is_deleted=0 ";
		$where.=" and (di.value=?)";
		return CLE::db('BASE')->fetchFirst("select di.* from icd10_code di $where", $params);
    }
	function getAreaWithValue($areaValue) {
		return CLE::db('BASE')->fetchFirst("select * from icd10_area where value=?",array($areaValue));
	}
	function insertArea($areaValue,$areaTitle) {
		return CLE::db('BASE')->insert("icd10_area",array("value"=>$areaValue,"title"=>$areaTitle));
	}
	function _addWildcardsToTerm($term,$fromStart=false) {
        return $fromStart ? "$term%" : "%$term%";
    }
}