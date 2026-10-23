<?php

class ADD_Component_HomeButton extends ADD_Component {
		
	function beforeGenerate() {
		$add = ADD::sys();
        $page = $this->_page;
        $ppage = $page->getParent();
		
        $html = $page->html();
		$name = "__home_button";
		
		$params = array("t"=>ADD::db('token')->generate());
		
		if(!$this->getSetting("no_patient")) {
			$params['pid']=$add->getPatientId();
		}

		$attrs = array("class"=>"btn btn-info","onclick"=>"ADD.Util.gotoPage('/',".htmlentities(json_encode($params)).")");
		
		$text = $page->getSetting('HomeButton.text',"Return to home page");
		$icon = $page->getSetting('HomeButton.icon','home');
		$html->addBeforeElement('opt_end',array('tag'=>'a','text'=>"<i class='icon-$icon icon-white'></i>&nbsp;$text",'attrs'=>$attrs,'type'=>ADD_Content_Html::TEXT));
	}	
}
?>
