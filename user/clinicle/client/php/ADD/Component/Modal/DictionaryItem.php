<?php

class ADD_Component_Modal_DictionaryItem extends ADD_Component_Modal {
	function beforePopulate() {
		$add = ADD::sys();

		$db = ADD::db('dictionary');
		$edb = ADD::db('encounter');
		list($this->action, $this->pim) = $add -> assertParams(array('a', 'pim'));
		$this->id = $add -> getParam('id');
		$this->pageInModule = $add->getPageFromPim($this->pim);
		$dictionary = $this->pageInModule->assertSetting('dictionary');
		
		if ($this->action == 'edit' || $this->action == 'view') {
			$di = $db -> getDictionaryItem($this->id);
			$data = $di;
			if (ADD_Util::val("dictionary_group_id", $di)) {
				$dg = $db -> getDictionaryGroup(ADD_Util::val("dictionary_group_id", $di));
				$data['group'] = $dg['name'];
			} else {
				$data['group'] = "";
			}

			$data['__id'] = $this->id;
			if (isset($di['data'])) {
				$diData = ADD_Util::unserialize($di['data']);
				if ($diData)
					$data = $data + $diData;
			}
		}
		if ($this->action == 'create') {
			$data = array();
	        $topValue = $db->fetchSingleValue("select di.value from dictionary_item di left join dictionary d on d.id = di.dictionary_id where d.name=? order by CAST(di.value AS UNSIGNED) DESC limit 0,1",$dictionary);       
	        $newValue = $topValue+1;
	        $data['value']= $newValue;
			$data['group'] = "";
			$data['title'] = "";
			$data['group_label'] = "";
			$data['new_group'] = 0;
			ADD::sys()->setState('new',false);
		}
		$data['__action'] = $this->action;
		$data['new_group'] = 0;
		$this->pageInModule->setData($data);
	}
	function afterGenerate() {
		$html = ADD::web()->getContent();
		$html->addElement('input',array('type'=>'hidden','value'=>ADD::sys()->getParam(ADD_Component_Modal::ACTION_GET),'name'=>ADD_Component_Modal::ACTION_POST));
		$html->addElement('input',array('type'=>'hidden','value'=>ADD::sys()->getParam(ADD_Component_Modal::ID_GET),'name'=>ADD_Component_Modal::ID_POST));
	}
	
	function submit($values) {
		$form = $this->_page->getComponent('Form');
		$id = $form->getUncleanValue("__id");
		$pim = $form->getUncleanValue("__pim");
		$this->pageInModule = ADD::sys()->getPageFromPim($pim);
		$dictName = $this->pageInModule->assertSetting('dictionary');
		
		$ddb = ADD::db('dictionary');
		$adb = ADD::db('audit');
		
		$action = $form->getUncleanValue("__action");
		
		if ($action=="create") {
			$highestWeight = $ddb->fetchSingleValue("select di.weight from dictionary_item di left join dictionary d on d.id = di.dictionary_id where d.name=? order by CAST(di.weight AS UNSIGNED) DESC limit 0,1",$dictName);       
	        $newWeight = ($highestWeight ? intval($highestWeight) + 1 : 1);
			$values['weight']=$newWeight;
			$di = $ddb->storeDictionaryItem($dictName,$values);
			ADD::web()->addMessage(ucfirst($this->pageInModule->getSetting('single'))." successfully added");
			$adb->auditDictItem("create",$di);
			return;
		}
		if ($action=='edit' || $action=='delete') {
			$di = $ddb->getDictionaryItem($id);
		}
		if (!$di) throw new ADD_FormException("Unable to find dictionary item with ID $id");

		if ($action=="edit") {
            $newDi = $ddb->storeDictionaryItem($dictName, $values, $di);
            $adb->auditDictItem("update",$newDi,$di);
            ADD::web()->addMessage(ucfirst($this->pageInModule->getSetting('single'))." successfully updated");
		}
	}
}
