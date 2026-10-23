<?php
class ADD_Component_DeleteSectionButton extends ADD_Component {
		
	function beforeGenerate() {
		$add = ADD::sys();
        $page = $this->_page;
        $ppage = $page->getParent();
		if(!$ppage->getPermission('delete')) return;
		if ($add->hasState('readOnly')) return;
		$per = $ppage->getEncounterRecord();
		$per->retrieve();
		
		$basePath = $ppage->createMenuPath();
		$eid =  $per -> getId();
		$deleteUri =  ADD_Util::uri($basePath,array("pid"=> $add->getPatientId(),"eid"=> $eid,"t"=>ADD_Util::token(),"action"=>"delete"));
		$deleteName = "__".$ppage->getName()."_delete_".$eid;
		$opts = array("heading"=>"Are you sure you want to delete this ".$ppage->getSetting('title')."?","text"=>"This record will be marked as deleted in ADD","uri"=>$deleteUri);
		$page->addJavascript("ADD.assign('ConfirmAction','".$deleteName."',".json_encode($opts, JSON_HEX_APOS | JSON_HEX_QUOT).")");
		
        
        $html = $page->html();
		$attrs = array("class"=>"btn btn-warning","id"=>$deleteName,"name"=>$deleteName,"type"=>"button");
		
		$text = $this->getSetting('text',"Delete section");
		$icon = $this->getSetting('icon','remove');
		$html->addBeforeElement('opt_end',array('tag'=>'button','text'=>"<i class='icon-$icon icon-white'></i>&nbsp;$text",'attrs'=>$attrs,'type'=>ADD_Content_Html::TEXT));
		
		
		$add = ADD::sys();
		//add the save button is not locked and not view only
		if ($add->hasState('locked') || $add->hasState('view')) return;
	}	
}
?>