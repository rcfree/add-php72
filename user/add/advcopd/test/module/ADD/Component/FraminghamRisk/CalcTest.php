<?php
set_include_path(getenv('ADD_PATH')."/test/module");

class ADD_Component_FraminghamRiskTest extends ADD_Test_EncounterBase {
	
	function testF56() {
		list($points, $risk) = ADD_Component_FraminghamRisk::calcFramScore(
			false,
			56, 
			200, 
			true, 
			50,
			false,
			200
		);
		$this->assertEquals(20,$points);
		$this->assertEquals("11%",$risk);
	}
	
	function testF34() {
		list($points, $risk) = ADD_Component_FraminghamRisk::calcFramScore(
			false,
			34, 
			140, 
			false, 
			40,
			false,
			130
		);
		$this->assertEquals(-4,$points);
		$this->assertEquals("<1%",$risk);
	}
	function testF46WithSmoking() {
		list($points, $risk) = ADD_Component_FraminghamRisk::calcFramScore(
			false,
			46, 
			240, 
			true, 
			80,
			false,
			140
		);
		$this->assertEquals(20,$points);
		$this->assertEquals("11%",$risk);
	}
	function testF46WithoutSmoking() {
		list($points, $risk) = ADD_Component_FraminghamRisk::calcFramScore(
			false,
			46, 
			240, 
			false, 
			80,
			false,
			140
		);
		$this->assertEquals(13,$points);
		$this->assertEquals("2%",$risk);
	}
	function testF46WithTreatment() {
		list($points, $risk) = ADD_Component_FraminghamRisk::calcFramScore(
			false,
			46, 
			240, 
			false, 
			80,
			true,
			140
		);
		$this->assertEquals(15,$points);
		$this->assertEquals("3%",$risk);
	}
	function testF78() {
		list($points, $risk) = ADD_Component_FraminghamRisk::calcFramScore(
			false,
			78, 
			204, 
			true, 
			43,
			false,
			120
		);
		$this->assertEquals(19,$points);
		$this->assertEquals("8%",$risk);
	}
	
	function testM56() {
		list($points, $risk) = ADD_Component_FraminghamRisk::calcFramScore(
			true,
			56, 
			200, 
			true, 
			50,
			false,
			200
		);
		$this->assertEquals(16,$points);
		$this->assertEquals("25%",$risk);
	}
	
	function testM34() {
		list($points, $risk) = ADD_Component_FraminghamRisk::calcFramScore(
			true,
			34, 
			140, 
			false, 
			40,
			false,
			130
		);
		$this->assertEquals(-7,$points);
		$this->assertEquals("<1%",$risk);
	}
	function testM46WithSmoking() {
		list($points, $risk) = ADD_Component_FraminghamRisk::calcFramScore(
			true,
			46, 
			240, 
			true, 
			80,
			false,
			140
		);
		$this->assertEquals(14,$points);
		$this->assertEquals("16%",$risk);
	}
	function testM46WithoutSmoking() {
		list($points, $risk) = ADD_Component_FraminghamRisk::calcFramScore(
			true,
			46, 
			240, 
			false, 
			80,
			false,
			140
		);
		$this->assertEquals(9,$points);
		$this->assertEquals("5%",$risk);
	}
	function testM46WithTreatment() {
		list($points, $risk) = ADD_Component_FraminghamRisk::calcFramScore(
			true,
			46, 
			240, 
			false, 
			80,
			true,
			140
		);
		$this->assertEquals(10,$points);
		$this->assertEquals("6%",$risk);
	}
	function testM78() {
		list($points, $risk) = ADD_Component_FraminghamRisk::calcFramScore(
			true,
			78, 
			204, 
			true, 
			43,
			false,
			120
		);
		$this->assertEquals(15,$points);
		$this->assertEquals("20%",$risk);
	}
        
        function testM83() {
		list($points, $risk) = ADD_Component_FraminghamRisk::calcFramScore(
			true,
			83, 
			300, 
			true, 
			50,
			true,
			120
		);
		$this->assertEquals("N/A",$points);
		$this->assertEquals("N/A",$risk);
	}
	
}
?>