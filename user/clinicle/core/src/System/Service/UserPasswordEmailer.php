<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\System\Service;
use ClinicLE\Service\Email\Email;
use ClinicLE\Service\Email\Emailer;
use ClinicLE\Common\Config\AbstractConfig;

class UserPasswordEmailer {
	private $lastResetInfo=null;
	
	/**
	 * 
	 * @param Emailer $emailer
	 * @param AbstractConfig $config
	 */
	function __construct(Emailer $emailer, AbstractConfig $config) {
		$this->emailer = $emailer;
		$this->config = $config;
		$this->password = substr(hash('sha512', rand()), 0, 8);
	}
	function createPasswordData() {
		$userData['password'] = $this->password;
        $userData['last_changed']=null;
		return $userData;
	}
    function getUserMessage() {
		$siteAdminEmail=$this->config->getSetting('site_admin_email','robert.free@uhl-tr.nhs.uk');
		$siteAdminName=$this->config->getSetting('site_admin_name','Dr. Rob Free');

		return 'You have been emailed a new temporary password. Please contact <a href="mailto:'.$siteAdminEmail.'">'.$siteAdminName.'</a> if you have any problems.';
	}
	function sendResetEmail($userToReset) {
		$config = $this->config;

		$siteTitle = $config->getSetting('site_title','Airway Disease Database');
		$siteName=$config->getSetting('site_name','ADD');
		$siteHost=$config->getSetting('site_host','lrbaddlive.lrbru.xuhl-tr.nhs.uk');
		$siteAdminEmail=$config->getSetting('site_admin_email','robert.free@uhl-tr.nhs.uk');
		$siteAdminName=$config->getSetting('site_admin_name','Dr. Rob Free');
        $siteTag = $config->getSetting('site_tag','');
		$email = new Email("Reset $siteTitle user account",$siteTitle,"lrbru-noreply@uhl-tr.nhs.uk");
		$email->setBody(array("Your password has been reset. Your temporary login details are:",
				"Username:%s\nPassword:%s","","To access $siteName $siteTag please go to this web site: http://$siteHost",
				"If you are still having problems logging in please email $siteAdminEmail"
		));
		
		$username = $userToReset->username;
		$toAddress = $userToReset->email;
		$email->setPrintfArgs(array($username,$this->password));
		
		$this->emailer->sendTo($email,$toAddress);
		$this->lastResetInfo = $email->getBodyAsText();
	}
	function getLastResetInfo() {
		return $this->lastResetInfo;
	}
}
