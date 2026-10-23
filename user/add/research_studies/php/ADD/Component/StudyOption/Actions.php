<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DetailsOption
 *
 * @author rcf8
 */

class ADD_Component_StudyOption_Actions extends ADD_Component_StudyOption {
	
	function beforeAllGenerate() {
		list($sdetailsPage,$sdetails) = $this->getStudyDetails();
		$stat = $sdetails->getObsText("current_status",$sdetailsPage);
		
		if(!in_array(strtolower($stat),ADD_Component_Content_StudyModalEntry::$completedStatuses)) return;
		
		ADD::web()->addWarning("Patient's study status is '".$stat."'. This means that you are not able to add or change actions");
		ADD::sys()->setState('readOnly',true);
	}
	function afterGenerate() {
		list($sdetailsPage,$sdetails) = $this->getStudyDetails();
		
		$html = ADD::web()->getContent();
		$studyNum = $sdetails->getObsValue("study_no",$sdetailsPage);
		if(!$studyNum) $studyNum="none";
		$stat = $sdetails->getObsText("current_status",$sdetailsPage);
		if(!$stat) $stat="none";
		$text = "<b>Current status:</b> ".$stat;
		$text.="&nbsp;|&nbsp;<b>Study no:</b> ".$studyNum;
		$attrs = array("class"=>"btn","href"=>$sdetailsPage->createMenuUri());
		
		$html->addBeforeElement('opt_end',array('tag'=>'a','text'=>$text,'attrs'=>array("class"=>"btn btn-link btn-disable","style"=>"color:black"),'type'=>ADD_Content_Html::TEXT));		
		
		$html->addBeforeElement('opt_end',array('tag'=>'a','text'=>"<i class='icon-pencil'></i>&nbsp;Change status/number..",'attrs'=>$attrs,'type'=>ADD_Content_Html::TEXT));
	}
}
