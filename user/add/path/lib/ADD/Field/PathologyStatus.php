<?php

class ADD_Field_PathologyStatus extends ADD_Field_Text {
    public function isStored() {
        return false;
    }
	public function createMarkup($page) {
		$html = $page->html();
        $pathologyStatus = CLE::svc(\ADD\Pathology\Resource\PathologyServices::TRANSFER_STATUS);
        list($status, $statusMessage, $statusTs, $completeTs) = $pathologyStatus->retrieveDetails(ADD::sys()->getPatientId());
        $prefix = $this->getSetting("status_prefix");
        list($statusText, $reattempt, $warning) = $pathologyStatus->determineStatusOutput($status,$statusTs, $completeTs,$prefix);
        
		$html->addTextElement("p",$statusText,array("class"=>$warning ? "text-warn": "text-info"));
	}
}