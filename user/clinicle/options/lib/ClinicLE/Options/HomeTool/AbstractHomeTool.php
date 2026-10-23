<?php

namespace ClinicLE\Options\HomeTool;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AbstractHomeTool
 *
 * @author rcf8
 */
abstract class AbstractHomeTool {
	abstract function createTool();
	abstract function toolTitle();
	function isAvailable() {
		return true;
	}
	function toolWeight() {}
	function toolClass() {}
}
