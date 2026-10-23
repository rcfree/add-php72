<?php
//require_once("ADD/Initialiser.php");
require_once("ADD/Component/Encounter/Domain/ManualEnrol.php");
class ADD_Component_Encounter_Domain_ManualEnrolTest extends PHPUnit_Framework_TestCase {
    /**
     * @expectedException ADD_ComponentException
     * @expectedExceptionMessage It appears that you may have tried to add this patient to the '' domain multiple times. They have already been added to this domain.
     */
    public function testGenerateFailsWhenTokenRegistered() {
        $er = $this->getMock("EncounterRecord",array("exists","store"));
        $page = $this->getMock("Page",array("registerToken","getSetting","getSettingList"));
        $comp = new ADD_Component_Encounter_Domain_ManualEnrol();
        $comp->setEncounterRecord($er);
        $comp->generate($page);
    }
    
    public function testGenerateDoesNotStoreWhenErrors() {
        $er = $this->getMock("EncounterRecord",array("exists"));
        $page = $this->getMock("Page",array("registerToken","getErrors"));
        $page->expects($this->once())->method("registerToken")->will($this->returnValue(true));
        $page->expects($this->once())->method("getErrors")->will($this->returnValue(true));
        $er->expects($this->once())->method("exists")->will($this->returnValue(TRUE));
        $comp = new ADD_Component_Encounter_Domain_ManualEnrol();
        $comp->setEncounterRecord($er);
        $comp->generate($page);
    }
    public function testGenerateDoesNotStoreWhenAlreadyExists() {
        $er = $this->getMock("EncounterRecord",array("exists"));
        $page = $this->getMock("Page",array("registerToken","getErrors","getSetting","addMessage","redirectUsingSetting"));
        $page->expects($this->once())->method("registerToken")->will($this->returnValue(true));
        $page->expects($this->once())->method("getErrors")->will($this->returnValue(null));
        $page->expects($this->once())->method("addMessage");
        $page->expects($this->once())->method("redirectUsingSetting");
        
        $er->expects($this->once())->method("exists")->will($this->returnValue(TRUE));
        $comp = new ADD_Component_Encounter_Domain_ManualEnrol();
        $comp->setEncounterRecord($er);
        $comp->generate($page);
    }
    
    public function testGenerateStoresWhenDoesNotExist() {
        $er = $this->getMock("EncounterRecord",array("exists","store"));
        $page = $this->getMock("Page",array("registerToken","getErrors","getSetting","addMessage","redirectUsingSetting","getSettingList"));
        $page->expects($this->once())->method("registerToken")->will($this->returnValue(true));
        $page->expects($this->once())->method("getErrors")->will($this->returnValue(null));
        $page->expects($this->once())->method("addMessage");
        $page->expects($this->once())->method("redirectUsingSetting");
        $page->expects($this->once())->method("getSettingList");
        
        $er->expects($this->once())->method("exists")->will($this->returnValue(false));
        $er->expects($this->once())->method("store");
        
        $comp = new ADD_Component_Encounter_Domain_ManualEnrol();
        $comp->setEncounterRecord($er);
        $comp->generate($page);
    }
    
    public function testGenerateStoresCreatesSubEncountersForChildList() {
        $mod = $this->getMock("Module",array("getPage"));
        $page2 = $this->getMock("Page",array("getSetting"));
        $page2->expects($this->exactly(3))->method("getSetting");
        $mod->expects($this->exactly(3))->method("getPage")->will($this->returnValue($page2));
        
        $page = $this->getMock("Page",array("registerToken","getErrors","getSetting","addMessage","redirectUsingSetting","getSettingList","getModule"));
        $page->expects($this->once())->method("registerToken")->will($this->returnValue(true));
        $page->expects($this->once())->method("getErrors")->will($this->returnValue(null));
        $page->expects($this->once())->method("addMessage");
        $page->expects($this->once())->method("redirectUsingSetting");
        $page->expects($this->once())->method("getSettingList")->will($this->returnValue(array("test1","test2","test3")));
        $page->expects($this->once())->method("getModule")->will($this->returnValue($mod));
        
        $cer = $this->getMock("EncounterRecord",array("store"));
        $cer->expects($this->exactly(3))->method("store");
        
        $er = $this->getMock("EncounterRecord",array("exists","store","newChild"));
        $er->expects($this->once())->method("exists")->will($this->returnValue(false));
        $er->expects($this->once())->method("store");
        $er->expects($this->exactly(3))->method("newChild")->will($this->returnValue($cer));
        $comp = new ADD_Component_Encounter_Domain_ManualEnrol();
        $comp->setEncounterRecord($er);
        $comp->generate($page);
    }
    
    public function testOptionsGeneration() {
        $mod = $this->getMock("Module",array("getPage"));
        $page2 = $this->getMock("Page",array("getSetting"));
        $page2->expects($this->exactly(3))->method("getSetting");
        $mod->expects($this->exactly(3))->method("getPage")->will($this->returnValue($page2));
        
        $page = $this->getMock("Page",array("registerToken","getErrors","getSetting","addMessage","redirectUsingSetting","getSettingList","getModule"));
        $page->expects($this->once())->method("registerToken")->will($this->returnValue(true));
        $page->expects($this->once())->method("getErrors")->will($this->returnValue(null));
        $page->expects($this->once())->method("addMessage");
        $page->expects($this->once())->method("redirectUsingSetting");
        $page->expects($this->once())->method("getSettingList")->will($this->returnValue(array("test1","test2","test3")));
        $page->expects($this->once())->method("getModule")->will($this->returnValue($mod));
        
        $cer = $this->getMock("EncounterRecord",array("store"));
        $cer->expects($this->exactly(3))->method("store");
        
        $er = $this->getMock("EncounterRecord",array("exists","store","newChild"));
        $er->expects($this->once())->method("exists")->will($this->returnValue(false));
        $er->expects($this->once())->method("store");
        $er->expects($this->exactly(3))->method("newChild")->will($this->returnValue($cer));
        $comp = new ADD_Component_Encounter_Domain_ManualEnrol();
        $comp->setEncounterRecord($er);
        $comp->generate($page);
    }
    
}


?>
