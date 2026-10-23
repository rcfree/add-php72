<?php

class Component_EncounterEntryTest extends PHPUnit_Framework_TestCase {
    static $modName=null;
    static $module=null;
    static $pageName=null;
    static $page = null;
    static $pat = null;
    static $pid = null;

    public static function setUpBeforeClass() {
        $testInfo = getenv('ADD_PAGE_TEST');
        if (!$testInfo) throw Exception("ADD_PAGE_TEST not set");

        //list($modName,$pageName)=explode(":",$testInfo);
        $modName = "cap";
        $pageName="episode";

        $add = ADD::sys();
        $add->initModules();

        self::$modName=$modName;
        self::$module=ADD::sys()->getModule($modName);
        self::$pageName=$pageName;
        self::$page = self::$module->getPage($pageName);
        $pdb = ADD::db('patient');
        $values = array("identifier"=>"Z99999999","first_name"=>"RICHARD","last_name"=>"LIONHEART");
        list($pat,$addr,$gp) = $pdb->createPatientDemographics($values);
        self::$pat=$pat;
        self::$pid=$pat['id'];
        $add->setParam('pid',self::$pid);
        $add->setPatientId(self::$pid);

        $em = ADD::sys()->getEncounterManager();
        $tpages = self::$module->getTopPages();
        $ter = $tpages[0]->getEncounterRecord();
        $ter->setNew(true);
        $ter->store();

        $ppage = self::$page->getParent();
        $cer = $em->newChild($ter,$ppage->getSetting('encounter'));
        $cer->store();

    }

    public function testContentDisplayForNewRecordIfRepeatSection() {
        $ppage = self::$page->getParent();
        if (get_class($ppage->getComponent('Encounter'))!="ADD_Component_Encounter_RepeatSection") $this->markTestSkipped("Not relevant - parent is RepeatSection");
        $web = ADD::web();
        $urlPath = self::$page->createMenuPath();
        $web->createOutput(array("method" => "GET", "path" => $urlPath, "values" => array()));
        $web->createOutput(array("method" => "GET", "path" => $urlPath, "values" => array()));

        $this->assertHasNoWarnings();
        $this->assertHasNoErrors();
    }

    public function testFormSubmitWithExampleData() {
        $config = new ADD_Config();
        $confFile = self::$module->getPath()."/test/config/".self::$pageName.".ini";
        if (!$config->loadFile($confFile)) throw new Exception("No config file '$confFile' found");

        ADD::sys()->setParam('t',ADD_Util::token());

        $egData = $config->getSection('FormSubmitWithExampleData')->getConfig();
        $urlPath = self::$page->createMenuPath();
        ADD::web()->createOutput(array("method" => "POST", "path" => $urlPath, "values" => $egData));

        $this->assertHasNoWarnings();
        $this->assertHasNoErrors();
    }

    public static function tearDownAfterClass() {
        ADD::db('encounter')->purgePatientEncounters(self::$pat);
        ADD::db('patient')->purgePatient(self::$pat);
    }

    public function assertHasNoWarnings() {
        //ADD::log()->info("warn:".json_encode(ADD::web()->getWarnings()));
        $warnings = ADD::web()->getWarnings();
        if (is_array($warnings) && count($warnings)>0) throw new Exception("No warnings were expected:".print_r($warnings,1));

    }
    public function assertHasNoErrors() {
        //ADD::log()->info("warn:".json_encode(ADD::web()->getWarnings()));
        $warnings = ADD::web()->getErrors();
        if (is_array($warnings) && count($warnings)>0) throw new Exception("No errors were expected:".print_r($warnings,1));

    }
} 