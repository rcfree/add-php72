<?php
namespace ADD\Referral\HomeTool;

use ClinicLE\Options\HomeTool\AbstractPageSpecificHomeTool;
use CLE;
use ClinicLE\Common\Util;

class PatientLinkHomeTool extends AbstractPageSpecificHomeTool {
    public function setPage($page) {
        parent::setPage($page);
        $page->setExpectedPermissions(array("view"));
    }
	public function isAvailable() {
	    if(ReviewReferralsHomeTool::isReferralDeactivated($this->getPage())) {
			return false;
		}
		return $this->getPage()->getPermission('view');
	}
	public function createTool() {
		$html = CLE::web()->newContent();
		$page = $this->getPage();
		$formPage = $page->getModule()->getPageInStructure($page->assertSetting('formPage'));
		
		$edb = CLE::db('encounter');
		$esql = "select count(*) from encounter e 
					left join encounter_form ef on ef.id = e.encounter_form_id
					left join patient p on e.patient_id = p.id 
					where ef.type=? and e.is_deleted!=1 and p.identifier=?";
		$eparams = array($formPage->getSetting('encounter'),$page->getSetting('specialIdentifier'));
		
		$ecount = $edb->fetchSingleValue($esql,$eparams);
		if($ecount==0) {
			$html->addTextElement("p","No ".strtolower(Util::pluralize($ecount,$page->getSetting('tableTitle')))." to resolve");
			return $html;
		}
		$html->addTextElement("p",$ecount." ".strtolower(Util::pluralize($ecount,$page->getSetting('title')))." to resolve");
		$html->startElement("a",array("href"=>$page->createMenuPath(),"class"=>"btn"));
		$html->addHtml("<i class='icon-list'></i>&nbsp;Resolve ".strtolower($page->getSetting('tableTitle'))."s");
		$html->endElement("a");
		return $html;
	}
	public function toolTitle() {
		return $this->getPage()->getSetting('title');
	}
	public function toolWeight() {
		return -99;
	}
	public function toolClass() {}
}
