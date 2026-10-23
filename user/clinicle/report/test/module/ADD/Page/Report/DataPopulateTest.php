<?php
class ADD_Page_ReportTest_DataPopulateTest extends ADD_Test_EncounterBase {
	const PATIENT_ID=123;
	const TEST_MOD="testmod";
	public $SUT;
	
	public function getMockPage($pageName, $settings, $encounterRecord) {
		$page = $this->getMockBuilder('ADD_Page')->disableOriginalConstructor()->getMock();
		$compCol = new ADD_ComponentCollection($page);
		
		$enc = new ADD_Component_Encounter($page);
		$enc->setEncounterRecord($encounterRecord);
		$compCol->add($enc);
		
		$page->expects($this->any())->method("getName")->will($this->returnValue($pageName));
		$page->expects($this->any())->method("getEncounterRecord")->will($this->returnValue($encounterRecord));
		$page->expects($this->any())->method("getComponents")->will($this->returnValue($compCol));
		
		$settingsMap = array();
		foreach($settings as $key => $setting) {
			$settingsMap[]=array($key,null,$settings[$key]);
		}
		$page->expects($this->any())->method("getSetting")->will($this->returnValueMap($settingsMap));
		$page->expects($this->any())->method("html")->will($this->returnValue(ADD::web()->getContent()));
		return $page;
	}
	
	public function getMockModule($modName, array $pages) {
		$module = $this->getMockBuilder('ADD_Module')->disableOriginalConstructor()->getMock();
		$module->expects($this->any())->method("getName")->will($this->returnValue("testmod"));
		$pageMap = array();
		foreach($pages as $page) {
			$pageMap[]=array($page->getName(),$page);
		}
			
		$module->expects($this->any())->method("getPageInStructure")->will($this->returnValueMap($pageMap));
		$module->expects($this->any())->method("getPage")->will($this->returnValueMap($pageMap));
		return $module;
	}
	
	public function getMockRecord($exists,$obsValues=null) {
		$record = $this->getMock("ADD_EncounterRecord");
		$record->expects($this->any())->method("exists")->will($this->returnValue($exists));
		if($obsValues) {
			$this->expectRecordToHaveValues($record,$obsValues);
		}
		return $record;
	}
	
	public function expectRecordToHaveValues($record,$obsValues) {
		$record->expects($this->any())->method("getObsValues")->will($this->returnValue($obsValues));
		$obsValuesMap = array();
		foreach($obsValues as $key => $obsValue) {
			$obsValuesMap[]=array($key,$obsValues[$key]);
		}
		$record->expects($this->any())->method("getObsValue")->will($this->returnValueMap($obsValuesMap));
	}
	public function setUp() {
		parent::setUp();
		set_include_path(get_include_path().":".$this->_module->getPath()."/lib");
		
		
		$this->currentRecord = $this->getMockRecord(true);
		$this->currentRecord2 = $this->getMockRecord(true);
		
		$this->testParent1 = $this->getMockPage("parent1",array("encounter"=>"test-parent1"),$this->currentRecord);
		
		$this->testPage = $this->getMockPage("page1",array("parent"=>"parent1","encounter"=>"test-page1"),$this->currentRecord);
		
		$this->testPage2 = $this->getMockPage("page2",array("parent"=>"parent1","encounter"=>"test-page2"),null);
		
		$this->testPage3 = $this->getMockPage("page3",array("parent"=>"parent1","encounter"=>"test-page3"),null);
		$this->testParent2 = $this->getMockPage("parent2",array("encounter"=>"test-parent2"),$this->currentRecord2);
		$this->testPage4 = $this->getMockPage("page4",array("parent"=>"parent2","encounter"=>"test-page4"),null);
		
		$this->testModule = $this->getMockModule(self::TEST_MOD,array($this->testParent1,$this->testPage,$this->testPage2,$this->testPage3,$this->testPage4));
		
		ADD::sys()->getModules()->add($this->testModule);
		
		$this->SUT=new ADD_Report_DataPopulate(self::PATIENT_ID,self::TEST_MOD,null,$this->testPage);
	}
	/**
	 * @test
	 */
	public function it_should_append_patient_demographics_data_from_the_database() {
		$demographics = array(
			array("identifier"=>"S1234567",
				"first_name"=>"JAMES",
				"last_name"=>"BOND"
			),
			array("address_line1"=>"TO KILL ROAD"),
			array("gp_name"=>"DR NO")
		);
		ADD::db('patient')->expects($this->once())->method("getPatientDemographics")->with(self::PATIENT_ID)->will($this->returnValue($demographics));
		$this->SUT->appendPatientData();
		$this->assertEquals(array (
			'patient.identifier' => 'S1234567',
			'patient.first_name' => 'JAMES',
			'patient.last_name' => 'BOND',
			'address.address_line1' => 'TO KILL ROAD',
			'gp.gp_name' => 'DR NO',
		),$this->SUT->getData());
	}
	
