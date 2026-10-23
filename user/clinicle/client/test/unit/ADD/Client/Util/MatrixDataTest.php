<?php
set_include_path(getenv('ADD_MODULE_PATH')."/php:".get_include_path());
require_once("ADD/Client/Util/MatrixConvert.php");

class ADD_Client_Util_MatrixConvertTest extends ADD_BaseTestFixture {
    private $SUT;
	private $flatData;
	
	function setUp() {
		$this->SUT = new ADD_Client_Util_MatrixConvert("xvalue","yvalue","value");
		$this->flatData = array(
			array("xvalue"=>"X1","yvalue"=>"Y1","value"=>"A"),
			array("xvalue"=>"X2","yvalue"=>"Y1","value"=>"B"),
			array("xvalue"=>"X3","yvalue"=>"Y1","value"=>"C"),
			array("xvalue"=>"X1","yvalue"=>"Y2","value"=>"D"),
			array("xvalue"=>"X2","yvalue"=>"Y2","value"=>"E"),
			array("xvalue"=>"X4","yvalue"=>"Y2","value"=>"F")
		);
	}
	
	
	/**
	 * @test
	 */
	function it_should_convert_flat_data_to_column_headings() {
		list($xKeyedData, $obsXHeadings, $yHeadings) = $this->SUT->convertDataToXKeyed($this->flatData);
		$expXHeadings=array("X1","X2","X3","X4");
		$this->assertEquals($expXHeadings,$obsXHeadings);
	}
	
	/**
	 * @test
	 */
	function it_should_convert_flat_data_to_row_headings() {
		list($xKeyedData, $xHeadings, $obsYHeadings) = $this->SUT->convertDataToXKeyed($this->flatData);
		$expYHeadings=array("Y1","Y2");
		$this->assertEquals($expYHeadings,$obsYHeadings);
	}
	
	/**
	 * @test
	 */
	function it_should_convert_flat_data_to_associative_data() {
		list($obsXKeyedData, $xHeadings, $yHeadings) = $this->SUT->convertDataToXKeyed($this->flatData);
		$expXKeyedData = array(
			"X1"=>array("Y1"=>"A","Y2"=>"D"),
			"X2"=>array("Y1"=>"B","Y2"=>"E"),
			"X3"=>array("Y1"=>"C"),
			"X4"=>array("Y2"=>"F")
		);
		$this->assertEquals($expXKeyedData,$obsXKeyedData);
	}
	
	/**
	 * @test
	 */
	function it_should_create_a_matrix_from_a_flat_data() {
		
		$obsMatrix = $this->SUT->process($this->flatData);
		$expMatrix = array(
			array(" ","X1","X2","X3","X4"),
			array("Y1","A","B","C"," "),
			array("Y2","D","E"," ","F")
		);
		
		$this->assertEquals($expMatrix,$obsMatrix);
	}
	
	/**
	 * @test
	 */
	function it_should_return_null_if_no_data() {
		
		$obsMatrix = $this->SUT->process(array());
		
		$this->assertEquals(array(),$obsMatrix);
	}
}