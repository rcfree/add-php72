<?php

class ADD_Field_ModalDictAction extends ADD_Field_Base {
	protected $stored = false;
	function createDrupalElement($page) {}
	
	function getDisplayText($page,$id) {
		$add = ADD::sys();
		$form = $page->getComponent('Form');
		$db = ADD::db('dictionary');
		
		$links = array();
		$action = $this->getSetting('modal_action');
		$modal = $this->getSetting('modal_name');
                $modalFld = $form->getField($modal);
                if(!$modalFld) $page->addWarning("'modal_name' points to field that does not exist");
                $dictName = $page->getSetting('DictionaryEntry.dictionary');
                
		$heading = $page->getSetting('single');
		$beforeJs='beforeDictionary';
		$afterJs=null;
		$hr = ADD::web()->newContent();
		
		if ($page->getPermission($action)) {
			if ($action=="delete") {	
				$di = $db->getDictionaryItem($id);
				$count = $db->countDictionaryObservations($dictName,$di);
				if ($count>0) return "Cannot delete ($count ".ADD_Util::pluralize($count, "result").")";
			}
			if (!$modal) throw new ADD_FieldException("No 'modal_name' setting provided in field ".$this->getName());
			if (!$action) throw new ADD_FieldException("No 'modal_action' setting provided in field ".$this->getName());
			$modalEntryOpt = array(
				"action" => $action,
				"name" => $modal,
				"elementId" => $modal,
				"id" => $id,
				"description" => $heading,
				"lid"=>$page->getModule()->getName(),
				"fid"=>$page->getName(),
				"beforeFunctionName"=>$beforeJs,
				"afterFunctionName"=>$afterJs,
				"ajax"=>array("path"=>ADD_Util::uri("api/dictitem"),"query"=>array("dict_name"=>$dictName))
			);
			$json = json_encode($modalEntryOpt);
			$json = str_replace('"',"'",$json);
			$hr->addTextElement("a",$this->getSetting('action_title',$this->getTitle()),array("href"=>"javascript:;", "onclick"=>'modalEntry('.$json.')'));
			return $hr->toHtml();
		}
		return null;	
	} 	
}
?>