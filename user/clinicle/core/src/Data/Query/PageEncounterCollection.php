<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PageEncounterCollection
 *
 * @author rob
 */
namespace ClinicLE\Data\Query;

class PageEncounterCollection extends EncounterCollection {
	private $pageNo = 1;
	private $rowsPerPage = 20;
	
	public function getRowsPerPage() {
	    return $this->rowsPerPage;
	}
	public function getPageNo() {
	    return $this->pageNo;
	}
}
