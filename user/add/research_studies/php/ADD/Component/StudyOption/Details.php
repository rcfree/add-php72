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

class ADD_Component_StudyOption_Details extends ADD_Component_StudyOption {
	function beforeGenerate() {
		$actionsPage = $this->_page->getSiblingPage('actions');
	
		$attrs = array("class"=>"btn","href"=>$actionsPage->createMenuUri());
		
		ADD::web()->getContent()->addBeforeElement('opt_end',array('tag'=>'a','text'=>"Go back to study actions",'attrs'=>$attrs,'type'=>ADD_Content_Html::TEXT));
	}
}
