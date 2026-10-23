<?php

namespace ClinicLE\Options\HomeTool;
use CLE;

class NotifyHomeTool extends AbstractHomeTool {
	public function createTool() {
		$notif = CLE::sys()->config("notification");
		if(!$notif) return;
		$html = CLE::web()->newContent();

		$html->addTextElement("p",$notif);
		return $html;
	}
	public function toolTitle() {
		return "Notification";
	}
	
	public function isAvailable() {
		return true;
	}
	
	public function toolWeight() {
		return -1000;
	}
	
	public function toolClass() {
		return "tool-warn";	
	}
}

