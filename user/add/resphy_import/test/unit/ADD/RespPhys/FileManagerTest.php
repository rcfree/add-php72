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
       $this->SUT = new ADD_Util_FileManager();
    }
	
	/**
	 * @test
	 * @expectedException 
	 */
	 function it_fails_to_move_non_existing_file() {
	 	$this->SUT->move("test","test2");
	 }
}
	