<?php


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Page_Api_Dictionary extends ADD_Page_Api {
	
	function isOnlyLabelOutput() {
		return false;
	}
	function apiOutput() {
		$add = ADD::sys();
		$log = ADD::log($this);
		$dictionary = $add->assertParam('dictionary');
		$pageInModuleName = $add->assertParam('pim');
        $groupFilter = $add->getParam('group');

		$db = ADD::db();
		$pageInModule = $add->getPageFromPim($pageInModuleName);
		if(!$pageInModule->getPermission("view")) throw new ADD_NoPermissionToAccessException("Sorry you do not have permission to view ".$pageInModule->getSetting('single')."s");
		$dictionaryCount = $db->fetchSingleValue("select count(*) from dictionary d where d.name=?",$dictionary);
        if($dictionaryCount==0) {
            throw new \Exception("Dictionary '$dictionary' does not exist in system");
        }

		$sql = new \ClinicLE\Common\Statement\SelectStatement();
        $sql->setSelect("di.*");
        $sql->setFrom("dictionary_item di");
        $sql->addJoin("left join dictionary d on d.id=di.dictionary_id");
        $sql->addWhere("d.name = ?");

        $params[]=$dictionary;
		

        if($groupFilter=="") {
            $sql->addWhere("di.dictionary_group_id is null");
        }
        else {
            if($groupFilter!==null) {
                $sql->addJoin("left join dictionary_group dg on di.dictionary_group_id = dg.id");
                $sql->addWhere("dg.name = ?");
                $params[]=$groupFilter;
            }
        }
        $sql->addOrderBy("di.title");
        $sql->addOrderBy("di.weight");
        $dictItems = $db->fetchAll($sql,$params);
		
		$results = array();

		foreach($dictItems as $dictRow) {
            if(!ADD_Util::isEmpty($dictRow['data'])) {
                $rowData = ADD_Util::unserialize($dictRow['data']);
                unset($rowData['data']);
            }
            else {
                $rowData = array();
            }
            $combinedRow = $dictRow + $rowData;
            foreach($combinedRow as $dictField => $dictValue) {
				$result[$dictField]=array("label"=>$dictValue,"value"=>$dictValue);
			}
			$result['id']=$dictRow['id'];

			$results[]=$result;
		}
		
		$data['data']=$results;
		$data['total']=count($dictItems);
		$data['first']=count($dictItems)==0 ? 0 : 1;
		$data['last']=count($dictItems);
		return $data;
	}

}