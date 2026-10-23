<?php


class ADD_Component_Encounter_ModalRepeatSection extends ADD_Component_Encounter_RepeatSection {
	/*
	public function menuSectionOpen($options) {
		$options->startElement("li");
		$options->startElement("select",array("name"=>$this->_page->getName()."-options","onchange"=>"window.location=get('".$this->_page->getName()."-options')"));
		$options->addTextElement("option","-select ".$this->_page->getSetting('label')."-",array("value"=>""));
	}
	public function menuSectionClose($options) {
		$options->endElement("select");
		$options->endElement("li");
	} 
	public function menuSectionOption($cer,$options) {
		$encLabel = $this->menuSectionLabel($cer);
		$eid = $cer->getId();
		$patientId = ADD::sys()->getPatientId();
		
		$uri = ADD_Util::uri($this->_basePath . "/" . $this->_availPages[0]->getName(),array("pid"=> $patientId,"eid"=> $eid));
		$attrs = array("value"=>htmlentities($uri));
		if(ADD::sys()->getParam('eid')==$eid) $attrs['selected']='selected';
		$options->addTextElement("option",$encLabel,$attrs);
	}

	public function menuSectionAngular($options) {
		$title = $this->_page -> getSetting('title');
		$options->startElement("div",array("ng-hide"=>"loadingGrid==true"));
		
		$options -> startElement('ul', array('class' => 'nav nav-list'));
		$options -> addTextElement("li", $title . " ({{ grid.length }})", array('class' => 'nav-header'));
		$options->startElement("li");
		$options->addTextElement("p","No current ".$this->_page -> getSetting('label')."s", array("ng-hide"=>"grid.length>0"));//,"ng-click"=>"updatePage(rowonchange"=>"window.location=get('".$this->_page->getName()."-options')"));
		$options->startElement("select",array("ng-hide"=>"grid.length==0","ng-model"=>"parentId","ng-change"=>"openPage(parentId)","name"=>$this->_page->getName()."-options","style"=>"width:12em"));//,"ng-click"=>"updatePage(rowonchange"=>"window.location=get('".$this->_page->getName()."-options')"));
		$options->addTextElement("option","-- choose --",array("value"=>""));
		$options->addTextElement("option",$this->getSetting('list_pattern'),array("ng-repeat"=>"row in grid","value"=>"{{ row.parent_encounter_id }}"));
		$options->endElement("select");
		$options->endElement("li");
		$options->endElement("div");
		$options->endElement("div");	
	} 
	public function menuSectionItems() {
		$add = ADD::sys();
        $options = new ADD_Content_Html();
		$page = $this->_page;

		$create = $page -> getPermission('create');
		
		$anyAvail = count($this->_availPages)>0 ? true : false;
		
		if (!$create && !$anyAvail)
			return array(0,$options);

		$patientId = $add -> getPatientId();

		$ppage = $page -> getParentPage();
		if(!$ppage) throw new ADD_PageNotFoundException("Parent page of '".$page->getName()."' (in module ".$page->getModule()->getName().") does not exist or was not defined");
		
		$this->_basePath = $ppage -> getName() . "/" . $page -> getName();
		
		$this->_populateFieldAndPattern();
		
		if ($anyAvail) {
			$this->menuSectionAngular($options);
		}
		else {
			if ($cerCount>0 && $create) {
					$options->addTextElement("li","You do not have permission to view ".ADD_Util::pluralize($cers,$page->getSetting('label')));
			}
		}
		return array(0,$options);
	}


    public function menuSectionOption($cer,$options) {
	}
*/
	public function menuSectionAdd($options) {
		$page = $this->_page;
		$module = $page->getModule()->getSuperModule();
		$options->startElement("ul",array("class"=>"nav nav-list","style"=>"padding-top:1em"));
		
		$availUsePages=array();
		foreach($page->getChildPages() as $childPage) {
			if(!$childPage->getPermission('create')) continue;
			if($childPage->getSetting("exclude_from_list")) continue;
			$availUsePages[$module->getName().":".$childPage->getName()]=$childPage->getSetting("title");
		}
		
		if(count($availUsePages)==0) return;
		elseif(count($availUsePages)==1) {
			$availUsePageLabels = array_values($availUsePages);
			$addField = new ADD_Field_GridAddButton(array("Title"=>"Add ".$availUsePageLabels[0],"Field"=>"add_".$page->getName(),"Settings"=>$page->getSettings(),"Options"=>$availUsePages));
		}
		else {
			$addField = new ADD_Field_GridAddSelection(array("Title"=>$page->getSetting("add_text"),"Field"=>"add_".$page->getName(),"Settings"=>$page->getSettings(),"Options"=>$availUsePages));
		}
		$options->startElement("li");
		$addField->createHtml($options,$page);
		$options->endElement("li");
	}
	/*
	public function menuSectionTitle($options,$entryCount) {
		
	}

	public function menu($options) {
		$this->_availPages = ADD::sys()->getAccessManager()->getAvailChildPages($this->_page);
		if(count($this->_availPages)==0) return null;

		$options->startElement("div",array("id"=>$this->_page->getName()."-app","ng-controller"=>"EncounterGridController","add-openpage"=>"/resphy/exam/detail","add-pim"=>"resphy:detail","add-pid"=>ADD::sys()->getPatientId()));

		$options->startElement("div",array("ng-hide"=>"loadingGrid==false"));
		$options->addHtml("Loading..&nbsp;");
		$options->addTextElement("img","",array("src"=>"/module/clinicle/client/images/ajax-loader.gif"));
		$options->endElement("div");

		parent::menu($options);
	}
	*/
}