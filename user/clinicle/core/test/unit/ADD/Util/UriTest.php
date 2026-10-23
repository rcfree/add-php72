<?php
require_once("ADD/UtilBaseTest.php");
class ADD_Util_UriTest extends ADD_UtilBaseTest
{
    /**
     * @test
     */
        function it_should_return_correct_uri_when_given_parameters() {
            $this->assertEquals("/dac/test?q=term",ADD_Util::uri("dac/test",array("q"=>"term")));
        }

    /**
     * @test
     */
        function it_should_return_correct_uri_when_given_no_or_empty_parameters() {
            $this->assertEquals("/dac/test",ADD_Util::uri("dac/test",array()));
            $this->assertEquals("/dac/test",ADD_Util::uri("dac/test"));
        }

}

?>