	/**
	 * @test
	 */
	public function it_should_append_report_information() {
		$this->SUT->appendReportData();
		$this->assertEquals(array (
			'report.printed_by' => 'System User',
			'report.date' => date("d/m/Y"),
		),$this->SUT->getData());
	}
	
	/**
	 * @test
	 */
	public function it_should_append_current_encounter_data_if_it_exists() {
		$encData = array("date_of_visit"=>"03/02/2014","occupation"=>"1","conditions"=>array("1"=>"1","2"=>"2"));
		$this->expectRecordToHaveValues($this->currentRecord,$encData);
		
		$this->SUT->appendCurrentEncounterData();
		$this->assertEquals($encData,$this->SUT->getData());
	}
	
	/**
	 * @test
	 */
	public function it_should_append_related_data_if_field_data_from_child_page_is_present() {
		$this->page2Record = $this->getMockRecord(true);
		$this->expectRecordToHaveValues($this->page2Record,array("fev1"=>"4"));
		$em = ADD::sys()->getEncounterManager();
		$em->setExpected("test-page2","child",$this->page2Record,$this->currentRecord);
		$this->SUT->appendDataForRelatedField("page2.fev1");
		$this->assertEquals(array("page2.fev1"=>4),$this->SUT->getData());
	}
	
	/**
	 * @test
	 */
	public function it_should_append_related_data_if_field_data_from_sibling_of_child_page_is_present() {
		$this->parentRecord = $this->getMockRecord(true);
		$this->currentRecord->expects($this->once())->method("getParent")->will($this->returnValue($this->parentRecord));
		$this->page3Record = $this->getMockRecord(true,array("rhinitis"=>"Y"));
		
		$em = ADD::sys()->getEncounterManager();
		$em->setExpected("test-page3","child",$this->page3Record,$this->parentRecord);
		$this->SUT->appendDataForRelatedField("parent.page3.rhinitis");
		$this->assertEquals(array("parent.page3.rhinitis"=>"Y"),$this->SUT->getData());
	}
	
	/**
	 * @test
	 */
	public function it_should_populate_encounter_data_based_on_field_names_along_with_default_patient_and_report_data() {
		$demographics = array(
			array("identifier"=>"S1234567",
				"first_name"=>"JAMES",
				"last_name"=>"BOND"
			),
			array("address_line1"=>"TO KILL ROAD"),
			array("gp_name"=>"DR NO")
		);
		ADD::db('patient')->expects($this->once())->method("getPatientDemographics")->with(self::PATIENT_ID)->will($this->returnValue($demographics));
		
		$encData = array("date_of_visit"=>"03/02/2014","occupation"=>"1","conditions"=>array("1"=>"1","2"=>"2"));
		$this->expectRecordToHaveValues($this->currentRecord,$encData);
		
		$this->page2Record = $this->getMockRecord(true);
		$this->expectRecordToHaveValues($this->page2Record,array("fev1"=>"4"));
		$em = ADD::sys()->getEncounterManager();
		$em->setExpected("test-page2","child",$this->page2Record,$this->currentRecord);
		
		$this->parentRecord = $this->getMockRecord(true);
		$this->currentRecord->expects($this->once())->method("getParent")->will($this->returnValue($this->parentRecord));
		$this->page3Record = $this->getMockRecord(true,array("rhinitis"=>"Y"));
		
		$em = ADD::sys()->getEncounterManager();
		$em->setExpected("test-page3","child",$this->page3Record,$this->parentRecord);
		
		$names = array("date_of_visit","occupation","conditions","page2.fev1","parent.page3.rhinitis");
		$expValues = array("patient.identifier"=>"S1234567","patient.first_name"=>"JAMES","patient.last_name"=>"BOND","address.address_line1"=>"TO KILL ROAD","gp.gp_name"=>"DR NO",
			"date_of_visit"=>"03/02/2014","occupation"=>"1","conditions"=>array("1"=>"1","2"=>"2"),"page2.fev1"=>4,"parent.page3.rhinitis"=>"Y"
			);
		$obsValues = $this->SUT->populateDataUsingNames($names);
		$this->assertEquals($expValues,$obsValues);
	}
	
	/**
	 * @test
	 */
	public function it_should_populate_patient_field_titles_in_a_markup_field() {
		$form = new ADD_Component_Form();
		$field1=new ADD_Field_Markup(array("Field"=>"mkup","Title"=>"{patient.first_name} {patient.last_name}","Type"=>"Markup"));
		$demographics = array(
			array("identifier"=>"S1234567",
				"first_name"=>"JAMES",
				"last_name"=>"BOND"
			),
			array("address_line1"=>"TO KILL ROAD"),
			array("gp_name"=>"DR NO")
		);
		ADD::db('patient')->expects($this->once())->method("getPatientDemographics")->with(self::PATIENT_ID)->will($this->returnValue($demographics));
		
		$form->addField($field1);
		$obsValues = $this->SUT->populateDataUsingForm($form);
		$this->assertEquals("JAMES BOND",$form->getField("mkup")->getTitle());
	}
	
