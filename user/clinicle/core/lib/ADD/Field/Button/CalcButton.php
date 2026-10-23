<?php

class ADD_Field_Button_CalcButton extends ADD_Field_Button {
	function afterAddMarkup($page) {
		
		$fieldName = $this->getName();
		$form = $page->getComponent('Form');
		if (ADD::sys()->hasState('locked')) return;
		if ($this->getSetting('calculate')) {
			
			$js = "";
			$lookForList = $this->getSetting('calculate');
			
			$lookFors = explode(";",$lookForList);
			$funcJs = "";
			foreach($lookFors as $lookFor) {
				$calcField = $form->getField($lookFor);
				if (!$calcField) throw new ADD_FieldException("Field '$lookFor' specified in 'calculate' setting was not found");
				$calcJs = $calcField->getSetting('expression');
				if(!$calcJs) throw new Exception("Expression field '".$calcField->getName()."' requires Javascript 'expression' setting");
				$calcFixed = $calcField->getSetting('fixed');
				$funcJs.="var tempVal = $calcJs; if (isNaN(tempVal)) { jQuery('[name=\"$lookFor\"]').val('') } else { ";
				if ($calcFixed) $funcJs.="tempVal = tempVal.toFixed($calcFixed);";
				$funcJs.="jQuery('[name=\"$lookFor\"]').val(tempVal); }\n";
			}
			$js.= "function ".$this->getName()."_calc() { $funcJs }";
			$page->addJavascript($js,"head");
			if ($this->getSetting('recalc_onsubmit')) {
				$page->addJavascript('jQuery("#content-form").submit(function() {'. $this->getName().'_calc(); });');
			}
			
		}
	}

	protected function getLinkTitle($page) {
		return $this->getTitle();
	}
	
	protected function getOnClick($page) {
		if (ADD::sys()->hasState('locked')) return;
		return $this->getName()."_calc(); return false;";
	}
	
	protected function getLinkPath($page) {
		if (ADD::sys()->hasState('locked')) return;
		return "javascript:;";
	}
	
	protected function getLinkAttrs($page) {
		return array(
			"class"=>"btn btn-info",
			"type"=>"button"
		);
	}
	
}
?>