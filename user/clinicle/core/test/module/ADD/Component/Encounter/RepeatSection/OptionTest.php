<?php

$home = getenv("ADD_PATH");
set_include_path(get_include_path().":$home/test/module");


class ADD_Component_Encounter_RepeatSection_OptionTest extends ADD_Test_EncounterBase {
 public function testCorrectOptionsIfSectionHasDifferentDates() {
        $page = $this->_page;
        $ppage = $page->getParentPage();
        $pencType = $ppage->getSetting('encounter');
        $encType = $page->getSetting('encounter');
        
        $em = ADD::sys()->getEncounterManager();
        $per = $this->getMock('ADD_EncounterRecord');
        $per->expects($this->any())->method("exists")->will($this->returnValue(true));
        $er1 = $this->getMock('ADD_EncounterRecord');
        $er1->expects($this->any())->method("exists")->will($this->returnValue(true));
        $er2 = $this->getERMock();
        $er2->expects($this->any())->method("exists")->will($this->returnValue(true));
        
        $cer = $this->getERMock();
        $cer2 = $this->getERMock();
            
        $dates = array("2013-03-12 13:04:25" => "12/03/2013","2013-07-23 03:02:34" => "23/07/2013");
        $dateVals = array_values($dates);
        $dateKeys = array_keys($dates);
        $em->setExpected($pencType, "record", $per);
            
        if ($page->getSetting('show_field')) {
            list($pageName,$fieldName) = explode(".",$page->getSetting('show_field'));
            $cer->expects($this->exactly(2))->method("getObsText")->will($this->returnValue($dateVals[0]));
            $cer2->expects($this->exactly(2))->method("getObsText")->will($this->returnValue($dateVals[1]));
            $cencType = $this->_module->getPage($pageName)->getSetting('encounter');
            $em->setExpected($cencType,"child",$cer,$er1);
			$em->setExpected($cencType,"child",$cer2,$er2);
        }
        
        $em->setExpected($encType, "children", array($er1, $er2));
        
        $cpageCount = 2;
        foreach($page->getChildPages() as $cpage) {
            if ($cpage->getSetting('no-menu')) continue;
            if (!$cpage->hasComponent('Encounter')) continue;
            if ($cpage->hasComponent('Content_EncounterSummary')) $cpageCount++;
            $cpageCount++;
        }
        
        ADD::sys()->getAccessManager()->setCurrentPage($this->_page);
        
        foreach ($this->_module->getPages() as $mpage) {
            $mpage->setPermission('view', true);
        }

        $opts = $page->createMenu();

        $expOpts = $page->getSetting('title');
        foreach ($page->getChildPages() as $cpage) {
            $expOpts.=$cpage->createMenu()->toText();
        }
        $opts = $page->createMenu();
        $expOpts = $page->getSetting('title') . " (2)";
        for ($i = 1; $i <= 2; $i++) {
            $expOpts.="&raquo;&nbsp;".$page->getSetting('label') . " $i";
			if ($page->getSetting('show_field')) {
				list($showPage,$showField) = explode(".",$page->getSetting('show_field'));
                $expOpts.=" (".$dateVals[$i-1].")";
			}
        }
        $this->assertEquals($expOpts, $opts->toText());
        
    }

    public function testDisplayAddOptionIfSectionNotExistingAndCreatePermission() {
        $page = $this->_page;
        $em = ADD::sys()->getEncounterManager();
        $per = $this->getMock('ADD_EncounterRecord');
        $per->expects($this->any())->method("exists")->will($this->returnValue(false));
        $em->setExpected(0, "record", $per);
        ADD::sys()->getAccessManager()->setCurrentPage($page);
        foreach ($this->_module->getPages() as $mpage) {
            $mpage->setPermission('create', true);
        }

        $opts = $page->createMenu();
        $this->assertEquals($page->getSetting('title') . " (0)&nbsp;New " . $page->getSetting('label') . "..", $opts->toText());
    }

