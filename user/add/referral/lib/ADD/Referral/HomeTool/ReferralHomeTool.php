<?php
namespace ADD\Referral\HomeTool;

use ClinicLE\Module\Exception\ItemUndefinedException;
use ClinicLE\Options\HomeTool\AbstractPageSpecificHomeTool;
use CLE;


class ReferralHomeTool extends AbstractPageSpecificHomeTool {
	public function createTool() {
		$html = CLE::web()->newContent();
		$page = $this->getPage();
		$referralType = $page->assertSetting("referral_type");
		$referralPageName = $page->assertSetting("referral_page");
		$referralPage = $page->getModule()->getPage($referralPageName);
		if(!$referralPage) throw new ItemUndefinedException("Referral page '".$referralPageName."' not found in 
		module '".$page->getModule()->getName()."'");
		$html->addTextElement("h4","Refer to ILD clinic");
		$html->addTextElement("p","You can add a patient referral for ".$referralType." without having an ADD login");
		$html->addTextElement("a","<i class='icon-plus'></i>&nbsp;Add $referralType referral",array("href"=>$referralPage->createMenuPath(),"class"=>"btn"));
		return $html;
	}
	public function toolTitle() {
		$page = $this->getPage();
		$referralType = $page->assertSetting("referral_type");
		return $referralType." referral";
	}
	public function isAvailable() {
		if(ReviewReferralsHomeTool::isReferralDeactivated($this->getPage())) {
			return false;
		}
		if($this->getPage()->getSetting('hide_if_login') && CLE::sys()->getAccessManager()->isLoggedIn()) return false;
		return true;
	}
	public function toolClass() {}
	public function toolWeight() {
		return 999;
	}
}
?>
