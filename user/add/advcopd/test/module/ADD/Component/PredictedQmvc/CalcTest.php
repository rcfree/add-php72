<?php
set_include_path(getenv('ADD_PATH')."/test/module");

class ADD_Component_PredictedQmvc_CalcTest extends ADD_Test_EncounterBase {
	
    function testM70() {
        $predQmvc = ADD_Component_PredictedQmvc::calcPredQmvc(70,17.3,170,true);
        $this->assertEquals(20.1278,$predQmvc);
    }

    function testF70() {
        $predQmvc = ADD_Component_PredictedQmvc::calcPredQmvc(70,17.3,170,false);
        $this->assertEquals(16.7078,$predQmvc);
    }

    function testM34() {
        $predQmvc = ADD_Component_PredictedQmvc::calcPredQmvc(34,23.7,156,true);
        $this->assertEquals(37.7182,$predQmvc);
    }

    function testF34() {
        $predQmvc = ADD_Component_PredictedQmvc::calcPredQmvc(34,23.7,156,false);
        $this->assertEquals(34.2982,$predQmvc);
    }
}
?>