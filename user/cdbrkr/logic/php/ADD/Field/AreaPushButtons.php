<?php

class ADD_Field_AreaPushButtons extends ADD_Field_PushButtons {
	function getOnClick($optionValue,$page) {
		$onclick = parent::getOnClick($optionValue,$page);	
		$onclick .= "$('.content-diagnoses_matrix_set').hide();$('#diagnoses_area_container button.btn-primary').removeClass('btn-primary');";
		return $onclick;
	}
	function getClassName($optionValue, $page) {
		$className = parent::getClassName($optionValue,$page);
		return $className." area-btn";
	}
}
?>