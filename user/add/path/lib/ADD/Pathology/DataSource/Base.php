<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use ClinicLE\Common\Config\AbstractConfig;

abstract class ADD_Pathology_DataSource_Base implements ADD_Pathology_DataSourceInterface {
	public function __construct(AbstractConfig $config) {
		$this->_config=$config;
	}
	function getTestItemTotal() {}
}
