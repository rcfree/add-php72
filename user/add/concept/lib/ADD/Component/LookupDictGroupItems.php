<?php

class ADD_Component_LookupDictGroupItems extends ADD_Component implements ADD_Component_AjaxInterface {
    const LOOKUP = "LookupDictGroupItems";
    function ajaxFunction($values, ADD_Page $callPage) {
        $add = ADD::sys();
        $dgLookField = $callPage->assertSetting(self::LOOKUP.'.lookup_field');
		$outputField = $callPage->assertSetting(self::LOOKUP.'.output_field');
        
        $dg = $add->getParam($dgLookField);
		if(!$dg) return array($outputField=>array("options"=>array()));
        $ddb = ADD::db('dictionary');
        $dis = $ddb->getDictionaryItemsInGroup($callPage->assertSetting(self::LOOKUP.'.dictionary'),$dg);
        $emptyOption = $callPage->getSetting(self::LOOKUP.'.empty_option');
        if($emptyOption) $opts[]=array("value"=>"","label"=>$emptyOption);

        foreach($dis as $di) {
            $opts[]=array("value"=>$di['value'],"label"=>$di['title']);
        }
        return array($outputField=>array("options"=>$opts));
    }

    function ajaxInputs(ADD_Page $callPage) {
        $dgLookField = $callPage->assertSetting(self::LOOKUP.'.lookup_field');
        
        return array($dgLookField);
    }
    function beforeGenerate() {
        $page =  $this->_page;
        $outField = $page->getComponent('Form')->getField($page->assertSetting(self::LOOKUP.'.output_field'));
        $lupField = $page->getComponent('Form')->getField($page->assertSetting(self::LOOKUP.'.lookup_field'));
        
        ADD::log()->info("outputField value:".$outField->getValue());
        ADD::log()->info("lookupField value:".$lupField->getValue());
    }
}