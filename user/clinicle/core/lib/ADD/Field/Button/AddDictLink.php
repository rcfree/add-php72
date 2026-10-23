<?php

class ADD_Field_Button_AddDictLink extends ADD_Field_Button {
	protected function getOnClick($page) {
		$modal = $this->getSetting('modal_name');
		$form = $page->getComponent('Form');
                if (!$modal) throw new ADD_FieldException("No 'modal_name' setting provided in field ".$this->getName());
                $modalFld = $form->getField($modal);
                if(!$modalFld) $page->addWarning("'modal_name' points to field that does not exist");
                $dictName = $page->getSetting('DictionaryEntry.dictionary');
                
		$single = $page->getSetting('single');
		$modalEntryOpt = array(
			"action" => "create",
			"name" => $modal,
			"elementId" => $modal,
			"description" => $single,
			"lid"=>$page->getModule()->getName(),
			"fid"=>$page->getName(),
			"ajax"=>array("path"=>"/api/dictitem","query"=>array("dict_name"=>$dictName))
		);
		$json = json_encode($modalEntryOpt);
		$json = str_replace('"',"'",$json);
		return "modalEntry($json);";
	}
	protected function getLinkAttrs($page) {
		return array(
			"class"=>"btn btn-primary",
			"type"=>"button"
		);
	}
	protected function getLinkTitle($page) {
		return "<i class='icon-ok icon-white'></i>&nbsp;".$this->getTitle();
	}
}
?>