<?php
namespace EMBER\Calculate;
class DECAFScoreTest extends \PHPUnit_Framework_TestCase {
	const FIB_VALUE=2;
	const NOT_FIB_VALUE=1;
	const JUST_ABOVE_EOS_THRESHOLD=0.05;
	const JUST_BELOW_EOS_THRESHOLD=0.049;
	const BELOW_EOS_THRESHOLD=0.04;
	const ABOVE_EOS_THRESHOLD=0.06;
	
	const JUST_ABOVE_ACID_THRESHOLD=7.3;
	const JUST_BELOW_ACID_THRESHOLD=7.29;
	const BELOW_ACID_THRESHOLD=7.2;
	const ABOVE_ACID_THRESHOLD=7.4;
	
	public function setUp() {
		$this->SUT = new DECAFScore();
	}
	/**
	 * @test
	 */
	public function score_is_zero_if_mrc_is_not_five() {
		for($i=0;$i<4;$i++) {
			$this->assertEquals(0,$this->SUT->determineScore($i,null,self::ABOVE_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE));
		}
	}
	/**
	 * @test
	 */
	 public function score_is_increased_by_1_if_mrc_is_5_and_ext_is_a() {
	 	$this->assertEquals(1,$this->SUT->determineScore(5,"A",self::ABOVE_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE));
	 }
	 /**
	 * @test
	 */
	 public function score_is_increased_by_2_if_mrc_is_5_and_ext_is_b() {
	 	$this->assertEquals(2,$this->SUT->determineScore(5,"B",self::ABOVE_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE));
	 }
	 /**
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	 public function fails_if_mrc_is_5_and_ext_is_not_a_or_b() {
	 	$this->assertEquals(2,$this->SUT->determineScore(5,"C",self::ABOVE_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE));
	 }
	 
	 /**
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	 public function fails_if_mrc_is_5_and_ext_5_is_null() {
	 	$this->SUT->determineScore(5,null,self::ABOVE_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE);
	 }
	 
	 /**
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	 public function fails_if_mrc_is_5_and_ext_5_is_empty() {
	 	$this->SUT->determineScore(5,"",self::ABOVE_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE);
	 }
	 
	 /**
	 * @test
	 */
	 public function score_is_null_if_mrc_is_null_or_empty() {
	 	$this->assertNull($this->SUT->determineScore(null,"B",self::ABOVE_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE));
		$this->assertNull($this->SUT->determineScore("","B",self::ABOVE_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE));
	 }
	 
	 /**
	 * @test
	 */
	 public function score_unchanged_if_above_eosinopenia_threshold() {
	 	$this->assertEquals(2,$this->SUT->determineScore(5,"B",self::JUST_ABOVE_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE));
		$this->assertEquals(2,$this->SUT->determineScore(5,"B",self::ABOVE_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE));
	 }
	 
	 /**
	 * @test
	 */
	 public function score_increased_by_1_when_below_eosinopenia_threshold() {
	 	$this->assertEquals(3,$this->SUT->determineScore(5,"B",self::JUST_BELOW_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE));
		$this->assertEquals(3,$this->SUT->determineScore(5,"B",self::BELOW_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE));
	 }
	 
	 /**
	 * @test
	 */
	 public function score_is_null_if_eosinopenia_threshold_is_null_or_empty() {
	 	$this->assertNull($this->SUT->determineScore(5,"B",self::JUST_BELOW_EOS_THRESHOLD,"N",null,self::NOT_FIB_VALUE));
		$this->assertNull($this->SUT->determineScore(5,"B",self::BELOW_EOS_THRESHOLD,"N","",self::NOT_FIB_VALUE));
	 }
	 
	 /**
	 * @test
	 */
	 public function score_increased_by_1_when_consolidation() {
	 	$this->assertEquals(3,$this->SUT->determineScore(5,"B",self::ABOVE_EOS_THRESHOLD,"Y",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE));
	 }
	 
	 /**
	 * @test
	 */
	 public function score_unchanged_when_no_consolidation() {
	 	$this->assertEquals(2,$this->SUT->determineScore(5,"B",self::ABOVE_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE));
	 }
	 /**
	 * @test
	 */
	 public function score_unchanged_when_above_acidemia_threshold() {
	 	$this->assertEquals(2,$this->SUT->determineScore(5,"B",self::ABOVE_EOS_THRESHOLD,"N",self::JUST_ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE));
		$this->assertEquals(2,$this->SUT->determineScore(5,"B",self::ABOVE_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE));
	 }
	 
	 /**
	 * @test
	 */
	 public function score_increased_by_1_when_below_acidemia_threshold() {
	 	$this->assertEquals(3,$this->SUT->determineScore(5,"B",self::ABOVE_EOS_THRESHOLD,"N",self::BELOW_ACID_THRESHOLD,self::NOT_FIB_VALUE));
		$this->assertEquals(3,$this->SUT->determineScore(5,"B",self::ABOVE_EOS_THRESHOLD,"N",self::JUST_BELOW_ACID_THRESHOLD,self::NOT_FIB_VALUE));
	 }
	 
	 /**
	 * @test
	 */
	 public function score_is_null_when_acidemia_threshold_is_null_or_empty() {
	 	$this->assertNull($this->SUT->determineScore(5,"B",self::ABOVE_EOS_THRESHOLD,"N",null,self::NOT_FIB_VALUE));
		$this->assertNull($this->SUT->determineScore(5,"B",self::ABOVE_EOS_THRESHOLD,"N","",self::NOT_FIB_VALUE));
	 }
	 
	 /**
	 * @test
	 */
	 public function score_increased_by_1_when_rhythm_is_atrial_fib_value() {
	 	$this->assertEquals(3,$this->SUT->determineScore(5,"B",self::ABOVE_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::FIB_VALUE));
	 }
	 
	 /**
	 * @test
	 */
	 public function score_unchanged_when_rhythm_is_not_atrial_fib_value() {
	 	$this->assertEquals(2,$this->SUT->determineScore(5,"B",self::ABOVE_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE));
	 }
	 
	 /**
	 * @test
	 */
	 public function score_is_null_when_rhythm_is_null_or_empty() {
	 	$this->assertNull($this->SUT->determineScore(5,"B",self::ABOVE_EOS_THRESHOLD,"Y",self::ABOVE_ACID_THRESHOLD,null));
		$this->assertNull($this->SUT->determineScore(5,"B",self::ABOVE_EOS_THRESHOLD,"Y",self::ABOVE_ACID_THRESHOLD,""));
	 }
	 
	 /**
	 * @test
	 */
	 public function score_is_correct_when_provided_with_example_values() {
	 	$this->assertEquals(3,$this->SUT->determineScore(5,"A",self::BELOW_EOS_THRESHOLD,"N",self::BELOW_ACID_THRESHOLD,self::NOT_FIB_VALUE));
	 }
	 /**
	 * @test
	 */
	 public function score_is_maximum_when_all_values_are_yes_or_pass_threshold() {
	 	$this->assertEquals(6,$this->SUT->determineScore(5,"B",self::BELOW_EOS_THRESHOLD,"Y",self::BELOW_ACID_THRESHOLD,self::FIB_VALUE));
	 }
	  /**
	 * @test
	 */
	 public function score_is_minimum_when_all_values_are_no_or_do_not_pass_threshold() {
	 	$this->assertEquals(0,$this->SUT->determineScore(4,null,self::ABOVE_EOS_THRESHOLD,"N",self::ABOVE_ACID_THRESHOLD,self::NOT_FIB_VALUE));
	 }
}