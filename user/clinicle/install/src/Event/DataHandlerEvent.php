<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Install\Event;
use ClinicLE\Install\DataHandler;
use ClinicLE\Install\FormSync;
use Symfony\Component\EventDispatcher\Event;

class DataHandlerEvent extends Event {
    private $dataHandler;
    private $formSync;


	function __construct(DataHandler $dataHandler, FormSync $formSync) {
	    $this->dataHandler = $dataHandler;
	    $this->formSync = $formSync;
	}

    /**
     * @return DataHandler
     */
	function getDataHandler() {
		return $this->dataHandler;
	}

    /**
     * @return FormSync
     */
    public function getFormSync()
    {
        return $this->formSync;
    }
}