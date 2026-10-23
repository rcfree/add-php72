<?php
class ADD_Component_Content_StudyListEntry extends ADD_Component {
	protected $_componentList = array('Form_File','AccessControl');
	function title(&$title) {
		$title = $this->_page->getSetting('title');
	}
	function beforeSubmit(&$values) {
		$add = ADD::sys();	
		$add->setState('action',ADD_Util::val(ADD_Component::ACTION,$values));
		$add->setState('id',ADD_Util::val(ADD_Component::ID,$values));
	}
	function submit($values)  {
		$add = ADD::sys();
                $page = $this->_page;
		$form = $page->getComponent('Form');
		
		$db = ADD::db('dictionary');
		$adb = ADD::db('audit');
		
		$action = $add->hasState('action');
		$id = $add->hasState('id');
		
		$di = null;
		$dictName = $page->assertSetting('DictionaryEntry.dictionary');
                
		if ($action=="removegroup") {
			$dg = $db->deleteDictionaryGroup($id);
			$adb->auditDictGroup("delete",$dg);
			$page->addMessage("Successfully removed dictionary group '".$dg['name']."'<br/>Any items that were in the group are displayed in the 'No group' section.");  
			return array('redirect'=>ADD_Util::uri($page->createMenuPath(),$add->getParams(),true));
		}
		if ($action=="create") {
			$di = $db->storeDictionaryItem($dictName,$values);
			$page->addMessage(ucfirst($page->getSetting('single'))." item successfully added");
			$add->setState('new',true);
			$adb->auditDictItem("create",$di);
		}
		if ($action=='edit' || $action=='delete') {
			$di = $db->getDictionaryItem($id);
		}
		if (!$di) throw new ADD_FormException("Unable to find dictionary item with ID $id");

		if ($action=="edit") {
            $newDi = $db->storeDictionaryItem($dictName, $values, $di);
            $adb->auditDictItem("update",$newDi,$di);
            $page->addMessage(ucfirst($page->getSetting('single'))." item successfully updated");
		}
		return array('redirect'=>$page->createMenuUri());
	}
	
	function afterGenerate() {
        $page = $this->_page;
		$page->html()->addElement('input',array('type'=>'hidden','value'=>'','name'=>ADD_Component::ACTION));
		$page->html()->addElement('input',array('type'=>'hidden','value'=>'','name'=>ADD_Component::ID));
	}
	function expectedPermissions(&$expPerms) {
		$expPerms = $expPerms + array('create','edit','view','delete');
	}
	
	function menuPath(&$menuPath) {
                $page = $this->_page;
		$subPath = $page->getName();
		$parentPage = $page->getParentPage();
        $topPages = $page->getModule()->getSuperModule()->getTopPages();
        if (count($topPages)==0) throw new ADD_ComponentException("No 'top' pages defined (e.g. without parent) defined in module");
		$topPath = $topPages[0]->getName();
		
		if (!$parentPage) {
			$menuPath = ADD_Util::makePath(array($topPath,$subPath));
			return;
		}
		$parentPath = $parentPage->getName();
		
		if (!$parentPage) return ADD_Util::makePath(array($topPath,$subPath));
		$menuPath = ADD_Util::makePath(array($topPath,$parentPath,$subPath));
	}
	function menuParams(&$menuParams) {
		$menuParams = array();
	}

}