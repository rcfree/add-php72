<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:30
 */
namespace ClinicLE\Search\Component;
use CLE;
use ClinicLE\Common\Util;
use ClinicLE\Search\Resource\SearchServices;


class SearchSettingsHandlerComponent extends \ADD_Component
{
    function getType() {
        return "SearchSettingsHandler";
    }
    public function expectedPermissions(&$expPerms) {
        $expPerms[]="edit";
    }

    function beforePopulate() {
        $add = CLE::sys();
        $add->setState("new",false);
        $ssData = CLE::svc(SearchServices::SEARCH_SESSION_DATA);
        $groupId = CLE::sys()->getParam("parentid");
        $settings = $ssData->getSearchGroupPart($groupId,"settings");
        $data = $settings;

        $this->pim = $add -> assertParam('pim');
        $this->pageInModule = $add->getPageFromPim($this->pim);
        $this->pageInModule->setData($data);
    }
    function afterGenerate() {
        $html = CLE::web()->getContent();

        $html->addElement('input',array('type'=>'hidden','value'=>CLE::sys()->getParam("parentid"),'name'=>"__group"));

    }

    function submit($values) {
        $form = $this->_page->getComponent('Form');
        $groupId = $form->getUncleanValue("__group");
        $ssData = CLE::svc(SearchServices::SEARCH_SESSION_DATA);
        $ssData->storeSearchGroupPart($groupId,"settings",$values);
    }
}