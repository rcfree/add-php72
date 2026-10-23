<?php
namespace ClinicLE\Options\HomeTool;
use CLE;

class LoginHomeTool extends AbstractHomeTool {
	public function isAvailable() {
		$am = CLE::sys()->getAccessManager();
			
		if($am->isLoggedIn()) return false;
		return true;
	}
	
	public function createTool() {
		$am = CLE::sys()->getAccessManager();
			
		$html = CLE::web()->newContent();
		$config = CLE::sys()->getConfig();
		$siteName = $config->getSetting('site_name',"ADD");
		$siteAdminEmail=$config->getSetting('site_admin_email',"robert.free@uhl-tr.nhs.uk");
		$siteAdminName=$config->getSetting('site_admin_name',"Dr Rob Free");
		$siteAdminJob=$config->getSetting('site_admin_job',"Respiratory BRU Informatics Manager");
		$html->addElement("input",array("type"=>"hidden","id"=>"ng-reload","value"=>"1"));
		$html->addTextElement("h4","Login to ".$siteName);
		
		$message = $am->getContext()->session->userdata('add_errors');

		if($message) {
			$html->addTextElement("div",$message,array("class"=>"alert alert-block alert-error"));
		}
		$html->startElement("form",array("class"=>"form-horizontal","action"=>"/auth/login","method"=>"POST"));
		$html->startElement("div",array("class"=>"control-group"));
		$html->addTextElement("label","UHL username");
		$html->addElement("input",array("type"=>"text","name"=>"identity","class"=>"span3"));
		$html->endElement("div");
		$html->startElement("div",array("class"=>"control-group"));
		$html->addTextElement("label","Password");
		$html->addElement("input",array("type"=>"password","name"=>"password","class"=>"span3"));
		$html->addElement("input",array("type"=>"hidden","name"=>"dest","value"=>CLE::sys()->getParam('dest')));
		
		$html->endElement("div");
		
		$html->addTextElement("button","<i class='icon-user icon-white'></i>&nbsp;Login to ".$siteName,array("class"=>"btn btn-primary","type"=>"submit","cle-nong"=>""));
		$html->addTextElement("a","Forgotten password..",array("class"=>"btn btn-link","href"=>"/user/forgotten"));
		
                $html->endElement("form");
		$html->startElement("div",array("class"=>"alert alert-info"));
		$html->addHtml('If you do not have access to '.$siteName.' and would like to get it please email <a href="mailto:'.$siteAdminEmail.'">'.$siteAdminName.'</a> ('.$siteAdminJob.') with your request.');
		$html->endElement("div");
		return $html;
	}
	public function toolTitle() {
		return "Login";
	}
	
	public function toolWeight() {
		return 1;
	}
	public function toolClass() {}
}
?>
