<?php

class ADD_Component_MultiEncounterCreate_SubmitTest extends ADD_Test_EncounterBase {
	
	public function setUp() {
		parent::setUp();
	}
	/**
	 * @test
	 */
	function it_should_output_the_form_contents() {
		$values = array(
			"spirometry_date"=>"03/02/2014",
			"pred_fev1"=>"2.5",
			"pred_fvc"=>"2.8",
			"pred_fev1_fvc"=>"1.9",
			"fev1"=>"2",
			"fvc"=>"2.4",
			"fev1_fvc"=>"1.8",
			"pc_pred_fev1"=>"66",
			"pc_pred_fvc"=>"78",
			"pc_pred_fev1_fvc"=>"90",
			'height'=>null
		);
		
		$er = $this->getERMock();
		$expected[0]=array (
			'test_type' => '1',
			'treatment'=>'1',
			'prediction_equation' => '1',
			'predicted' => '2.5',
			'pre_value' => '2',
			'pre_pc_predicted' => '66',
			'test_date' => '03/02/2014',
			'height'=>null
		  );
		$expected[1]=array (
			'test_type' => '2',
			'treatment'=>'1',
			'prediction_equation' => '1',
			'predicted' => '2.8',
			'pre_value' => '2.4',
			'pre_pc_predicted'=>'78',
			'test_date' => '03/02/2014',
			'height'=>null
		  );
		
		$expected[2]=array (
			'test_type' => '3',
			'treatment'=>'1',
			'prediction_equation' => '1',
			'predicted' => '1.9',
			'pre_value' => '1.8',
			'pre_pc_predicted'=>'90',
			'test_date' => '03/02/2014',
			'height'=>null,
		  );
		
		for($i=0;$i<count($expected);$i++) {
			$er->expects($this->at($i))->method("store")->with($expected[$i]);
		}
		$per = $this->_page->getParent()->getEncounterRecord();
		$em = ADD::sys()->getEncounterManager();
		$em->setExpected("inv-lungfunc-result","newChild",$er,$per);
		
		$this->_page->submitForm($values);
		$this->assertHasNoErrors();
		$this->assertHasNoWarnings();
	}
	
}