<?php
class ADD_Component_Verification extends ADD_Component {
	const NO_VERIFY_SETTING="no-verification";
	const VERIFY="__verify";
	const VERIFYUSER="__verify_user";
	const VERIFYPASS="__verify_pass";
	const REASON="__reason";
    private $verAuds = array();
    function setup() {
        $this->getPage()->getEvents()->addListener(\ClinicLE\System\Event\ContentEvents::OPTION_BUTTONS,array($this,
            "onOptionButtons"));
    }
	function afterPopulate() {
		$page = $this->_page;
		if($page->getSetting(self::NO_VERIFY_SETTING)) return;
		
	    $add = ADD::sys();
	    
	    $er = $page->getEncounterRecord();
		#if($add->hasState('readOnly')) $page->setSetting(self::NO_VERIFY_SETTING,1);
	    if ($er->exists() && $er->getEncData('is_verified')==1) $add->setState('readOnly',true);
        $this->verAuds = $this->_getVerifications($er->getEncounter());
    }
	
	private function _getVerifications($enc) {
		return ADD::db('audit')->getAuditsForEncounter($enc);
	}
	
	function onOptionButtons(\ClinicLE\Component\Event\PageContentEvent $event) {
	    $page = $this->_page;
		if($page->getSetting(self::NO_VERIFY_SETTING)) return;

		$html = $event->html();
		$er = $page->getEncounterRecord();
        if (!$er->exists()) {
        	if(!$this->getSetting('ignore-no-record')) return;
			$er->store();
		}
        $er->retrieve();
        
		$attrs = array("class"=>"btn btn-primary","type"=>"button","id"=>"__verify_btn","name"=>"__verify_btn","onclick"=>"confirmVerified()");
		
		$verCount = count($this->verAuds);
			
		if ($page->getPermission('verify')) {
			if (!$er->exists() || $er->getEncData('is_verified')!=1) {
				$attrs['class']="btn btn-success";
                $html->addTextElement("button","<i class='icon-ok icon-white'></i>&nbsp;"
                    .$this->getSetting('verify_text','Verify'),$attrs);
			}
		}
		if ($page->getPermission('unverify')) {
			if ($er->exists() && $er->getEncData('is_verified')==1) {
				$attrs['class']="btn btn-danger";
				$attrs['onclick']="markUnverified()";

                $html->addTextElement("button","<i class='icon-edit icon-white'></i>&nbsp;"
                .$this->getSetting('unverify_text','Review'),$attrs);
            }
		}
		if ($er->getEncData('is_verified')==1 && $verCount==0) { $page->addWarning("A verification audit record missing"); return; }
		
		if ($er->exists() && $verCount>0) {
			$verHtml = new ADD_Content_Html();
			$verHtml->startElement("div",array("class"=>"alert alert-info","style"=>"margin-left:0.5em"));
			$verText = ADD_Util::pluralize($verCount,"verification");
			$verHtml->addHtml("<b>$verCount</b> $verText");
			$verHtml->startElement("ul");
			
			foreach($this->verAuds as $verAud) {
				$audTs = DateTime::createFromFormat("Y-m-d H:i:s",$verAud['timestamp'],new DateTimeZone(date_default_timezone_get()));
                $info = ADD_Util::unserialize($verAud['action_info']);
				$verHtml->addTextElement('li',"<b>".$verAud['user_id']."</b> (".($audTs ? $audTs->format('d/m/Y H:i') : "unknown").")".($this->getSetting('no_reason') ? "": "- <i>". ADD_Util::val('reason',$info,"Initial verification")."</i>"));
			}
			$verHtml->endElement("ul");
			$verHtml->endElement("div");

            $this->getPage()->getEvents()->addListener(\ClinicLE\System\Event\ContentEvents::AFTER_OPTION_BUTTONS,
                function($event) use($html, $verHtml) { $html->addHtml($verHtml); });

		}
	}	
	
	function expectedPermissions(&$expPerms) {
		if($this->_page->getSetting(self::NO_VERIFY_SETTING)) return;
		
		$expPerms[]="verify";
		$expPerms[]="unverify";
	}
	
