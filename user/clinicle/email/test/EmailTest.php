<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Service\Email;

class EmailTest extends \PHPUnit_Framework_TestCase {
	const SUBJECT_LINE="test subject";
	const FROM_LABEL="From label";
	const FROM_EMAIL="from@email.org";
	const BODY_LINE="line1\nline2\nline3";
	
	private $bodyText;
	private $email;
	
	function setUp() {
		$this->email = new Email(self::SUBJECT_LINE,self::FROM_LABEL,self::FROM_EMAIL);
		$this->bodyText = array("line1","line2","line3");
	}
	
	/**
	 * @test
	 */
	function addToBody_should_add_a_line_to_the_body_text() {
		foreach($this->bodyText as $bodyLine) {
			$this->email->addToBody($bodyLine);
		}
		$this->assertEquals($this->email->getBodyAsText(),self::BODY_LINE);
	}
	
	/**
	 * @test
	 */
	function getFromLine_should_return_concatenated_address_and_label() {
		$this->assertEquals($this->email->getFromLine(),self::FROM_LABEL." <".self::FROM_EMAIL.">");
	}
	
	/**
	 * @test
	 */
	function setBody_will_accept_an_array_of_lines() {
		$this->email->setBody($this->bodyText);
		$this->assertEquals($this->email->getBodyAsText(),self::BODY_LINE);
	}
	
	/**
	 * @test
	 */
	function setBody_will_accept_a_body_of_text_separated_by_lf_char() {
		$this->email->setBody(self::BODY_LINE);
		$this->assertEquals($this->email->getBodyAsText(),self::BODY_LINE);
	}
	
	/**
	 * @test
	 */
	function getBodyAsText_will_include_sprintf_arguments_in_output() {
		$this->email->setBody("user:%s, password:%s");
		$this->email->setPrintfArgs(array("rob","pa55word"));
		$this->assertEquals($this->email->getBodyAsText(),"user:rob, password:pa55word");
	}
	
	/**
	 * @test
	 */
	function setLineFeed_allows_the_lf_char_to_be_changed() {
		$this->email->setLineFeed("\r");
		foreach($this->bodyText as $bodyLine) {
			$this->email->addToBody($bodyLine);
		}
		$this->assertEquals($this->email->getBodyAsText(),"line1\rline2\rline3");
	}
	
		
}