<?php

namespace ClinicLE\Install\Dictionary;

/**
 * Defines methods required for a DictLoad plugin
 *
 * @author Rob Free
 * @category ADD
 * @package ModuleBundle
 */
interface DictPluginInterface {
	function processFields(&$fields);
	function beforeProcessRow(&$row);
	function afterProcessRow(&$row);
}