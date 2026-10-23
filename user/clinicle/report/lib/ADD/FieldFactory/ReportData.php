<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReportData
 *
 * @author rcf8
 */

class ADD_FieldFactory_ReportData {
	function newInstance($args) {
		return CLE::sys()->newFactoryInstance(ClinicLE\System\Factory\SystemFactories::REPORT_FIELD,$args);
	}
}
