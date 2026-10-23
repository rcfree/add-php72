<?php

class ADD_Field_DataList extends ADD_Field_Text {
	public function getDisplayText($form, $id) {
		$dataValues = $this->getValue();
		if(substr($dataValues,0,1)=="{") $dataValues=ADD_Util::unserialize($dataValues);
		if(ADD_Util::isEmpty($dataValues)) return "No ".lcfirst($this->getTitle());	
		if(!is_array($dataValues)) return $dataValues;
		
		$dataList = array();
		$prefix="<table class='table table-condensed table-bordered'><thead><tr><th>Organism</th><th>Quantifier</th><th>Sensitivities</th></tr></thead><tbody>";
		foreach($dataValues as $items) {
			$dataItems=array();	
			$organismType = ADD_Util::val('organism',$items);
			$quantifier = ADD_Util::val('quantifier',$items);
			foreach(ADD_Util::val('sens',$items,array()) as $name => $item) {
				$dataItems[]="$name ($item)";
			}
			
			$dataList[]="<tr class='info'><td>$organismType</td><td>$quantifier</td><td>".implode(",",$dataItems)."</td></tr>";
		}
		return $prefix.implode("",$dataList)."</tbody></table>";
	}
}