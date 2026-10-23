<?php

class ADD_Field_Container_SeparateDate extends ADD_Field_Container_HorizContainer {
        private $_months = array("0"=>"Jan","1"=>"Feb","2"=>"Mar","3"=>"Apr","4"=>"May","5"=>"Jun","6"=>"Jul","7"=>"Aug","8"=>"Sep","9"=>"Oct","10"=>"Nov","11"=>"Dec");
        
        function ignoreContainerTest() {
            return true;
        }
        
	function beforeInstall(ADD_Form_FieldContainerInterface $form) {
            $fname = $this->getName();
            $ff = new ADD_FieldFactory_RowData();
            $title = $this->getTitle();
			$days =  array_merge(array('01','02','03','04','05','06','07','08','09'),range(10,31));
			$days = array_combine(range(0,30),$days);
            $reqd = $this->getSetting('required');
            $dayField = $ff->newInstance(array("Title"=>"$title - Day","Field"=>$fname."_day","Type"=>"dropdown","Options"=>$days,"Size"=>4,"Settings"=>array("empty_option"=>"- unknown -","label_size"=>"hide")));
            $monthField = $ff->newInstance(array("Title"=>"$title - Month","Field"=>$fname."_month","Type"=>"dropdown","Options"=>$this->_months,"Size"=>5,"Settings"=>array("empty_option"=>"- unknown -","label_size"=>"hide")));
            $yearField = $ff->newInstance(array("Title"=>"$title - Year","Field"=>$fname."_year","Type"=>"year","Min"=>"get_year(date_of_birth())","Max"=>"get_year(today())","Settings"=>array("label_size"=>"hide")));
            $this->setSetting('required',null);
			$page = $form->getPage();
			$dayField->finaliseField($page);
			$monthField->finaliseField($page);
			$yearField->finaliseField($page);
			
            $form->addChildField($fname,$dayField);
            $form->addChildField($fname,$monthField);
            $form->addChildField($fname,$yearField);
        }
        
        function beforeAddMarkup($page) {
            $page->addJavascript("ADD.assign('SeparateDate','".$this->getName()."')");
            
                  
		$name = $this->getName();
                $page->html()->startElement("label");
                $page->html()->addHtml($this->getTitle());
                $page->html()->endElement("label");
		$page->html()->startElement("div",array(
			"id" => $name,
			"name" => $name,
			"style" => "display:inline-block;width:100%",
                        "class"=>"content-".$this->getName()
		),$name."_start");
               
	}
        
}
?>