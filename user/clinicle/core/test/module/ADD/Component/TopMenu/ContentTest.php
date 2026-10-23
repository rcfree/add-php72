<?php

$home = getenv("ADD_PATH");
set_include_path("$home/test/module");


class ADD_Component_TopMenu_ContentTest extends ADD_Test_EncounterBase {
	protected $_conf = null;
		public function setUp() {
			parent::setUp();
		}
		public function testTopMenuList() {
                    $page = $this->_page;
 
                    $ppage = $page->getParentPage();
                    $ppage->setPermission('create',true);
                    foreach($ppage->getChildPages() as $cpage) {
                        $cpage->setPermission('create',true);
                        $cpage->setPermission('edit',true);
                        $cpage->setPermission('view',true);
                    }
                    $tmenu = $page->getComponent('TopMenu');
                    $tmenu->generate($page);
                    
                    $tmContent = ADD::web()->getContent('topMenu');
                    $expOptList = array();
                    foreach($ppage->getChildPages() as $cpage) {
                        if ($cpage->getSetting('no-menu')) continue;
                        $expOptList[]=$cpage->createMenuLabel();
                    }
                    $expOpts=implode("",$expOptList);
                    $this->assertEquals($expOpts,$tmContent->toText(true));
                }
                
}

?>