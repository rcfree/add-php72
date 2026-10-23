<?php

class Database_BaseTest extends PHPUnit_Framework_TestCase {
    
    function testGetVersion() {
        $version = ADD::db()->getVersion();
        $this->assertEquals("2.0.0",$version);
    }
    
        
}
