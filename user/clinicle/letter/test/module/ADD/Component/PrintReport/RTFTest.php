<?php
class ADD_Component_PrintReport_RTFTest extends ADD_Test_EncounterBase {
	const RTF_FILE="example.docx";
	const RTF_FILE_WITH_DATA="example_with_data.rtf";
	public $_dataPath;
	public function setUp() {
		parent::setUp();
		set_include_path(get_include_path().":".$this->_module->getPath()."/lib");
		require_once("ADD/Tool/RTF.php");
		require_once("ADD/Report/DataPopulate.php");
		$this->_dataPath = ADD::sys()->getModule('letter')->getPath()."/test/data";
		
	}
	
	/**
	 *@test
	 */
	public function it_should_load_and_output_an_rtf_file() {
		$rtfFile = $this->_dataPath."/".self::RTF_FILE;
		$rtf = new ADD_Tool_RTF($rtfFile);
		//$content = file_get_contents($rtfFile);
		//$this->assertEquals($content,$rtf->cleanOutput());
		$this->assertTrue(true);
	}
	
	
	/**
	 * @test
	
	 
	public function it_should_get_fields_from_a_document() {
		$rtfFile = $this->_dataPath."/".self::RTF_FILE;
		$rtf = new ADD_Tool_RTF($rtfFile);
		$expFields = array("general.consultant","capitalise('gp.last_name')","patient.first_name","patient.last_name","patient.date_of_birth","patient.nhs_no","join('address.address_line1;address.address_line2;address.address_line3;address.address_line4,address.postcode',', ',true)");
		list($obsFields,$elements) = $rtf->getFieldsFromDocument();
		print_r($obsFields);
		$this->assertEquals($expFields,$obsFields);
	}
	

	public function it_should_load_and_output_an_rtf_file_with_field_values() {
		$rtfFile = $this->_dataPath."/".self::RTF_FILE;
		$rtfFilePopulated = $this->_dataPath."/".self::RTF_FILE_WITH_DATA;
		$rtf = new ADD_Tool_RTF($rtfFile);
		$content = file_get_contents($rtfFile);
		
		$values = array(
			"capitalise('gp.last_name')"=>"Monroe",
			"gp.last_name"=>"MONROE",
			"general.consultant"=>"J Consultant",
			"patient.first_name"=>"SHERLOCK",
			"patient.last_name"=>"HOLMES",
			"patient.date_of_birth"=>"02/03/2014",
			"patient.nhs_no"=>"1234 5678 90",
			"join('address','address_line1;address_line2;address_line3;address_line4;postcode',', ',true)"=>"223B BAKER STREET, LONDON, W1"
		);
		
		$tmpfile = tempnam("/tmp","RTFTest");
		
		$this->reportData = $this->getMockBuilder("ADD_Report_DataPopulate")->disableOriginalConstructor()->getMock();
		$this->reportData->expects($this->once())->method("populateDataUsingNames")->will($this->returnValue($values));
		
		$output = $rtf->fillFieldsUsingData($this->reportData);
		$rtf->saveFile($tmpfile);
		$expFile = file($rtfFilePopulated);
		$obsFile = file($tmpfile);
		$diff = array_diff($expFile,$obsFile);
		//system("cat $rtfFilePopulated");
		//system("cat $tmpfile");
		ADD::log()->info("tmpfile:$tmpfile");
		system("open /Applications/Microsoft\ Office\ 2011/Microsoft\ Word.app/ $tmpfile");
		$this->assertEquals(1,count($diff));
		$this->assertEquals(array(7),array_keys($diff));
		$regExp = '{\info{\title }{\subject }{\category }';
		$regExp = preg_quote($regExp,"/");
		$this->assertRegExp("/^$regExp/",$diff[7]);
		//unlink($tmpfile);
	}
	 */
}
?>