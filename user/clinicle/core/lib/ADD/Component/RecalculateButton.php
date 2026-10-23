<?php

class ADD_Component_RecalculateButton extends ADD_Component {
		
	function beforeGenerate() {
		$add = ADD::sys();
        $page = $this->_page;

        if ($add->hasState('locked') || $add->hasState('view') || $add->hasState('readOnly')) return;
		
		$form = $page->getComponent('Form');

		$uniqueExps = array();

		foreach($form->getFields() as $field) {
			$fexps = array();
			if ($field->getType() == 'Expression') {
				$exp = $field->getSetting('expression');
				$ajax = $field->getSetting('ajax-func');

				if ($ajax) {
					$apage = $add->getPageInModule('ajax','ajax');
					$acomp = $apage->getComponent('AjaxExecute');
                                        $funcInputs = $acomp->createAjaxInputs($page,$ajax);
				
					if (!is_array($funcInputs)) {
                                            $page->addWarning("Function input '$ajax' has no inputs");
                                            continue;
                                        }
					
					foreach($funcInputs as $input) {
						$fexps[]=$input;
					}
					
				}
				else {
					$fexps = ADD_Field_Expression::extractFieldsFromExpression($exp,$form);
				}
			}
			foreach($fexps as $fexp) $uniqueExps[$fexp]=true;
		}
		$exps = array_keys($uniqueExps);
		if (count($exps)==0) return;
		$html = $page->html();
		$json = str_replace('"',"'",json_encode($exps, JSON_HEX_APOS | JSON_HEX_QUOT));
		$attrs = array("class"=>"btn","type"=>"button","id"=>"__recalc_btn","name"=>"__recalc_btn","onclick"=>"recalculate($json);");
		
		$text = $page->getSetting('RecalcButton.text');
		!$text and $text="Recalculate";
		
		$html->addAfterElement('opt_start',array('tag'=>'button','text'=>"<i class='icon-refresh'></i>&nbsp;$text",'attrs'=>$attrs,'type'=>ADD_Content_Html::TEXT));

	}	
}
?>