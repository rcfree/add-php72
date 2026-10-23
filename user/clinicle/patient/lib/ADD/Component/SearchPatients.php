<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 12/09/18
 * Time: 13:49
 */
use ClinicLE\Common\Util;

class ADD_Component_SearchPatients extends ADD_Component {

    function title(&$title) {
        $title="Search patients";
    }
    function displayAccess() {
        $page = $this->_page;
        if (!$page->getPermission('search')) {
            throw new ADD_NoPermissionToAccessException("Sorry you do not have permission to search patients.");
        }
    }

    function beforePopulate() {
        $values = CLE::sys()->getParams();
        $this->hadInput = false;

        $this->terms = Util::val('terms',$values);
        if ($this->terms) $this->hadInput = true;

        $this->fname = Util::val('first_name',$values);
        if ($this->fname) $this->hadInput = true;

        $this->lname = Util::val('last_name',$values);
        if ($this->lname) $this->hadInput = true;

        $this->dob = Util::val('dob',$values);
        if ($this->dob) $this->hadInput = true;

        $this->searchSel = Util::val('search_sel',$values,1);

        // Form elements
        $args = $this->searchSel==1 ? array('id'=>$this->terms,'fname'=>null,'lname'=>null,'dob'=>null,'raw'=>true,'search_sel'=>$this->searchSel) : array('id'=>null,'fname'=>$this->fname, 'lname'=>$this->lname, 'dob'=>$this->dob,'raw'=>true,'search_sel'=>$this->searchSel);
        $this->values = $values;
        $this->getPage()->setData($args);
    }

    public function menuPath(&$menuPath) {
        $menuPath = "search";
    }


    function expectedPermissions(&$expPerms) {
        $expPerms = $expPerms + array('search');
    }

    function afterGenerate() {
        $page = $this->_page;
        $html = $page->html();
        $html->startElement("div",array("id"=>"modal-remove","class"=>"modal hide fade","style"=>"width:500px"));
        $html->startElement("div", array("class"=>"modal-body"));
        $html->addTextElement("div","",array("id"=>"modal-remove-text"));

        $html->endElement("div");
        $html->startElement("div",array("class"=>"modal-footer"));
        $html->addTextElement("a","No, thanks",array("href"=>"javascript:;","onclick" => "$('#modal-remove').modal('hide')","class"=>"btn","id"=>"modal-no"));
        $html->addTextElement("a","Yes, please",array("href"=>"javascript:;","class"=>"btn btn-primary","id"=>"modal-confirm-yes"));
        $html->endElement("div");
        $html->endElement("div");
    }

    function baseContent() {
        $page = $this->_page;
        $search = ADD::web()->getContent('search');
        if (!$page->getPermission('search')) return;
        $search->startElement("form",array("class"=>"navbar-form pull-right", "action" => $page->createMenuPath(),"method"=>"post"));
        $search->startElement("div",array("class"=>"input-append"));
        $search->addElement("input",array("class"=>"span2","name"=>"terms","type"=>"text","placeholder"=>"Enter patient no"));
        $search->startElement("button",array("type"=>"submit","class"=>"btn"));
        $search->addTextElement("i","",array("class"=>"icon-search"));
        $search->addHtml("&nbsp;Search");
        $search->endElement("button");
        $search->endElement("div");
        $search->endElement("form","search_patient_end");
    }
}
