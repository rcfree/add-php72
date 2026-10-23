<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/04/2014
 * Time: 12:56
 */

abstract class ADD_BaseTestFixture extends PHPUnit_Framework_TestCase {
    public $system;
    public $initialiser;
    public $web;
	public $dbm;
    public function initSystemAccess($webAlso=false,$dbmMocks=null)
    {
        $this->initialiser = $this->getMockBuilder("ADD_InitialiserInterface")->disableOriginalConstructor()->getMock();
        $this->system = $this->getMockBuilder("ADD_System")->disableOriginalConstructor()->getMock();
        $this->initialiser->expects($this->once())->method("getSystem")->will($this->returnValue($this->system));
        if ($webAlso) {
            $this->web = $this->getMockBuilder("ADD_Web")->disableOriginalConstructor()->getMock();
            $this->initialiser->expects($this->once())->method("getWeb")->will($this->returnValue($this->web));
        }
		
		if($dbmMocks) {
			$dbm['BASE']=$this->getMock('ADD_Database');
			foreach($dbmMocks as $availableDbType) {
				$dbm[$availableDbType]=$this->getMock('ADD_Database_'.ADD_Util::convertToPascal($availableDbType));
			}
			$dbmMap = array_map(function($key,$value) { return array($key,$value); },array_keys($dbm),$dbm);

			$dbmMock = $this->getMock('ADD_DatabaseManager',array('get'));
			$dbmMock->expects($this->any())->method("get")->will($this->returnValueMap($dbmMap));
			$this->dbm = $dbmMock;
			$this->initialiser->expects($this->once())->method("getDatabaseManager")->will($this->returnValue($this->dbm));
		}
        ADD::init($this->initialiser);
    }
	
	public function initDBManager() {
		
		
		
	}
}
 