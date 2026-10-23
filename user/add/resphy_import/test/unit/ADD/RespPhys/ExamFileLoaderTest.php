<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ADD_RespPhys_ExamFileLoaderTest extends PHPUnit_Framework_TestCase {
    private $SUT;
    const IDENTIFIER="S12345678";
    const IDENTIFIER2="S12345679";
	
    function setUp() {
       $this->SUT = new ADD_RespPhys_ExamFileLoader();
    }
	
	/**
	 * @test
	 */
	function it_interprets_test_file_1() {
		$examFile = new ADD_RespPhys_ExamFile(__DIR__."/../../../data/HOLMES_S0000002_20150202_1512.TXT");
		$this->SUT->load($examFile);
		$this->assertEquals("HOLMES",$examFile->getLastName());
		$this->assertEquals("Sherlock",$examFile->getFirstName());
		$this->assertEquals("S0000002",$examFile->getIdentifier());
		$this->assertEquals("03/04/1936",$examFile->getDateOfBirth());
		$this->assertEquals("149.0",$examFile->getHeight());
		$this->assertEquals("69.6",$examFile->getWeight());
		$this->assertEquals("02/02/2015 15:12",$examFile->getDateTimeOfTests());
		$this->assertEquals("BTS-ERS.DB",$examFile->getPredictedSource());
		$this->assertEquals("Dr Penchalen",$examFile->getConsultant());
		
		$tests = $examFile->getTests();
		$this->assertCount(71,array_keys($tests));
		$fev1 = $examFile->getTest("FEV1");
		$this->assertEquals(array("TEST"=>"FEV1","UNITS"=>"L","PREDICTEDS"=>"2.97"),$fev1);
		
		$heIc = $examFile->getTest("He-IC");
		$this->assertEquals(array("TEST"=>"He-IC","UNITS"=>"L","PREDICTEDS"=>"2.31","PRE-TEST TEST SEL. MEASURE"=>"2.81"),$heIc);
		
		$o2Final = $examFile->getTest("O2 Final");
		$this->assertEquals(array("TEST"=>"O2 Final","UNITS"=>"%","PRE-TEST TEST SEL. MEASURE"=>"22.37"),$o2Final);
	}
    
    /**
	 * @test
	 */
	function it_outputs_exam_details() {
		$examFile = new ADD_RespPhys_ExamFile(__DIR__."/../../../data/MOUS_S12345678_20141208_1537.TXT");
		$this->SUT->load($examFile);
		
		$expectedExamInfo = array("exam_date"=>"08/12/2014","exam_time"=>"15:37","height"=>"166.0","weight"=>"97.6","predicted_source"=>"BTS-ERS.DB","consultant"=>"Prof. Warnload","source"=>1,"date_imported"=>date("d/m/Y H:i"));
		$this->assertEquals($expectedExamInfo,$examFile->getExamInfo());
	}
	
	 /**
	 * @test
	 */
	function it_interprets_test_file_2() {
		$examFile = new ADD_RespPhys_ExamFile(__DIR__."/../../../data/MONEYPENNY_03021934_1512.TXT");
		$this->SUT->load($examFile);
		$this->assertEquals("MONEYPENNY",$examFile->getLastName());
		$this->assertEquals("Eve",$examFile->getFirstName());
		$this->assertEquals("S0000001",$examFile->getIdentifier());
		$this->assertEquals("24/03/1976",$examFile->getDateOfBirth());
		$this->assertEquals("157.5",$examFile->getHeight());
		$this->assertEquals("59.2",$examFile->getWeight());
		$this->assertEquals("11/09/2015 14:55",$examFile->getDateTimeOfTests());
		$this->assertEquals("BTS-ERS.DB",$examFile->getPredictedSource());
		$this->assertEquals("Dr Cage",$examFile->getConsultant());
		
		$comments = <<<COMMENT
No evidence of airways obstruction.
Lung volumes at lower limits of normal.
TLCO & KCO at lower limits of normal, query HB level.
Overall results normal at this time.
COMMENT;

		$this->assertEquals($comments,$examFile->getComments());
		$tests = $examFile->getTests();
		$this->assertCount(71,array_keys($tests));
		$fev1 = $examFile->getTest("FEV1");
		$this->assertEquals(array("TEST"=>"FEV1","UNITS"=>"L","PREDICTEDS"=>"1.49","PRE-TEST TEST SEL. MEASURE"=>"1.65"),$fev1);
		
		$heIc = $examFile->getTest("FVC");
		$this->assertEquals(array("TEST"=>"FVC","UNITS"=>"L","PREDICTEDS"=>"1.86","PRE-TEST TEST SEL. MEASURE"=>"2.16"),$heIc);
		
		$o2Final = $examFile->getTest("O2 Final");
		$this->assertEquals(array("TEST"=>"O2 Final","UNITS"=>"%","PRE-TEST TEST SEL. MEASURE"=>"22.32"),$o2Final);
	}
	
	/**
	 * @test
	 */
	function it_interprets_test_file_with_formatted_fields() {
		$examFile = new ADD_RespPhys_ExamFile(__DIR__."/../../../data/MONEYPENNY_03021934_1512.TXT");
		$this->SUT->load($examFile);
		$this->assertEquals("MONEYPENNY",$examFile->getLastName());
		$this->assertEquals("Eve",$examFile->getFirstName());
		$this->assertEquals("S0000001",$examFile->getIdentifier());
		$this->assertEquals("24/03/1976",$examFile->getDateOfBirth());
		$this->assertEquals("157.5",$examFile->getHeight());
		$this->assertEquals("59.2",$examFile->getWeight());
		$this->assertEquals("11/09/2015 14:55",$examFile->getDateTimeOfTests());
		$this->assertEquals("BTS-ERS.DB",$examFile->getPredictedSource());
		$this->assertEquals("Dr Cage",$examFile->getConsultant());
		
		$comments = <<<COMMENT
No evidence of airways obstruction.
Lung volumes at lower limits of normal.
TLCO & KCO at lower limits of normal, query HB level.
Overall results normal at this time.
COMMENT;

		$this->assertEquals($comments,$examFile->getComments());
		$tests = $examFile->getTests();
		$this->assertCount(71,array_keys($tests));
		$fev1 = $examFile->getTest("FEV1");
		$this->assertEquals(array("TEST"=>"FEV1","UNITS"=>"L","PREDICTEDS"=>"1.49","PRE-TEST TEST SEL. MEASURE"=>"1.65"),$fev1);
		
		$heIc = $examFile->getTest("FVC");
		$this->assertEquals(array("TEST"=>"FVC","UNITS"=>"L","PREDICTEDS"=>"1.86","PRE-TEST TEST SEL. MEASURE"=>"2.16"),$heIc);
		
		$o2Final = $examFile->getTest("O2 Final");
		$this->assertEquals(array("TEST"=>"O2 Final","UNITS"=>"%","PRE-TEST TEST SEL. MEASURE"=>"22.32"),$o2Final);
	}
	
	/**
	 * @test
	 * @expectedException ADD_Exception_InvalidFileException
	 */
	function it_fails_with_file_in_the_wrong_format() {
		$examFile = new ADD_RespPhys_ExamFile(__DIR__."/../../../data/WRONG_FORMAT.TXT");
		$this->SUT->load($examFile);
	}
	
	/**
	 * @test
	 */
	function it_creates_a_unique_identifier_for_an_exam() {
		$examFile = new ADD_RespPhys_ExamFile(__DIR__."/../../../data/MONEYPENNY_03021934_1512.TXT");
		$this->SUT->load($examFile);
		$this->assertEquals("270aaf52a8a5aee15238df753ff86b2f408d16f6",$examFile->generateUniqueId());
	}
	
}