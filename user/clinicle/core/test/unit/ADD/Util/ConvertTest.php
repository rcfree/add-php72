<?php
require_once("ADD/UtilBaseTest.php");
class ADD_Util_ConvertTest extends ADD_UtilBaseTest
{
    
    /**
     * @test
     */
    function it_should_convert_multiple_words_in_pascal_to_perlish() {
		$this->assertEquals("radio_buttons_group",ADD_Util::convertToPerlish("RadioButtonsGroup"));
	}

    /**
     * @test
     */
	function it_should_convert_single_word_in_pascal_to_perlish() {
		$this->assertEquals("radio",ADD_Util::convertToPerlish("Radio"));
	}

    /**
     * @test
     */
	function it_should_recognise_perlish_and_not_reconvert_it() {
		$this->assertEquals("radio_buttons",ADD_Util::convertToPerlish("radio_buttons"));
	}
}

?>