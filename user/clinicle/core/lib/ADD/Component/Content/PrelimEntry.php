<?php

class ADD_Component_Content_PrelimEntry extends ADD_Component {
	protected $_componentList = array('PatientCheck','Encounter_Single_Prelim', 'Form_DB','AccessControl','OptionContainer','SaveButton','ConfirmSave','Locking','RequiredFields_Notify','HistoryOption');
	
	function title(&$title) {
        $page = $this->_page;
		$ppage = $page->getParentPage();
		$title = $page->getSetting('title') . " (".$ppage->getSetting('title').")";
	}
		
	public function menu($options) {
        $page = $this->_page;

			if ($page->getSetting('no-menu')) return null;
			list($create,$view,$edit)=$page->getPermissions(array('create','view','edit'));
            if (!$create && !$view && !$edit) return null;

                    $add = ADD::sys();

                    $label = $page->getSetting('title');
           if ($page->getExpectedPermissions()!=array('view')) {
                    if (!$create && !$edit && $view) $label.=" (view only)";
                }
            $oattrs = $add->getAccessManager()->isCurrentPage($page) ? array('class'=>'active') : array();

                    $options->startElement('li',$oattrs);
					$attrs = $page->createMenuAttrs();
					$attrs['href']=$page->createMenuUri();
		
                $options->startElement('a',$attrs,"option-".$page->getName());

                $menuIcon = $page->createMenuIcon($page);
                if ($menuIcon) {
                    if (!is_array($menuIcon)) $menuIcon = array($menuIcon);
                    foreach($menuIcon as $icon) {
                        $options->addTextElement("i","",array("class"=>$icon));
                    }
                }
                $options->addTextElement("span","",array(),"option-prefix-".$page->getName());
                    $options->addHtml($label);
                    $options->endElement('a',"option-".$page->getName()."-end");

                    $options->endElement('li');
    }
	
	function menuPath(&$menuPath) {
        $page = $this->_page;
		$menuPath=$page->getModule()->getName()."/".$page->getName();
		return;
	}
	function menuParams(&$menuParams) {
		$menuParams['pid']=ADD::sys()->getParam('pid');
	}
	function displayAccess() {
        $page = $this->_page;
		$page->setSetting('no-parent','1');
		$page->setSetting('no-locking',1);
	}
	
	function expectedPermissions(&$expPerms) {
        $page = $this->_page;
		$expPerms = $expPerms + array('create','edit','view');
	}
	
}
