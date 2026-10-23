<?php

namespace ClinicLE\Client\Panel;
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
use ClinicLE\Client\Factory\PanelFactory;
use ADD_Content_Html;

abstract class AbstractPanel {
    protected $contentPage;
    private $panelFactory;

    /**
     * @return PanelFactory
     */
    public function getPanelFactory()
    {
        return $this->panelFactory;
    }

    function getContentPage() {
        return $this->contentPage;
    }


    function setContentPage($contentPage) {
        $this->contentPage = $contentPage;
    }

    function __construct($panelFactory) {
        $this->panelFactory = $panelFactory;
    }
    function createPanelContent($values = array()) {
        try {
            return $this->generateContent($values);
        }
        catch(\Exception $ex) {
            $content = new ADD_Content_Html();
            $content->addElement("input",array("value"=>"1","id"=>"ng-error","type"=>"hidden"));
            $content->addTextElement("div",$ex->getMessage()."<pre>".$ex->getTraceAsString()."</pre>",array("class"=>"alert alert-error"));
            return $content;
        }
    }
    abstract function generateContent($values = array());


}
