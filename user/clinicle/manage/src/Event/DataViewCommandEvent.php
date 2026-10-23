<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Manage\Event;
use ClinicLE\System\Event\CommandEvent;

/**
 * Description of PatientEvent
 *
 * @author rob
 */
class DataViewCommandEvent extends CommandEvent {
	private $title;
	private $subTitle;
	private $data=array();
	private $fields = array();
    private $idFieldName = "id";

	function getTitle() {
		return $this->title;
	}

	function getSubTitle() {
		return $this->subTitle;
	}

	function getData() {
		return $this->data;
	}

	function setTitle($title) {
		$this->title = $title;
	}

	function setSubTitle($subTitle) {
		$this->subTitle = $subTitle;
	}

	function setData($data) {
		$this->data = $data;
	}

	function getFields() {
		return $this->fields;
	}

	function setFields($fields) {
		$this->fields = $fields;
	}

	function setIdFieldName($idFieldName) {
	    $this->idFieldName = $idFieldName;
    }

    /**
     * @return mixed
     */
    public function getIdFieldName()
    {
        return $this->idFieldName;
    }

}
