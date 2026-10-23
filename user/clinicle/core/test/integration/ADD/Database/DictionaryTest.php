<?php

class ADD_Database_DictionaryTest extends PHPUnit_Framework_TestCase
{
	protected static $ddb;
	
	static function setUpBeforeClass() {
		$add = ADD::sys();
		
		self::$ddb = ADD::db('dictionary');
        }
          
        /**
         * @test
         */
        function it_will_create_a_dictionary() {
            $dict = self::$ddb->createDictionary(array("name"=>"test1","title"=>"Test1"));
            $this->assertEquals("test1",$dict['name']);
            $this->assertEquals("Test1",$dict['title']);
            $this->assertGreaterThan(0,$dict['id']);
        }
        
        /**
         * @test
         */
        function it_will_add_a_dictionary_item_to_a_dictionary() {
            $dict = self::$ddb->createDictionary(array("name"=>"test2","title"=>"Test2"));
            $di = self::$ddb->storeDictionaryItem($dict,array("value"=>1,"title"=>"Item 1"));
            $this->assertEquals($dict['id'],$di['dictionary_id']);
            $this->assertGreaterThan(0,$di['id']);
            $this->assertEquals(1,$di['value']);
            $this->assertEquals("Item 1",$di['title']);
            $di2 = self::$ddb->getDictionaryItemWithValue($dict,1);
            $similars = array_intersect($di, $di2);
                    
            $this->assertCount(count($di),$similars);
        }
        
        /**
         * @test
         */
        function it_will_create_a_dictionary_group_if_it_does_not_exist() {
            $dict = self::$ddb->createDictionary(array("name"=>"test3","title"=>"Test3"));
            $di = self::$ddb->storeDictionaryItem($dict,array("value"=>1,"title"=>"Item 1","new_group"=>1,"group_label"=>"Just a group"));
            
            $this->assertEquals($dict['id'],$di['dictionary_id']);
            $this->assertGreaterThan(0,$di['id']);
            $this->assertGreaterThan(0,$di['dictionary_group_id']);
            $this->assertEquals(1,$di['value']);
        }
        
        /**
         * @test
         */
        function it_will_fail_if_input_data_has_non_existing_group_and_no_new_flag() {
            $dict = self::$ddb->getDictionaryByName("test1");
            $inputData = array("group"=>"just_a_group");
            $this->setExpectedException("ADD_DictionaryNotFoundException");
            $dg = self::$ddb->obtainDictionaryGroupFromInput($dict,$inputData);
        }
        
        /**
         * @test
         */
        function it_will_return_a_new_group_if_input_data_has_new_flag_and_label() {
            $dict = self::$ddb->getDictionaryByName("test1");
            $inputData = array("new_group"=>1,"group_label"=>"Just a group");
            $dg = self::$ddb->obtainDictionaryGroupFromInput($dict,$inputData);
            
            $this->assertGreaterThan(0,$dg['id']);
            
            $this->assertEquals("Just a group",$dg['title']);
            $this->assertEquals("just_a_group",$dg['name']);
        }
        
        /**
         * @test
         */
        function it_will_return_an_existing_group_if_input_data_has_new_flag_but_label_exists() {
            $dict = self::$ddb->getDictionaryByName("test1");
            $inputData = array("new_group"=>1,"group_label"=>"Another group");
            $dg1 = self::$ddb->obtainDictionaryGroupFromInput($dict,$inputData);
            $dgId = $dg1['id'];
            
            $inputData = array("new_group"=>1,"group_label"=>"Another group");
            $dg2 = self::$ddb->obtainDictionaryGroupFromInput($dict,$inputData);
            $this->assertEquals($dgId,$dg2['id']);
        }
        
        /**
         * @test
         */
        function it_will_return_null_if_input_data_has_null_group() {
            $dict = self::$ddb->getDictionaryByName("test1");
            $inputData = array("group"=>null);
            
            $dg = self::$ddb->obtainDictionaryGroupFromInput($dict,$inputData);
             
            $this->assertEquals(null,$dg);
        }
        
        /**
         * @test
         */
        function it_will_update_an_existing_dictionary_item_and_return_it() {
            $dict = self::$ddb->getDictionaryByName("test1");
            
            $di1 = self::$ddb->storeDictionaryItem($dict,array("value"=>"X","title"=>"Item X"));
            $diId = $di1['id'];
            
            $di2 = self::$ddb->storeDictionaryItem($dict,array("value"=>"XY","title"=>"Item XY"),$diId);
            
            $this->assertEquals("XY",$di2['value']);
            $this->assertEquals("Item XY",$di2['title']);
            
            $di3 = self::$ddb->getDictionaryItem($diId);
            $this->assertEquals("XY",$di3['value']);
            $this->assertEquals("Item XY",$di3['title']);
        }
        
        /**
         * @test
         */
        function it_will_change_the_dictionary_group_to_null() {
            $dict = self::$ddb->getDictionaryByName("test1");
            $inputData = array("new_group"=>1,"group_label"=>"Just another group");
            $di1 = self::$ddb->storeDictionaryItem($dict,array("value"=>"1","title"=>"Item A","new_group"=>1,"group_label"=>"My group"));
            $diId = $di1['id'];
            
            $di2 = self::$ddb->storeDictionaryItem($dict,array("value"=>"1","title"=>"Item A","group"=>null),$diId);
            $this->assertNull($di2['dictionary_group_id']);
        }
        
         /**
         * @test
         */
        function it_will_remove_an_existing_dictionary_group() {
            $dict = self::$ddb->getDictionaryByName("test1");
            $di1 = self::$ddb->storeDictionaryItem($dict,array("value"=>"A1","title"=>"Item A1","new_group"=>1,"group_label"=>"Remove group"));
            $di2 = self::$ddb->storeDictionaryItem($dict,array("value"=>"A2","title"=>"Item A2","group"=>"remove_group"));
            $di3 = self::$ddb->storeDictionaryItem($dict,array("value"=>"A3","title"=>"Item A3","group"=>"remove_group"));
            $ditems = self::$ddb->getDictionaryItemsInGroup("test1","remove_group");
            $this->assertCount(3,$ditems);
            $dg = self::$ddb->getDictionaryGroupByName("remove_group","test1");
            
            self::$ddb->deleteDictionaryGroup($dg['id']);
            
            $ditems = self::$ddb->getDictionaryItemsInGroup("test1","remove_group");
            $this->assertCount(0,$ditems);
            
            $dg = self::$ddb->getDictionaryGroupByName("remove_group","test1");
            $this->assertFalse($dg);
        }
}

?>