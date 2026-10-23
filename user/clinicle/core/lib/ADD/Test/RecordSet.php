<?php
class ADD_Test_RecordSet {

	private $_module;
	private $_testCase;
	private $_topRecord;
	private $_sectionRecords=array();
	private $_sectionArgs=array();
	private $_dataRecords=array();
	private $_dataArgs=array();
	private $_id = 0;
	
	public function __construct(ADD_Module $module,PHPUnit_Framework_TestCase $testCase) {
		$this->_module = $module;
		$this->_testCase = $testCase;
		//$this->_mockObjectGenerator = new PHPUnit_Framework_MockObject_Generator();
	}

	public function expectTopDomain($exists,$args=array()) {
		$this->_topRecord = $this->_testCase->getMock("ADD_EncounterRecord",array("getObsValues","getObsValue","getObsText","setObsValues","setObsValue"));
		$id = ADD_Util::val('id',$args,$this->_id++);
		
		if($exists!==null) {
			$this->_topRecord->expects($this->_testCase->atLeastOnce())->method("exists")->will($this->_testCase->returnValue($exists));
		}
		if($exists===true) {
			$this->_topRecord->expects($this->_testCase->any())->method("getId")->will($this->_testCase->returnValue($id));
		}
		$args['id']=$id;
		return $this->_topRecord;
	}
	public function incrementId() {
		$this->_id++;
		return $this->_id;
	}
	public function expectSection($sectionName,$args) {
		$sectionRecord = $this->_testCase->getMock("ADD_EncounterRecord");
		$exists = ADD_Util::val('exists',$args,null);
		$id = ADD_Util::val('id',$args,$this->incrementId());
		
		if($exists!==null) {
			$sectionRecord->expects($this->_testCase->atLeastOnce())->method("exists")->will($this->_testCase->returnValue($exists));
		}
		$sectionRecord->expects($this->_testCase->any())->method("getId")->will($this->_testCase->returnValue($id));
		
		$args['id']=$id;
		$this->_initArrayIfEmpty($this->_sectionRecords,$sectionName);
		$this->_initArrayIfEmpty($this->_sectionArgs,$sectionName);
		
		$this->_sectionRecords[$sectionName][$id]=$sectionRecord;
		$this->_sectionArgs[$sectionName][$id]=$args;
		return $sectionRecord;
	}
	
	public function expectData($pageName,$args) {
		$record = $this->_testCase->getMockBuilder("ADD_EncounterRecord")
				->setMethods(array("store","retrieve","getObsFields","exists","getId"))
				->getMock();
		$page = $this->_module->getPage($pageName);
		
		list($parentId,$exists,$storeWithData,$store,$startData) = ADD_Util::vals(array('parent','exists','storeWithData','store','startData'),$args);
		if(!$parentId) throw new Exception("No 'parent'  provided to data record '$pageName':".json_encode($args));
		$id = ADD_Util::val('id',$args,$this->incrementId());
		
		if($exists!==null) {
			$record->expects($this->_testCase->atLeastOnce())->method("exists")->will($this->_testCase->returnValue($exists));
		}
		
		$record->expects($this->_testCase->any())->method("getId")->will($this->_testCase->returnValue($id));
		$form = $page->getComponent('Form');
		$form->setup();
		$obsFields = array();
		foreach($form->getFields() as $field) {
			$obsFields[$field->getName()]=true;
		}
		$record->expects($this->_testCase->any())->method("getObsFields")->will($this->_testCase->returnValue($obsFields));
		if($startData) {
			$record->setObsValues($startData);
		}
		if($storeWithData!==null) {
			$record->expects($this->_testCase->at($storeWithData))->method("store");
		}
		
		$args['id']=$id;
		$args['name']=$pageName;
		
		$this->_initArrayIfEmpty($this->_dataRecords,$parentId);
		$this->_initArrayIfEmpty($this->_dataArgs,$parentId);
		$this->_initArrayIfEmpty($this->_dataRecords[$parentId],$id);
		$this->_initArrayIfEmpty($this->_dataArgs[$parentId],$id);
		
		$this->_dataRecords[$parentId][$id]=&$record;
		$this->_dataArgs[$parentId][$id]=$args;
		return $record;
	}
	
	public function populateEncounterManager($encounterManager) {
		$topPages = $this->_module->getTopPages();
		$encounterManager->setExpected($topPages[0],"record",$this->_topRecord);
		
		foreach($this->_sectionRecords as $sectionName => $recordsForSection) {
			foreach($recordsForSection as $sectionId => $recordForSection) {
				$sectionArgs = $this->_sectionArgs[$sectionName][$sectionId];
				$sectionPage = $this->_module->getPage($sectionName);
				
				$newSection = ADD_Util::val('new',$sectionArgs,false);
				$oldSection = ADD_Util::val('old',$sectionArgs,false);
				$byId = ADD_Util::val('byId',$sectionArgs,false);
				if($byId) {
					$encounterManager->setExpected(ADD_Util::val('id',$sectionArgs),"recordById",$recordForSection);
				}
				if($newSection) {
					$encounterManager->setExpected($sectionPage,"newChild",$recordForSection,$this->_topRecord);
				}
				if($oldSection) {
					$encounterManager->setExpected($sectionPage,"child",$recordForSection,$this->_topRecord);
				}	
				
				if(!isset($this->_dataRecords[$sectionId])) continue;
				$sectionDataRecords = $this->_dataRecords[$sectionId];
				foreach($sectionDataRecords as $recordId => $dataRecord) {
					$dataArgs = $this->_dataArgs[$sectionId][$recordId];
					$dataPage = $this->_module->getPage($dataArgs['name']);
					$newRecord = ADD_Util::val('new',$dataArgs,false);
					$oldRecord = ADD_Util::val('old',$dataArgs,false);
					$id = isset($sectionArgs['repeat']) ? $sectionId : null;
					if($newRecord) {
						$encounterManager->setExpected($dataPage, "newChild",$dataRecord,$recordForSection,$id);
					}
					if($oldRecord) {
						$encounterManager->setExpected($dataPage, "child",$dataRecord,$recordForSection,$id);
					}
				}
			}
		}
	}
	
	private function _initArrayIfEmpty(&$array,$key) {
		if (isset($array[$key])) return;
		$array[$key]=array();
	}
/*
	  array("exists"=>true));
	  $rs->expectSection("pr-ref",array("id"=>123,"new"=>true));
	  $rs->expectData("pr-ref",array("parent"=>123,"data"=>array("treatment_id"=>321),"new"=>true));
	  $rs->expectSection("pr-prog",array("id"=>321,"new"=>true));
	  $rs->expectData("pr-prog-subject",array("parent"=>321,"exists"=>true,"data"=>array("referral_id"=>123)));
	 */
}