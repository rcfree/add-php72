<?php
namespace ADD\Referral\HomeTool;

use ClinicLE\Module\Exception\ItemUndefinedException;
use ClinicLE\Options\HomeTool\AbstractPageSpecificHomeTool;
use ADD;
class ReviewReferralsHomeTool extends AbstractPageSpecificHomeTool {
    public function setPage($page) {
        parent::setPage($page);
        $page->setExpectedPermissions(array("view"));
    }
	public function isAvailable() {
        if(self::isReferralDeactivated($this->getPage())) {
			return false;
		}
		return $this->getPage()->getPermission('view');
	}
	static function isReferralDeactivated($page) {
	    if(!$page) {
	        throw new ItemUndefinedException("Page not provided");
        }
		$module = $page->getModule();
		$section = ADD::sys()->getConfig()->getSection("Referral");
		if(!$section) return false;
		if($section->get("deactivate_".$module->getAlias())==1) {
			return true;
		}
		return false;
	}
	public function createTool() {
		$html = ADD::web()->newContent();
		$page = $this->getPage();

		$fieldNames = explode(";",$page->assertSetting("tableFields"));
		$statusField = $page->assertSetting("statusField");
		$fieldLabels = array_map(function($fieldName) { return ADD_Util::nameToLabel($fieldName); },$fieldNames);

		$formPage = $page->getModule()->getPageInStructure($page->assertSetting('formPage'));
		$form = $formPage->getComponent('Form');
		$form->setup();

		foreach($fieldLabels as $fieldName) {
			$headings[]=$fieldName;
		}

		$row = array();
		foreach($fieldNames as $fieldName) {
			$row[]=array("{{row.".$fieldName.".label}}");
		}

		$attrs = array();
		$attrs['ng-cloak']="";
		$unverifiedRecords = new ADD_Content_Html_EncounterGridHelper($page->getName(),$this->_page->getTitle(),$attrs);
		$unverifiedRecords->putProperty("pim",$page->getModule()->getName().":".$formPage->getName());
		$unverifiedRecords->putProperty("filter",implode("|",array($statusField,"eq","null")));
		$unverifiedRecords->setHeadings($headings);

		$unverifiedRecords->setRepeatRow($row);
		$unverifiedRecords->addActionAfterRow(new ADD_Client_Action_EditPage($formPage,$this->_page));
		$unverifiedRecords->addActionAfterRow(new ADD_Client_Action_DeleteRecord($formPage,$this->_page));

		$html->addHtml($unverifiedRecords->create($page));

		$html->endElement("div");

		return $html;
	}
	public function toolTitle() {
		return $this->_page->getSetting('title')."s";
	}
	public function toolWeight() {
		return -99;
	}
	public function expectedPermissions(&$expPerms) {
		$expPerms[]="view";
	}
	public function toolClass() {}
}

