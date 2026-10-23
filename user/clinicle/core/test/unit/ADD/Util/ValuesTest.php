<?php
require_once("ADD/UtilBaseTest.php");
class ADD_Util_ValuesTest extends ADD_UtilBaseTest
{
	/**
     * @test
     */
	function it_should_convert_simple_csv_values_into_associative_arrays() {
		$output = ADD_Util::getCsvArrayValues(
			'Settings', 
			array('Settings'=>
				'condition_test=2,condition_target=reason;end_date,condition_action=show,dictionary=drug_status'
			)
		);
		$expected = array (
		  'condition_test' => '2',
			'condition_target'=>'reason;end_date',
			'condition_action'=>'show',
		  'dictionary' => 'drug_status',
		);
		$this->assertEquals($expected,$output);
	}
	
	/**
     * @test
	 * @expectedException ADD_UtilException
	 * @expectedExceptionMessage Key 'dictionary' is a duplicate value in CSV list:condition_test=2,condition_target=reason;end_date,condition_action=show,dictionary=drug_status,dictionary=drug_status
     */
	function it_should_fail_if_simple_csv_values_are_present_multiple_times() {
		$output = ADD_Util::getCsvArrayValues(
			'Settings', 
			array('Settings'=>
				'condition_test=2,condition_target=reason;end_date,condition_action=show,dictionary=drug_status,dictionary=drug_status'
			)
		);
		$expected = array (
		  'condition_test' => '2',
			'condition_target'=>'reason;end_date',
			'condition_action'=>'show',
		  'dictionary' => 'drug_status',
		);
		$this->assertEquals($expected,$output);
	}
	
    /**
     * @test
     */
	function it_should_convert_csv_values_into_appropriate_indexed_arrays() {
		$output = ADD_Util::getCsvArrayValues(
			'Settings', 
			array('Settings'=>
				'condition_test[0]=2,condition_target[0]=reason;end_date,condition_action[0]=show,condition_test[1]=2,condition_target[1]=end_date,condition_action[1]=update,condition_target_value[1]=today(),condition_test[2]=1,condition_action[2]=clear,condition_target[2]=reason;end_date,dictionary=drug_status'
			)
		);
		$expected = array (
		  'condition_test' => 
		  array (
		    0 => '2',
		    1 => '2',
		    2 => '1',
		  ),
		  'condition_target' => 
		  array (
		    0 => 'reason;end_date',
		    1 => 'end_date',
		    2 => 'reason;end_date',
		  ),
		  'condition_action' => 
		  array (
		    0 => 'show',
		    1 => 'update',
		    2 => 'clear',
		  ),
		  'condition_target_value' => 
		  array (
		    1 => 'today()',
		  ),
		  'dictionary' => 'drug_status',
		);
		$this->assertEquals($expected,$output);
	}
	
	/**
     * @test
     */
	function it_should_convert_csv_values_with_spaces_into_appropriate_indexed_arrays() {
		$output = ADD_Util::getCsvArrayValues(
			'Settings', 
			array('Settings'=>
				'condition_test[0]=2, condition_target[0]=reason;end_date, condition_action[0]=show, condition_test[1]=2, condition_target[1]=end_date, condition_action[1]=update, condition_target_value[1]=today(), condition_test[2]=1, condition_action[2]=clear, condition_target[2]=reason;end_date,dictionary=drug_status'
			)
		);
		$expected = array (
		  'condition_test' => 
		  array (
		    0 => '2',
		    1 => '2',
		    2 => '1',
		  ),
		  'condition_target' => 
		  array (
		    0 => 'reason;end_date',
		    1 => 'end_date',
		    2 => 'reason;end_date',
		  ),
		  'condition_action' => 
		  array (
		    0 => 'show',
		    1 => 'update',
		    2 => 'clear',
		  ),
		  'condition_target_value' => 
		  array (
		    1 => 'today()',
		  ),
		  'dictionary' => 'drug_status',
		);
		$this->assertEquals($expected,$output);
	}

    /**
     * @test
     */
        function it_should_return_true_for_empty_values() {
            $this->assertTrue(ADD_Util::isEmpty(array()));
            $this->assertTrue(ADD_Util::isEmpty(null));
            $this->assertTrue(ADD_Util::isEmpty(""));

        }
        /**
         * @test
         */
        function it_should_return_false_for_non_empty_values() {
        	$this->assertFalse(ADD_Util::isEmpty(array(128574 => array("current_status"=>"Available","ilh_identifier"=>"ILH85038384"))));
            $this->assertFalse(ADD_Util::isEmpty(array("test")));
            $this->assertFalse(ADD_Util::isEmpty("test"));
            $this->assertFalse(ADD_Util::isEmpty(0));
            $this->assertFalse(ADD_Util::isEmpty("0"));
            $this->assertFalse(ADD_Util::isEmpty(1));
            $this->assertFalse(ADD_Util::isEmpty(1));
        }

        /**
         * @test
         */
        function it_should_return_an_existing_array_value() {
            $this->assertEquals("value",ADD_Util::val("key",array("key"=>"value")));
        }
		
        /**
         * @test
         */
        function it_should_return_a_defined_default_value_if_the_key_does_not_exist_in_the_array() {
            $this->assertEquals("pigeon",ADD_Util::val("nokey",array("key"=>"value"),"pigeon"));
        }

        /**
         * @test
         */
        function it_should_return_a_null_value_if_the_key_does_not_exist_and_no_default_is_provided() {
            $this->assertNull(ADD_Util::val("nokey",array("key"=>"value")));
        }

        /**
         * @test
         */
        function it_should_return_existing_array_values_as_an_array() {
            $this->assertEquals(
                array("value1","value2","value3"),
                ADD_Util::vals(
                    array("key1","key2","key3"),array("key1"=>"value1","key2"=>"value2","key3"=>"value3")
                )
            );
        }

        /**
         * @test
         */
        function it_should_return_an_array_including_defined_default_values_if_one_or_more_keys_do_not_exist_in_the_array() {
            $this->assertEquals(array("value1","pigeon","pigeon"),
                ADD_Util::vals(
                    array("key1","key4","key5"),array("key1"=>"value1","key2"=>"value2","key3"=>"value3")
                ,"pigeon"
                )
            );
        }

        /**
         * @test
         */
        function it_should_return_an_array_including_null_values_if_one_or_more_keys_do_not_exist_and_no_default_is_provided() {
            $this->assertEquals(array(null,"value3",null),
                ADD_Util::vals(
                    array("key4","key3","key5"),array("key1"=>"value1","key2"=>"value2","key3"=>"value3")
                )
            );
        }
		
		/**
         * @test
         */
        function it_should_return_the_correct_values_if_the_array_is_zero_indexed() {
			$list = array (
				0 => 0,
				1 => '1',
				2 => '2',
				3 => '3',
				4 => '4',
				5 => '5',
			  );
            $this->assertTrue('0'===ADD_Util::val(0,$list,false));
			$this->assertEquals("0",ADD_Util::val("0",$list));
			$this->assertEquals(1,ADD_Util::val("1",$list));
			$this->assertEquals(null,ADD_Util::val(null,$list));
        }
		
		/**
         * @test
         */
        function it_should_return_the_default_if_the_value_is_not_present_or_empty() {
			$list = array (
				1 => '1',
				2 => '',
			  );
            $this->assertFalse(ADD_Util::val(3,$list,false));
			$this->assertFalse(ADD_Util::val(2,$list,false));
        }
}

?>