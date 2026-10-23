<?php
require_once("ADD/UtilBaseTest.php");
class ADD_Util_HashTest extends ADD_UtilBaseTest
{
	/**
     * @test
     */
	function hash_function_should_return_a_sha1_hash() {
		$hash = ADD_Util::hash("this is a string to hash");
		$this->assertEquals("c5f1f20124fd5ae2980ca0beefa5f2e6c055cf27",$hash);
	}
}

?>