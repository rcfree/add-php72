<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AbstractContent
 *
 * @author rcf8
 */

abstract class ADD_Page_AbstractContent extends ADD_Page {
	protected $_public = true;

	function isWebContent() {
		return false;
	}
	
	function createContentType() {
		return "Html";
	}
		
	function createOutput($values = array()) {
		$registry = CLE::svc(\ClinicLE\System\Service\CoreServices::FACTORY_REGISTRY);

        $panel = null;
        try {
            $panel = $registry->newInstance(\ClinicLE\Client\Factory\ClientFactories::PANEL,$this->getPanelName());
        }
        catch(\ClinicLE\Page\Exception\PublicPageException $ex) {
            $content = new ADD_Content_Html();
            $content->addElement("input",array("value"=>"1","id"=>"ng-loggedout","type"=>"hidden"));
            return $content;
        }
        catch(\ClinicLE\Module\Exception\ItemUndefinedException $ex) {
            $content = new ADD_Content_Html();
            $content->addTextElement("p",$ex->getMessage(),array("class"=>"text-error"));
            return $content;
        }

		return $panel->createPanelContent($values);
	}

	function getPanelName() {
	    return ADD::sys()->getParam("panel");
    }
	function _addError(Exception $ex) {
		ADD::web()->addError($ex->getMessage());
		if (ADD::sys()->config('stacktrace')) {
			ADD::web()->addError("<pre>" . $ex->getTraceAsString() . "</pre>");
		}
		$this->_error = true;
	}

}