	/**
	 * @test
	 */
	public function it_should_populate_child_field_titles_in_a_markup_field() {
		$this->page2Record = $this->getMockRecord(true);
		$this->expectRecordToHaveValues($this->page2Record,array("fev1"=>"4"));
		$em = ADD::sys()->getEncounterManager();
		$em->setExpected("test-page2","child",$this->page2Record,$this->currentRecord);
		
		$form = new ADD_Component_Form();
		$field1=new ADD_Field_Markup(array("Field"=>"mkup","Title"=>"FEV1: {page2.fev1} l","Type"=>"Markup"));
		
		$form->addField($field1);
		$obsValues = $this->SUT->populateDataUsingForm($form);
		$this->assertEquals("FEV1: 4 l",$form->getField("mkup")->getTitle());
	}
	
		
	/**
	 * @test
	 */
	public function it_should_populate_sibling_field_titles_in_a_markup_field() {
		$this->parentRecord = $this->getMockRecord(true);
		$this->currentRecord->expects($this->once())->method("getParent")->will($this->returnValue($this->parentRecord));
		$this->page3Record = $this->getMockRecord(true,array("rhinitis"=>"Y"));
		
		$em = ADD::sys()->getEncounterManager();
		$em->setExpected("test-page3","child",$this->page3Record,$this->parentRecord);
		
		$form = new ADD_Component_Form();
		$field1=new ADD_Field_Markup(array("Field"=>"mkup","Title"=>"Has rhinitis: {parent.page3.rhinitis}","Type"=>"Markup"));
		
		$form->addField($field1);
		$obsValues = $this->SUT->populateDataUsingForm($form);
		$this->assertEquals("Has rhinitis: Y",$form->getField("mkup")->getTitle());
	}
	
	/**
	 * @test
	 */
	public function it_should_create_data_for_a_simple_function_in_the_field() {
		$this->parentRecord = $this->getMockRecord(true);
		$this->currentRecord->expects($this->once())->method("getParent")->will($this->returnValue($this->parentRecord));
		$this->page3Record = $this->getMockRecord(true,array("disease"=>"asthma"));
		
		$em = ADD::sys()->getEncounterManager();
		$em->setExpected("test-page3","child",$this->page3Record,$this->parentRecord);
		$this->SUT->populateDataUsingNames(array("capitalise('parent.page3.disease')"));
		$this->assertEquals(array("capitalise('parent.page3.disease')"=>"Asthma","parent.page3.disease"=>"asthma"),$this->SUT->getData());
	}
	
	
	/**
	 * @test
	 */
	public function it_should_create_data_for_a_complex_function_in_the_field() {
		$demographics = array(
			array("identifier"=>"S1234567",
				"first_name"=>"JAMES",
				"last_name"=>"BOND"
			),
			array("address_line1"=>"TO KILL ROAD","address_line2"=>"LICENSETON","postcode"=>"007"),
			array("gp_name"=>"DR NO")
		);
		ADD::db('patient')->expects($this->once())->method("getPatientDemographics")->with(self::PATIENT_ID)->will($this->returnValue($demographics));
		
		$em = ADD::sys()->getEncounterManager();
		$func = "join('address','address_line1;address_line2;address_line3;address_line4;postcode',', ',true)";
		$this->SUT->populateDataUsingNames(array($func));
		$this->assertEquals(array('patient.identifier' => 'S1234567',
		'patient.first_name' => 'JAMES',
		'patient.last_name' => 'BOND',
		'address.address_line1' => 'TO KILL ROAD',
		'address.address_line2' => 'LICENSETON',
		'address.postcode' => '007',
		'gp.gp_name' => 'DR NO',$func=>"TO KILL ROAD, LICENSETON, 007"),$this->SUT->getData());
	}
	
	/**
	 * @test
	 */
	public function it_should_populate_linked_field_titles_in_a_markup_field() {
		$this->page2Record = $this->getMockRecord(true);
		$this->page4Record = $this->getMockRecord(true);
		
		$this->expectRecordToHaveValues($this->page2Record,array("fev1"=>"4","page3_id"=>2));
		$this->expectRecordToHaveValues($this->page4Record,array("consultant"=>"J Smith"));
		
		$em = ADD::sys()->getEncounterManager();
		$this->page2Record->expects($this->once())->method("getId")->will($this->returnValue(1));
		$this->page4Record->expects($this->once())->method("getId")->will($this->returnValue(2));
		
		$em->setExpected("test-page2","child",$this->page2Record,$this->currentRecord);
		$em->setExpected("test-page4","child",$this->page2Record,$this->currentRecord);
		
		$form = new ADD_Component_Form();
		$field1=new ADD_Field_Markup(array("Field"=>"mkup","Title"=>"Consultant: {page4.consultant}","Type"=>"Markup"));
		
		$form->addField($field1);
		$obsValues = $this->SUT->populateDataUsingForm($form);
		$this->assertEquals("Consultant: J Smith",$form->getField("mkup")->getTitle());
	}
}
?>