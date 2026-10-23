<?php

class ADD_Component_PrintReport_PdfTest extends ADD_Test_EncounterBase {
	static $formMocker = null;
	static $module = null;
    function setUp() {
            parent::setUp();
            ADD::web()->setContentClass('Pdf');
            self::$formMocker = $this->_formMocker;
			self::$module = $this->_module;
            $this->_rpage = ADD::sys()->getModule('report')->getPage('report');
            ADD::db('encounter')->expects($this->any())->method("getEncounterFormWithType")->will($this->returnCallback(function($encType) { return array("id"=>5,"type"=>$encType); } ));
            ADD::db('encounter')->expects($this->any())->method("getObservationFieldsByIdAndName")->will($this->returnCallback(function($encForm) {
                $encType = $encForm['type'];
                $form = ADD_Component_PrintReport_PdfTest::getForm($encType);
                
                $ofs = array();
                foreach($form->getFields() as $field) {
                    
                    $of=ADD_Component_PrintReport_PdfTest::getAsObsField($field);
                    $ofs[$field->getName()]=$of;
                }
                return $ofs;
            }));
            ADD::db('encounter')->expects($this->any())->method("getFieldFromEncounterForm")->will($this->returnCallback(function($encType,$fieldName) {
                $form = ADD_Component_PrintReport_PdfTest::getForm($encType);
                $field = $form->getField($fieldName);
                return ADD_Component_PrintReport_PdfTest::getAsObsField($field);
            }));
            ADD::db('encounter')->expects($this->any())->method("getChildEncountersWithType")->will($this->returnValue(array()));
			ADD::db('dictionary')->expects($this->any())->method("getDictionaryOptions")->will($this->returnCallback(function($name) { return array("1"=>$name."1","2"=>$name."2","3"=>$name."3"); }));
	    
            $this->givenDictionaryGroupsReturned(3);
			
			foreach($this->_module->getPages() as $mpage) {
				if(!$mpage->getComponents()->has("Form")) continue;
				$mpage->getComponents()->get("Form")->setup();
				$mpage->getComponents()->get("Form")->beforeInstall();
			}
    }
	
	public function expectsPatientDemographics() {
		ADD::db('patient')->expects($this->once())->method("getPatientDemographics")->will($this->returnValue(array(
			 array("identifier"=>"S123467","nhs_no"=>"1234567","id"=>"123","first_name"=>"JAMES","last_name"=>"BOND","date_of_birth"=>"03/02/1945"),
			 array("postcode"=>"AB12 34C"),
			 array("gp_title"=>"DR","gp_surname"=>"JONES","gp_firstname"=>"INDIANA","gp_address1"=>"INDIANAPOLIS","gp_address2"=>"INDIANAPOLIS","gp_address3"=>"INDIANAPOLIS","gp_address4"=>"INDIANAPOLIS","gp_postcode"=>"")
		)));
	}
    public static function getForm($encType) {
        list($modName,$pageName) = self::$formMocker->getPageEnc($encType);
        $opage = self::$module->getPage($pageName);
        $form = $opage->getComponent('Form');
		
		return $form;
    }
    static function getAsObsField($field) {
        if (!$field) return null;
        $data = array("name"=>$field->getName(), "type"=>$field->getType(), "settings"=>$field->getSettings(), "title"=>$field->getTitle());
        if(is_a($field,"ADD_Field_List")) {
            $options=$field->getOptions();
            if (!$options) throw new Exception("Field '".$field->getName()."' has no options");
            $data["options"]=$options->getOptionList();
        }
        return $data;
    }
    
    function testBlankReportProducesPdf() {
        $add = ADD::sys();
        $oldRpath = $add->config('report_path');
        $add->getConfig()->setSetting('report_path','/tmp');
        
        ADD::db('audit')->expects($this->once())->method("auditReport");
            
        $page = $this->_page;
        $page->setupComponents();
        $add->setParam('name',$page->getSetting('report-name'));
		$add->setParam('module',$page->getModule()->getName());
        $add->setParam('empty',1);
        $ro = $this->_rpage;
		$ro->setupComponents();
        
		$content = $this->_rpage->createOutput(array());
		//print_r($content);
        $output = $content->output();
        $this->assertIsPdf($output);
		$report = $ro->getReport();
		
        $this->assertFileExists($report->getReportFileName());
		
        $this->assertEquals($output,file_get_contents($report->getReportFileName()));
        unlink($report->getReportFileName());
        $add->getConfig()->setSetting('report_path',$oldRpath);
    }
    
    function testPatientSpecificReportProducesPdf() {
    	ADD::db('audit')->expects($this->once())->method("auditReport");
        $add = ADD::sys();
        
        $oldRpath = $add->config('report_path');
        $add->getConfig()->setSetting('report_path','/tmp');
        
        
        $page = $this->_page;
        $page->setupComponents();
		
		$add->setParam('name',$page->getSetting('report-name'));
        $add->setParam('module',$page->getModule()->getName());
        $add->setParam('id',5);
        
		$ro = $this->_rpage;
		$ro->setupComponents();
		
        $er = $this->getERMock();
        $er->expects($this->once())->method("exists")->will($this->returnValue(true));
		
        $er->expects($this->once())->method("getObsValues")->will($this->returnValue(array()));
        $this->expectsPatientDemographics();
            
        $rconfig = new ADD_Config();
        $rconfigFile = $this->_module->getPath()."/report.ini";
        $rconfig->loadFile($rconfigFile);
        $rsect = $rconfig->getSection($page->getSetting('report-name'));
        
        $add->getEncounterManager()->setExpected($page->getSetting('encounter'),"record",$er);
        $output = $this->_rpage->createOutput(array());
		$report = $ro->getReport();
       	$this->assertHasNoErrors();
        $this->assertIsPdf($output);
        $this->assertFileExists($report->getReportFilename());
        unlink($report->getReportFileName());
        $add->getConfig()->setSetting('report_path',$oldRpath);
    }
    function assertIsPdf($data) {
        $output = unpack('A8PDF',$data);
        $this->assertEquals("%PDF-1.4",$output['PDF']);
    }
}