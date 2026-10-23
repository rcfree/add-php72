<?php

namespace ClinicLE\Options\HomeTool;
use CLE;
use ClinicLE\Common\Util;

class ResetQuestionHomeTool extends AbstractHomeTool {
	public function isAvailable() {
		$add = CLE::sys();
		if($add->config('ResetQuestionHomeTool.disable')) return false;
			
		$am = $add->getAccessManager();
		$user = $am->getUser();
		if(!$user) return false;
		
		if(!property_exists($user,"reset_question_id") || !property_exists($user, "reset_answer_hash")) {
			return true;
		}
		
		if(Util::isEmpty($user->reset_question_id) || Util::isEmpty($user->reset_answer_hash)) {
			return true;
		}
		return false;
	}
	
	public function createTool() {
		$html = CLE::web()->newContent();
		$html->addTextElement("p","You have not yet set up your reset password question/answer. This will allow you to reset your password without contacting the administrator in future.");
		$html->addTextElement("a","<i class='icon-user'></i>&nbsp;Setup reset password",array("href"=>"/user/question","class"=>"btn"));
		return $html;
	}
	public function toolTitle() {
		return "Setup reset password question/answer";
	}
	public function toolWeight() {
		return -1000;
	}
	public function toolClass() {
		return "tool-warn";
	}
}
?>
