<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class ADD_Field_DictionaryGrid extends ADD_Field_EncounterGrid {
	protected $stored = false;
	
	function getDataUrl() {
		return "/api/dictionary";
	}

    function _addGridProperties(\ClinicLE\Client\Container\Container $container, \ClinicLE\Client\Container\Table
    $table) {
		$container->putProperty("pid",ADD::sys()->getPatientId());
		
		$container->putProperty("pim",$this->assertSetting('use_page'));
		$container->putProperty("dataurl",$this->getDataUrl());
		$container->putProperty("dictionary",$this->assertSetting('dictionary'));
        if($this->getSetting('include_fields')) {
            $container->putProperty("includefields",$this->getSetting('include_fields'));
        }
	}

    /**
     * @param $childFields
     * @param $colField
     * @return string
    */
    public function generateCellContent($childFields, $colField)
    {
        $cell = $colField->createAngularCell();
        return $cell;
    }

}
