<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


namespace ClinicLE\Data\Query;

class EncounterCollection {
	
	private $rows = array();
	private $currentPos = 0;
	
        public function __construct($rows, $type=null) {
            foreach($rows as $row) {
                if(is_a($row,"ClinicLE\Data\Record\EncounterRecord")) {
                   $this->rows[]=$row; 
                } 
                else {
                    $encounter = new \ClinicLE\Data\Record\EncounterRecord();
                    $encounter->setType($type);
                    $encounter->setAllData($row);
                    $encounter->setRetrieved(true);
                    $this->rows[]=$encounter;
                }
            }
        }
        
	/**
	 * 
	 * @return \ClinicLE\Database\Encounter
	 */
	public function next() {
	    if($this->currentPos>count($this->rows)) {
		return false;
	    }
	    if(!isset($this->rows[$this->currentPos])) {
		return false;
	    }
	    $encounter = $this->rows[$this->currentPos];
	    $this->currentPos++;
	    return $encounter;
	}
	
	public function all() {
	    return $this->rows;
	}
	
	public function setRows($rows) {
		$this->rows = $rows;
	}
	
	public function count() {
	    return count($this->rows);
	}
}