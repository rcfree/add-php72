<?php

abstract class ADD_FieldFactory {

    function getBasePaths() {
       return array("ADD/Field","ADD/Field/Text","ADD/Field/Container","ADD/Field/List","ADD/Field/Expression","ADD/Field/Button","ADD/Field/Submit");
    }

	function newInstance($args) {
    }

    function loadFieldClass($fieldType,$fieldData) {
        $fieldReg = ADD::sys()->getService(\ClinicLE\System\Service\CoreServices::FACTORY_REGISTRY);
        $field = $fieldReg->newInstance(\ClinicLE\System\Factory\SystemFactories::ROW_FIELD, $fieldType, array($fieldData));
        return $field;
	}

    function getClassName($basePath,$fieldType) {
        return str_replace("/","_",$basePath."/{$fieldType}");
    }
		
}