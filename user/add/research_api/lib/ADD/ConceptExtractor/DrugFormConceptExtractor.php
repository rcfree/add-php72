<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_ConceptExtractor_DrugFormConceptExtractor extends ADD_ConceptExtractor_FormConceptExtractor {
	function createConcepts() {
		$concepts = parent::createConcepts();
		unset($concepts[2]);
		//$drugName = $concepts[2];
		$drugType = $this->getConceptData($this->form->getField("drug_name"));
		
	}
	
}