	function afterGenerate() {
		$page = $this->_page;
		 
		if($page->getSetting(self::NO_VERIFY_SETTING)) return;
       
		$er = $page->getEncounterRecord();

        if (!$er->exists()) return;

        $er->retrieve();

		$html = $page->html();
		$page->html()->addElement('input',array('type'=>'hidden','value'=>'','name'=>ADD_Component_Verification::VERIFY));
		
		$html->startElement("div",array("id"=>"modal-confirm-verify","class"=>"modal hide fade","style"=>"width:500px"));
		$html->startElement("div", array("class"=>"modal-body"));
		
		$html->addTextElement("label","To mark this data as verified please enter your user name below:",array("id"=>"modal-messages-confirm-verify"));
		
		$html->addElement("input",array("type"=>"text","class"=>"span3","name"=>self::VERIFYUSER));
		
		$html->addTextElement("label","If you are NOT the logged in user please enter your password:",array("id"=>"modal-messages-confirm-verify"));
		$html->addElement("input",array("type"=>"password","class"=>"span3","name"=>self::VERIFYPASS));

        $verAuds = $this->verAuds;
        $verCount = count($verAuds);
        if ($verCount>0 && !$this->getSetting('no_reason')) {
            $html->addTextElement("label","Since this record has already been verified $verCount ".ADD_Util::pluralize($verCount,"time").", please enter a reason for this change:",array("id"=>"modal-messages-reason-verify"));
            $html->addElement("input",array("name"=>ADD_Component_Verification::REASON, "type"=>"text","class"=>"span4"));
        }
		//$html->endElement("form");
		$html->endElement("div");
		
		$html->startElement("div",array("class"=>"modal-footer"));
		$html->addTextElement("a","Cancel",array("href"=>"javascript:;","onclick"=>"$('#modal-confirm-verify').modal('hide');", "class"=>"btn ignoredirty","id"=>"close-confirm"));
		$html->addTextElement("a","Ok",array("href"=>"javascript:markVerified();","class"=>"btn btn-primary ignoredirty","id"=>"save-confirm"));
		$html->endElement("div");
		$html->endElement("div");
	}
	function submitAccess($values) {
		$page = $this->_page;
		if($page->getSetting(self::NO_VERIFY_SETTING)) return;
        
		$form = $page->getComponent('Form');
		$verify = ADD_Util::val(ADD_Component_Verification::VERIFY,$values);
		$reason = ADD_Util::val(ADD_Component_Verification::REASON,$values);
		if (!$verify) return;
        $er = $page->getEncounterRecord(ADD_Util::val(ADD_Component::PARENT_ID,$values));
        if (!$er->exists()) return;
        $er->retrieve();
		
		$am = ADD::sys()->getAccessManager();
		$user = $am->getUser();		
		
	 	if ($verify=="<unverify>") {
			if ($er->getEncData('is_verified')) {
				$er->setEncData('is_verified','0');
				$this->_auditVerify("unverify",$er,null,$user->username);
			}
		}
		else {
			$vuser = ADD_Util::val(self::VERIFYUSER,$values);
			$vpass = ADD_Util::val(self::VERIFYPASS,$values);
			
			if(!$this->_verifyRecord($vuser,$vpass)) return;
			
			if (!$er->getEncData('is_verified')) {
				$er->setEncData('is_verified',1);
				$this->_auditVerify("verify",$er,$reason,$vuser);
			}
		}
		$er->store();
	}
	
	function _verifyRecord($vuser,$vpass) {
		$am = ADD::sys()->getAccessManager();
		$user = $am->getUser();		
		$context = $am->getContext();
		$page = $this->_page;
		
		if($vuser==$user->username) {
			ADD::web()->addMessage("Record was successfully verified");
			return true;
		}
		else {
			$isAuth = $context->ion_auth->login($vuser,$vpass,false,true);
			if(!$isAuth) {
				ADD::sys()->setState('cancel',true);
				ADD::web()->addError("Sorry the record could not be verified by '$vuser': ".$context->ion_auth->errors());
				return false;
			}
		}
		//does user have verify permission?
		$context->ion_auth->limit(1);
		$context->ion_auth->where('users.username', $vuser);
		$query = $context->ion_auth->users();
		$vuserObj = $query->row();
		$groups = $context->ion_auth->get_users_groups($vuserObj->id)->result();
		
		$verifyPerm=false;
		foreach($groups as $group) {
		    if($group->name=="verify_".$page->getModule()->getAlias()."_".$page->getName()) {
				$verifyPerm=true;
				break;
			}
		}
		if(!$verifyPerm) {
			ADD::sys()->setState('cancel',true);
			ADD::web()->addError("Sorry the record could not be verified by '$vuser' because they do not have the correct access permission");
			return false;
		}
		return true;
	}

	function _auditVerify($action, $er, $reason=null,$username) {
		$add = ADD::sys();
		if (!$add->config('audit')) return;

		$info = $reason ? array("reason"=>$reason) : array();
        $encForm = $er->getEncForm();
		$audData = array(
			'record_id'=>$er->getId(),
			'record_type'=>"encounter",
			'record_name'=>$encForm['type'],
			'revision_no'=>$er->getEncData('revision_no'),
			'user_id'=>$username,
			'action'=>$action,
			'action_info'=>ADD_Util::serialize($info)
		);
		ADD::db('audit')->createAudit($audData);
	}

    function menuIcon(&$menuIcon) {
    	$page = $this->_page;
    	if($page->getSetting(self::NO_VERIFY_SETTING)) return;
        
        $er = $page->getEncounterRecord();

        if (!$er->exists()) return;
        if ($er->getEncData('is_verified')) {
        	if(is_scalar($menuIcon)) {
        		$menuIcon=array($menuIcon);
        		
			}
			$menuIcon[]="icon-thumbs-up";
		}
    }
}
?>