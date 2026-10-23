<?php
class ADD_Field_GroupPushButtons extends ADD_Field_PushButtons {
	
	function getOnClick($optionValue,$page) {
		return "CLE.Client.togglePushButton(this,'".$this->getSetting('parent_field')."','$optionValue');$('.content-diagnoses_matrix_set').show()";
	}
	function getClassName($optionValue, $page) {
		return "btn ellipsis-container group-btn";
	}
}
?>