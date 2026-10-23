<?php
date_default_timezone_set("Europe/London");
require_once("add.php");
require_once("ADD/Field/Util.php");
require_once("ADD/Tool/Evaluate.php");

class ADD_Tool_EvaluateTest extends PHPUnit_Framework_TestCase
{
		/**
		 * @test
		 */
		function it_should_evaluate_the_correct_value() {
			ADD_Field_Util::$values = array("height"=>180,"weight"=>81);
			$exp = "today()";
			$ev = new ADD_Tool_Evaluate($exp);
			$this->assertEquals(date("d/m/Y"),$ev->evaluate());
		}
		
		/**
		 * @test
		 */
		function it_should_evaluate_a_function_with_no_parameters() {
			ADD_Field_Util::$values = array("height"=>180,"weight"=>81);
			$exp = "mytestfunction()";
			$ev = new ADD_Tool_Evaluate($exp);
			$this->assertEquals("mytest",$ev->evaluate("ADD_Tool_EvaluateTest"));
		}
		
		static function mytestfunction() {
			return "mytest";
		}
}

?>