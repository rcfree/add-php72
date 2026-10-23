<?php


class ADD_Component_UniqueDictItem extends ADD_Component implements ADD_Component_AjaxInterface {
	function ajaxFunction($values,ADD_Page $callPage) {
        $term = $values['title'];
        if(ADD_Util::isEmpty($term)) return;
		$dictName = $callPage->assertSetting('dictionary');
        $dictItems = ADD::db('dictionary')->getDictionaryItemsMatchingTerm($dictName, $term, $fromStart=false);
		$otherItems = array();
		foreach($dictItems as $dictItem) {
			if($dictItem['id']==$values['__id']) {
				continue;
			}
			$otherItems[]=$dictItem;
		}
		if(count($otherItems)==0) return;
		$itemDesc = ADD_Util::pluralize($otherItems,$callPage->getSetting('single')." matches",$call->getSetting('single')." match");
		ADD::web()->addWarning(count($otherItems)." ".$itemDesc." your title:<ul><li>".implode("</li><li>",array_map(function($item) { return $item['title']; },$otherItems))."</li>");
    }

    function ajaxInputs(ADD_Page $callPage) {
        return array('title');
    }
}
?>