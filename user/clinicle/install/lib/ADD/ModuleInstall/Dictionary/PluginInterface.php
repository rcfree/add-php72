<?php

/**
 * Defines methods required for a DictLoad plugin
 *
 * @author Rob Free
 * @category ADD
 * @package ModuleBundle
 */
interface ADD_ModuleInstall_Dictionary_PluginInterface {
	function processFields(&$fields);
	function beforeProcessRow(&$row);
	function afterProcessRow(&$row);
}