<?php

class ADD_Field_CheckboxesToList extends ADD_Field_List_Checkboxes {
	public function afterAddMarkup($page) {
		$l = ADD::log($this);
		$this->addJQuery($page,"change","function() {
			 var checks = jQuery('.content-".$this->getName()."').find('input:checked');
			 var lists = ['activity1','activity2','activity3','activity4','activity5'];
			 for(var i=0;i<lists.length;i++) {
			 	currList = lists[i];
			 	currValue = get(currList);
			 	field(currList).empty();
			 	field(currList).append('<option value=\"\">-select option-</option>');
				 for(var c=0;c<checks.length;c++) {
				 	var checked = jQuery(checks[c]);
				 	var text = checked.parent('label').text();
				 	var val = checked.val();
				 	
				 	if(val==currValue) {
				 	    optionHtml = '<option value=\"' + val + '\" selected=\"selected\">' + text + '</option>';
				 	}
				 	else {
				 	    optionHtml = '<option value=\"' + val + '\">' + text + '</option>';
				 	}
				 	field(currList).append(optionHtml);
				 }
			}
		}");

	}
	function afterPopulateFields($page) {
		$form = $page->getComponent('Form');
		$values = $this->getValue();
		$options = $this->getOptions();
               
		if (!$values) return;
                
                $foptions = array();

                foreach($values as $value => $data) {
                    $label = $options->findLabelWithValue($value);
                    if (!$options->findLabelWithValue($value)) {
                        ADD::web()->addWarning("Option with value '$value' does not exist. Unable to set up lists");
                        return;
                    }
                    $foptions[$value] = $label;
                }
                $copyToFields = array('activity1','activity2','activity3','activity4','activity5');
                $optionPartialCopy = new ADD_Field_Option_StandardImpl();
                $optionPartialCopy->setOptionList($foptions);
                foreach($copyToFields as $copyToField) {
                    $form->getField($copyToField)->setOptions($optionPartialCopy);
                }
	}
	function getJQuery($page,$event,$code) {
		$add = ADD::sys();
		
		$name = $this->getName();
		return "jQuery('[name^=\"{$name}[\"]').{$event}({$code})";
	}
        function updateExistingData($dbField) {
            return true;
        }
}
class ADD_Field_Option_StandardImpl extends ADD_Field_Option_Standard { }
?>