<?php

namespace ClinicLE\Install\Dictionary;

use ADD;
use ClinicLE\Common\Config\FileConfig;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Supervisor {
    private $config;

function update($mod, $refreshDict=false) {
		$add = ADD::sys();
		$log = ADD::log();
		$modPath = $mod->getPath();
                try {
                    $config = new FileConfig($modPath . "/dictionary.ini");
                }
                catch(\ClinicLE\Common\Exception\FileNotFoundException $ex) {
                    $log->info("No dictionary file in module:".$mod->getName());
                    return;
                }
		$this->_module = $mod;

		$ddb = ADD::db('dictionary');
	    $this->config = $config;
		foreach ($config->getSections() as $sname => $section) {
                $dictFile = new SingleFile($sname,$section, $modPath);
                $dictFile->setSupervisor($this);
                $dictFile -> updateSingle($refreshDict);
		}
	}
	function getModule() {
		return $this->_module;
	}

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

}