<?php

require_once("ADD/UtilBaseTest.php");

class ADD_Util_EvaluateTest extends ADD_UtilBaseTest {

	/**
	 * @test
	 */
	function it_should_evaluate_a_number_correctly() {
		$this->assertEquals(ADD_Util::evaluate("5"), 5);
	}

	/**
	 * @test
	 */
	function it_should_evaluate_a_function_correctly() {
		$this->assertEquals(ADD_Util::evaluate("number_format(2.081+(0.5846*(pow(1.8,3))-(0.01599*23)*1.8),2)"), 4.83);
	}

	/**
	 * @test
	 */
	function it_should_evaluate_a_field_function_correctly() {
		$this->assertEquals(date("d/m/Y"), ADD_Util::evaluate("today()"));
	}

	/**
	 * @test
	 */
	function it_should_evaluate_quoted_text_correctly() {
		$this->assertEquals("test", ADD_Util::evaluate("'test'"));
	}

	/**
	 * @test
	 */
	function it_should_evaluate_non_quoted_text_correctly() {
		$this->assertEquals("test", ADD_Util::evaluate("test"));
	}

	/**
	 * @test
	 */
	function it_should_evaluate_the_correct_value() {
		ADD_Field_Util::$values = array("height" => 180, "weight" => 81);
		$exp = "(get('weight')/((get('height')/100) * (get('height')/100)))";
		$this->assertEquals(25.0, ADD_Util::evaluate($exp));
	}

	/**
	 * @test
	 */
	function it_should_evaluate_a_complex_value() {
		ADD_Field_Util::$values = array("tot_lean_body_mass" => 40, "tot_bone_min_calc" => 15, "height" => 180);
		$exp = "sum('tot_lean_body_mass;tot_bone_min_calc')/((get('height')/100)*(get('height')/100))";
		$this->assertEquals(17.0, number_format(ADD_Util::evaluate($exp), 1));
	}

	/**
	 * @test
	 */
	function it_should_evaluate_to_empty_value_for_get() {
		ADD_Field_Util::$values = array("height" => 180, "weight" => null);
		$exp = "(get('weight')/((get('height')/100) * (get('height')/100)))";
		$this->assertEmpty(ADD_Util::evaluate($exp));
	}

	/**
	 * @test
	 */
	function it_should_evaluate_to_empty_value_for_sum_if_one_value_is_null() {
		ADD_Field_Util::$values = array("height" => 180, "weight" => null);
		$exp = "sum('height;weight')";
		$this->assertEmpty(ADD_Util::evaluate($exp));
	}

	/**
	 * @test
	 */
	function it_should_evaluate_to_empty_value_for_reversibility() {
		ADD_Field_Util::$values = array("post_bronch_fev1" => 4.2, "pre_bronch_fev1" => null);
		$exp = "(get('post_bronch_fev1')-get('pre_bronch_fev1'))/get('pre_bronch_fev1')*100";
		$this->assertEmpty(ADD_Util::evaluate($exp));
	}

}

?>