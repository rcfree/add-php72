<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
set_include_path(getenv('ADD_MODULE_PATH')."/lib:".get_include_path());
;
;
;
;
;
;
;
class ADD_Query_ScratchTest extends PHPUnit_Framework_TestCase {
    function setUp() {
		$init = new ADD_Initialiser_Standard(getenv('ADD_PATH'),'local-stable');
        ADD::init($init); 
    }
	
    /**
	 * @test
	 */
	function get_data_from_system() {
		$setup = new ADD_ModuleSetup(ADD::sys());
		$setup->loadSingleModuleWithDependencies("query", ADD::sys()->getSourceModulePath(),"");
		ADD::sys()->initModules();
		$run = new ADD_Query_Run();
		$run->addFilter("Consented",array("consent"=>ADD_Query_Filter_Consented::RECRUIT));
		$run->addFilter("ObsOption",array("field"=>"current_status","value"=>"Available for recruitment","op"=>"equal","encounter"=>"res-contact-details"));
		$run->addValue("PatientData",array("fields"=>array("identifier","first_name","last_name","nhs_no","date_of_birth","deceased")));
		
		$run->addValue("ObsData",array("encounter"=>"res-contact-details","fields"=>array("current_status")));
		$run->addValue("ObsData",array("encounter"=>"dac-base-details"));
		
		
		$encounterLookup = new ADD_Export_EncounterLookup(ADD::sys()->getModules());
		$result = new ADD_Query_Result($encounterLookup);
		$output = new ADD_Query_Output_Table($run,$result);
		$output->setRowPerPatient(true);
		
		$run->run($result,$output);
	}
}