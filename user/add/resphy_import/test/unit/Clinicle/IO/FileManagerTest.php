<?php
namespace Clinicle\Test\IO;
use Clinicle\IO\FileManager;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class FileManagerTest extends \PHPUnit_Framework_TestCase {
    private $SUT;
    const IDENTIFIER="S12345678";
    const IDENTIFIER2="S12345679";
	
    function setUp() {
       $this->SUT = new FileManager();
    }
	
	/**
	 * @test
	 * @expectedException Clinicle\IO\Exception\SystemCommandException
	 */
	 function it_fails_to_move_non_existing_file() {
	 	$this->SUT->move("test-notexist","test2-notexist");
	 }
	 
	 /**
	 * @test
	 */
	 function it_moves_an_existing_file() {
	 	$existFileSrc = __DIR__."/../../../data/fm/test-existfile";
		$existFileTgt = __DIR__."/../../../data/fm/test-existfile2";
	 	
	 	if(file_exists($existFileSrc)) {
	 		system("rm $existFileSrc");
		}
		if(file_exists($existFileTgt)) {
	 		system("rm $existFileTgt");
		}
	 	$this->assertFalse(file_exists($existFileTgt));
	 	system("touch $existFileSrc");
	 	$this->SUT->move($existFileSrc, $existFileTgt);
		
		$this->assertTrue(file_exists($existFileTgt));
		system("rm $existFileTgt");
	 }
}
	