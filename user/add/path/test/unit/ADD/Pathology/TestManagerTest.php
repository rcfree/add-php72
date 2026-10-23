<?php
set_include_path(getenv('ADD_MODULE_PATH')."/lib:".get_include_path());
require_once("ADD/Pathology/TestManager.php");
require_once("ADD/Pathology/DataSourceInterface.php");
require_once("ADD/Data/PathologyService.php");
require_once("ADD/Pathology/RecordSet.php");

class ADD_Pathology_TestManagerTest extends ADD_BaseTestFixture {
    private $SUT;
    private $dataSource;
	private $testTypes;
	private $testItem = array("UNIQUE_TEST_ID"=>self::UNIQUEID);
    	
	
    public function setUp() {
    	$this->source = $this->getMock("ADD_Pathology_DataSourceImpl");
		$this->parent = $this->getMock("ADD_EncounterRecord");
		$this->recordSet = $this->getMockBuilder("ADD_Pathology_RecordSet")->disableOriginalConstructor()->getMock();
		$this->service = $this->getMockBuilder("ADD_Data_PathologyService")->disableOriginalConstructor()->getMock();
		$this->pathRecord = $this->getMock("ADD_Pathology_Record_Micro");
		$this->SUT = new ADD_Pathology_TestManager($this->source,$this->service, $this->recordSet);
    }

    /**
     * @test
     */
    public function updateTests_will_deal_with_no_records() {
		$this->source->expects($this->once())->method("nextTestItem")->will($this->returnValue(null));
    	$this->service->expects($this->never())->method("retrieveRecord");
    	$this->SUT->updateTests();
	}
	
	const UNIQUEID="24545645645564";
	
	/**
     * #@test
     */
    public function updateTests_will_instantiate_new_record_if_next_test_item_cannot_be_obtained_from_query() {
    	$this->source->expects($this->at(0))->method("nextTestItem")->will($this->returnValue($this->testItem));
    	$this->service->expects($this->once())->method("retrieveRecord")->with($this->testItem)->will($this->returnValue(null));
    	$this->service->expects($this->once())->method("newRecord")->with($this->testItem)->will($this->returnValue($this->pathRecord));
    	$this->recordSet->expects($this->once())->method("addRecord")->with(self::UNIQUEID,$this->pathRecord);
    	$this->pathRecord->expects($this->once())->method("updateFromRowData")->with($this->testItem);
    	$this->recordSet->expects($this->once())->method("persistRecords");
		
    	$this->SUT->updateTests();
	}
	
	
	/**
     * #@test
     */
    public function updateTests_will_persist_records_in_the_cache_using_the_service_if_the_record_has_changed() {
    	//$this->source->expects($this->at(0))->method("nextTestItem")->will($this->returnValue($this->testItem));
		
		//$this->recordSet->expects($this->once())->method("getRecords")->will($this->returnValue(array($this->pathRecord)));
		//$this->pathRecord->expects($this->once())->method("hasChanged")->will($this->returnValue(true));
		$this->recordSet->expects($this->once())->method("persistRecords");
		//$this->service->expects($this->once())->method("retrieveRecord")->with(self::UNIQUEID)->will($this->returnValue($this->pathRecord));
    	
		//$this->service->expects($this->once())->method("persistRecord")->with($this->pathRecord);
    	
    	$this->SUT->updateTests();
	}
	
	/**
   
    public function updateTests_will_not_persist_records_in_the_cache_using_the_service_if_the_record_has_not_changed() {
		//$this->source->expects($this->at(0))->method("nextTestItem")->will($this->returnValue($this->testItem));
		//$this->service->expects($this->once())->method("newRecord")->with($this->testItem)->will($this->returnValue($this->pathRecord));
    	$this->recordSet->expects($this->once())->method("getRecords")->will($this->returnValue(array($this->pathRecord)));
		$this->pathRecord->expects($this->once())->method("hasChanged")->will($this->returnValue(false));
		$this->recordSet->expects($this->once())->method("persistRecords");
		
		$this->service->expects($this->never())->method("persistRecord");
    	
    	$this->SUT->updateTests();
	}
	 * 
	 */
	
}
class ADD_Pathology_DataSourceImpl implements ADD_Pathology_DataSourceInterface {
	public function nextTestItem() {
	}
}
 