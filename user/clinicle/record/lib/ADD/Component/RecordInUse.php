<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RecordInUse
 *
 * @author rcf8
 */
 

class ADD_Component_RecordInUse extends ADD_Component {
	private $_whoUsing;
	private $_userLogin;
	public function setUserLogin(ADD_UserLogin $userLogin) {
		$this->_userLogin=$userLogin;
	}
	
	public function beforePopulate() {
		$add = ADD::sys();
		
		$udb = ADD::db('record');
		if(!$this->_page) return;
		$whoUsing = $this->_userLogin->whoIsUsingRecordPage($this->_page);
		if($whoUsing===null) {
			$this->_userLogin->lockRecordPage($this->_page);
			return;
		}
		$add->setState(ADD_System::INUSE,true);
		
		$web = ADD::web();
		$web->addWarning("<i class='icon-lock'></i>&nbsp;<b>This record is currently being edited by '".$whoUsing['username']."'.</b><br/>In order to prevent discrepancies you will not be able to make changes until the other user has finished.");
		 
		$this->_whoUsing = $whoUsing;
	}
	
	public function afterGenerate() {
		return;	
		$add = ADD::sys();
		
		
		$am = $add->getAccessManager();
		
		//if(!$am->isAdmin()) return;
		if (!$add->hasState(ADD_System::INUSE)) {
			$jsAttrs = array("requested_by"=>$am->getUser()->username,"check_interval"=>$add->config('in_use_refresh_time'));
			ADD::web()->addJSAssign("RequestRecordAccess",null,"#content-form", $jsAttrs);
			return;
		}
		$attrs = array("class"=>"btn","type"=>"button","onclick"=>"ADD.User.requestUnlock()");
		$text = "Request Access from '" .$this->_whoUsing['username']."'";
		$html = ADD::web()->getContent();
		
		$html->addBeforeElement('opt_end',array('tag'=>'button','text'=>"<i class='icon-lock'></i>&nbsp;$text",'attrs'=>$attrs,'type'=>ADD_Content_Html::TEXT));
	}
}
