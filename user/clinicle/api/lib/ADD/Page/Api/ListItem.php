<?php

class ADD_Page_Api_ListItem extends ADD_Page_Api {
		
        function apiOutput() {
            $add = ADD::sys();
            
            list($token,$id,$values) = $add->getParams(array('token','id','values'));
			list($mod,$pageName,$miFieldName) = $add->getParams(array('mod','page','field'));
			$page = $add->getPageInModule($mod,$pageName);
			$form = $page->getComponent('Form');
			$form->setup();
			$data = array();
			
			
			if ($values) {
				$row = ADD::db()->fetchFirst("select id,data from temp_data where token=? and id=?",array($token,$id));
				$params = ADD_Util::unserialize($row['data']);
				foreach($params as $key=>$value) {
					$data[]=array("name"=>$key,"value"=>$value);
				}
				$data[]=array("name"=>$pageName."_id","value"=>$row['id']);
				return $data;
			}
			
			$params = $add->getParams();
			
			foreach($form->getTopLevelFields() as $tlFieldName) {
				$tlField = $form->getField($tlFieldName);
				$data[]=$this->_getCellData($id,$tlField,$params,$page);
			}
			$row = array("token"=>$token,"data"=>ADD_Util::serialize($params));
			$id = $add->getParam($pageName."_id");
			if($id) {
				ADD::db()->update("temp_data",array("id"=>$id),$row);
			}
			else {
				$row = ADD::db()->insert("temp_data",$row);
				$id = $row['id'];
			}
			$data[]=$id;
			return $data;
		}
		
		function _getCellData($id,$field,$values,$page) {
			$add = ADD::sys();
			$form = $page->getComponent('Form');
			$field->setValue(null);
			$field->populateValue($values,$page);
                
			$childFields = $form->getChildFields($field->getName());
			$data = null;
			if (count($childFields)>0) {

				$subResults = array();
				$joinChar = $field->getSetting('join_char') ? $field->getSetting('join_char') : " ";

				foreach($childFields as $childField) {
					$childField->setValue(null);
					$childField->populateValue($values,$page);
					$subResults[]=$childField->getDisplayText($page,$id);
				}
				$data = implode($joinChar,$subResults);
			}
			else {
				$data = $field->getDisplayText($form,$id);
			}


			return $data;
	}
}