    public function testDisplayAvailableIfSectionsAddedAndCreatePermission() {
        $page = $this->_page;
        $em = ADD::sys()->getEncounterManager();
        $per = $this->getMock('ADD_EncounterRecord');
        $per->expects($this->any())->method("exists")->will($this->returnValue(true));
        $er = $this->getMock('ADD_EncounterRecord');
        
        $em->setExpected($page->getParentPage()->getSetting('encounter'), "record", $per);
        $em->setExpected($page->getSetting('encounter'), "children", array($er, $er));

        ADD::sys()->getAccessManager()->setCurrentPage($this->_page);
        foreach ($this->_module->getPages() as $mpage) {
            $mpage->setPermission('create', true);
        }

        $opts = $page->createMenu();
        $expOpts = $page->getSetting('title') . " (2)";
        for ($i = 1; $i <= 2; $i++) {
            $expOpts.="&raquo;&nbsp;".$page->getSetting('label') . " $i";
            if($page->getSetting('show_field')) {
				list($showPage,$showField) = explode(".",$page->getSetting('show_field'));
                $expOpts.=" (".lcfirst(ADD_Util::nameToLabel($showField))." not specified)";
            }
        }
        $expOpts.="&nbsp;New " . $page->getSetting('label') . "..";

        $this->assertEquals($expOpts, $opts->toText());
    }

    public function testDisplayViewableIfSectionExistingAndViewPermission() {
        $page = $this->_page;
        $em = ADD::sys()->getEncounterManager();
        $per = $this->getMock('ADD_EncounterRecord');
        $per->expects($this->any())->method("exists")->will($this->returnValue(true));
        $er1 = $this->getMock('ADD_EncounterRecord');
        $er1->expects($this->any())->method("exists")->will($this->returnValue(true));
        $er2 = $this->getMock('ADD_EncounterRecord');
        $er2->expects($this->any())->method("exists")->will($this->returnValue(true));
        
        
        $pencType = $page->getParentPage()->getSetting('encounter');
        $encType = $page->getSetting('encounter');
        $em->setExpected($pencType, "record", $per);
        $em->setExpected($encType, "children", array($er1, $er2));
        //$em->setExpected($encType, "child", $er1);
        
        $dates = array("2012-02-12 16:05:03" => "12/02/2012","2013-06-03 12:00:43" => "03/06/2013");
        $dateVals = array_values($dates);
        $dateKeys = array_keys($dates);
            
        ADD::sys()->getAccessManager()->setCurrentPage($this->_page);

        foreach ($this->_module->getPages() as $mpage) {
            $mpage->setPermission('view', true);
        }
        
        $obsOpts = $page->createMenu();
        
        
        $expOpts = $page->getSetting('title') . " (2)";
        
        for ($i = 1; $i <= 2; $i++) {
            $expOpts.="&raquo;&nbsp;".$page->getSetting('label') . " $i";
            if ($page->getSetting('show_field')) {
				list($showPage,$showField) = explode(".",$page->getSetting('show_field'));
                $expOpts.=" (".lcfirst(ADD_Util::nameToLabel($showField))." not specified)";
			}
        }
        $this->assertEquals($expOpts, $obsOpts->toText());
    }
    
   
    public function testDisplayAddableLinkMatchesPageURI() {
        $page = $this->_page;
        list($per, $er, $cer) = $this->setupEncounterRecord();
        $this->givenMockPatientIsSelected();
        $per->expects($this->any())->method("exists")->will($this->returnValue(false));

        foreach ($this->_module->getPages() as $mpage) {
            $mpage->setPermission('create', true);
        }
        ADD::sys()->getAccessManager()->setCurrentPage($page);
        $opts = $page->createMenu();
        $pos = strpos($opts->toHtml(), $page->createMenuPath());
        $this->assertGreaterThan(0, $pos);
    }

}

?>
