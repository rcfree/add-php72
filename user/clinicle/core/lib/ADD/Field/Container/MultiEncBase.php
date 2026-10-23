<?php

class ADD_Field_Container_MultiEncBase extends ADD_Field_Container {
	private $_rowEncounters = array();
	function getRowEncounters($page) {
		$add = ADD::sys();
		if ($this->_rowEncounters) return $this->_rowEncounters;
		//if ($add->hasState('validation')) return array();
		$form = $page->getComponent('Form');
		$patientId= $add->getPatientId();
		$edb = ADD::db('encounter');
		$ppage = $page->getParentPage();
		$per = $ppage->getEncounterRecord($add->getParam('eid'));
		$subEncType = $page->getSetting('encounter');
		if (!$per->exists()) throw new ADD_EncounterException("No parent encounter record");
               
		$cers = ADD::sys()->getEncounterManager()->children($per,$subEncType);
        $rowEncounters = array();
		if ($cers) {
			foreach($cers as $cer) {
				$rowEncounters[$cer->getId()] = $cer;
			}
		}
		return $rowEncounters;
	}
	
	function setRowEncounters($rowEncounters) {
		$this->_rowEncounters = $rowEncounters;
	}	
	function addTableMarkup($headings,$rows, $page) {
		$add = ADD::sys();
		$html = $page->html();
		
		$html->startElement("div");//,array("style"=>"height:200px;overflow-y:scroll"));
		if (!$this->getSetting('no_count')) {
			if (count($rows)==0) {
				$html->addTextElement("div","No ".$this->getSetting('description',$page->getSetting('single'))." records");
				return;
			}
			
			$html->addTextElement("div",count($rows)." " . $this->getSetting('description',$page->getSetting('single'))." record".(count($rows)==1 ? "" : "s"));
			$tableClass = "table table-hover table-condensed table-striped";
			if ($this->getSetting('class')) $tableClass.=" ".$this->getSetting('class');
			$html->startElement("table",array("class"=>$tableClass,"name"=>$this->getName()));
			$html->startElement("thead");
			$html->startElement("tr");
			foreach($headings as $heading) {
				$html->addTextElement("th",$heading);
			}
			$html->endElement("tr");
			$html->endElement("thead");
			$html->startElement("tbody");
			
			foreach($rows as $row) {
				$rattrs=array();
				if (isset($row['class'])) $rattrs['class']=$row['class'];
				$html->startElement("tr",$rattrs);
				if (!isset($row['data'])) throw new ADD_FieldException("No 'data' element in row");
				foreach($row['data'] as $cell) {
					$html->addTextElement("td",$cell);
				}
				$html->endElement("tr");
			}
			$html->endElement("tbody");
			$html->endElement("table");
			$html->endElement("div");
			
		}
	}
}
?>