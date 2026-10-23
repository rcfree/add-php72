<?php
use ADD\Referral\HomeTool\ReviewReferralsHomeTool;

class ADD_Component_PatientLinkTable extends ADD_Component {
	public function displayAccess() {
		if(ReviewReferralsHomeTool::isReferralDeactivated($this->getPage())) {
			return false;
		}
		if(!$this->_page->getPermission('view')) {
			throw new ADD_NoPermissionToAccessException("You do not have permission to view this table.");
		}
	}
	public function generate() {
		$html = ADD::web()->getContent();
		$page = $this->_page;
		
		$fieldNames = explode(";",$page->assertSetting("tableFields"));

		$formPage = $page->getModule()->getPageInStructure($page->assertSetting('formPage'));
		if(!$formPage) throw new Exception("Page '".$page->assertSetting('formPage')."' in module '".$page->getModule()->getAlias()."' not found. Page settings:".$page->getName()." - ".print_r($page->getSettings(),1));
		$form = $formPage->getComponent('Form');
		$form->setup();
		$patient =ADD::db('patient')->getPatientWithIdentifier($page->assertSetting('specialIdentifier'));
		if(!$patient) return;
		

		
		$attrs = array();
		$updateField = $page->getSetting('updatefield');
        $container = new \ClinicLE\Client\Container\Container($page->getName(),"EncounterGridController");
        $linkedPatients = new \ClinicLE\Client\Container\Table($this->_page->getSetting('tableTitle'),"control.data
        .grid","row");
        $linkedPatients->setPaged(true);
        $container->putProperty("rows_per_page",$this->getSetting("rows_per_page",20));
        $container->putProperty("page_no",1);

        if($updateField) {
            $container->putProperty("updatefield",$updateField);
            $container->putProperty("updatevalue",$page->assertSetting("updatevalue"));
        }
		$container->putAttr('ng-cloak',"");

		$container->putProperty("pid",$patient['id']);
		$container->putProperty("pim",$page->getModule()->getName().":".$formPage->getName());
		$container->putProperty("specialstatus","held");
		$container->putProperty("refresh",$page->getSetting('refresh'));
		
		
        $row = array();
		foreach($fieldNames as $fieldName) {
            $linkedPatients->addCell(new \ClinicLE\Client\Container\Cell\FieldCell(ADD_Util::nameToLabel($fieldName),
                $fieldName.".label"));
        }
		if($page->getSetting('hasIssues')) $linkedPatients->addCell(new \ClinicLE\Client\Container\Cell\RowActionCell(new
        ADD_Client_Action_IssuesButton($formPage,$this->_page)));
	
		$linkedPatients->addCell(new \ClinicLE\Client\Container\Cell\RowActionCell(new ADD_Client_Action_DeleteRecord
        ($formPage,$this->_page)));
		
		$linkedPatients->addCell(new \ClinicLE\Client\Container\Cell\RowActionCell(new
        ADD_Client_Action_LinkToPatientButton($formPage,$this->_page)));
		$container->addToContent($linkedPatients);
		$html->addHtml($container->createHtml());

	}
	public function title(&$title) {
		$title=$this->_page->getSetting('title');
	}
	public function expectedPermissions(&$expPerms) {
		$expPerms[]="view";
	}
	public function menuPath(&$menuPath) {
		$modName = $this->_page->getModule()->getSuperModule()->getAlias();
		$menuPath = $modName."/".$this->_page->getName();
	}
}

