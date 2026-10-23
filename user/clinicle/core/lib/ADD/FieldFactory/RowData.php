<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RowData
 *
 * @author rob
 */
class ADD_FieldFactory_RowData {
	function newInstance($name,$arguments=array()) {
		return ADD::sys()->getFactory(ClinicLE\System\Factory\SystemFactories::ROW_FIELD)->newInstance($name,$arguments);
	}
}
