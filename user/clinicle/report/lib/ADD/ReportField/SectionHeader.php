<?php

class ADD_ReportField_SectionHeader extends ADD_ReportField_Markup {
	public function finaliseField($report) {
		$this->defaultSetting('with_next',1);
		$this->defaultSetting('decoration','BU');
		$this->defaultSetting('size','16');
		$this->defaultSetting('top','+3');
	}
}
?>