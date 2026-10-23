<?php

class ADD_Component_SaveButton extends ADD_Component {
		
	function beforeGenerate() {
		$add = ADD::sys();
        $page = $this->_page;

        $html = $page->html();
		$attrs = array("class"=>"btn btn-primary","id"=>"__save_btn","name"=>"__save_btn","onclick"=>"ADD.Notice.startWait()");
		if ($add->hasState('readOnly')) return;
		#$attrs['disabled']='disabled';
		
		$text = $page->getSetting('SaveButton.text',"Save");
		$icon = $page->getSetting('SaveButton.icon','fa fa-floppy-o');
		$html->addAfterElement('opt_start',array('tag'=>'button','text'=>"<i class='$icon'></i>&nbsp;$text",'attrs'=>$attrs,'type'=>ADD_Content_Html::TEXT));
		
		
		$add = ADD::sys();
		//add the save button is not locked and not view only
		if ($add->hasState('locked') || $add->hasState('view')) return;
	}	
}
?>