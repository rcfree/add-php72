<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 07/09/16
 * Time: 14:04
 */

namespace ClinicLE\Client\Panel;
use ADD_Content_Html;
use CLE;

class MainPanel extends AbstractPanel
{
    function generateContent($values=array()) {
        return $this->createMainContent($values);
    }
    protected function createMainContent($values=array()) {
        $add = CLE::sys();
        $db = CLE::db();

        $db->beginTxn();
        $currentPage = $this->getContentPage();

        $currentPage->setupComponents();

        $panel = new ADD_Content_Html();

        $panel->addElement("input",array("id"=>"ng-menu","type"=>"hidden","value"=>$currentPage->getModule()->getAlias()));
        $panel->addElement("input",array("id"=>"ng-current","type"=>"hidden","value"=>$currentPage->getModule()->getAlias().":".$currentPage->getName()));
        $beforeRedirectState = $add->hasState('redirect');

        if($beforeRedirectState) {
            $panel->addElement("input",array("id"=>"ng-redirect","type"=>"hidden","value"=>$beforeRedirectState));
            $add->setState('redirect',null);
        }
        $currentPage->createBreadcrumb();
        $title = $currentPage->createTitle();
        CLE::web()->createBaseContent();
        if($add->config("debug")) {
            $panel->addTextElement("p","DEBUG - Panel page:{{ getPageData().pagePath }}");
        }
        $panel->addHtml(CLE::web()->getContent("breadcrumb"));
        $panel->addHtml(CLE::web()->getContent("topMenu"));

        $panel->addTextElement("p",$title,array("class"=>"lead"));
        $mainContent = CLE::web()->getContent("main");
        $web = CLE::web();

        $currentPage->createContent();

        if ($db->inTxn()) {
            $db->commitTxn();
        }
        $panel->startElement("div",array("id"=>"messages"));

        $panel->addHtml($this->drawMessages("error",$web->getErrors()));
        $panel->addHtml($this->drawMessages("warning",$web->getWarnings()));
        $panel->addHtml($this->drawMessages("success",$web->getMessages()));
        $panel->endElement("div");

        $panel->addHtml($mainContent);

        $afterRedirectState = $add->hasState('redirect');

        if ($afterRedirectState) {
            return $this->createRedirectedContent($afterRedirectState, $values);
        }
        $context = CLE::sys()->getAccessManager()->getContext();
        $context->session->set_userdata('add_messages', null);
        $context->session->set_userdata('add_warnings', null);
        $pagePath = $add->getParam('pagePath');
        if(strlen($pagePath)>0) {
            $context->session->set_userdata('cle_path',$pagePath);
        }
        $this->addJavascriptToContent($panel);
        return $panel;
    }

    protected function createRedirectedContent($redirectPath,$values=array()) {
        $add = CLE::sys();
        $add->setState("redirect",$redirectPath);

        $url = parse_url($redirectPath);
        $params = array();
        if(isset($url['query'])) {
            parse_str($url['query'],$params);
        }
        $params['pagePath']=$url['path'];
        $params['panel']='main';

        $add->setParams($params);
        $this->getPanelFactory()->setContentPageFromPath($url['path'],$this);
        return $this->createMainContent($values);
    }

    function addJavascriptToContent($content) {
        $this->addContentPageJs($content);

        $jsList = CLE::web()->getJavascript();

        if (!isset($jsList['document'])) {
            return;
        }

        $content->startElement("script",array("type"=>"text/javascript"));
        $content->addHtml("ADD.Form.onModalReady['content']=function() {");
        foreach($jsList['document'] as $jsItem) {
            $content->addHtml($jsItem."\n");

        }
        $content->addHtml("};");
        $content->endElement("script");
    }

    function addContentPageJs($content) {
        $contentPage = $this->getContentPage();

        $pageJs = $contentPage->getSetting("javascript");
        if(!$pageJs) {
            return;
        }
        if(!is_array($pageJs)) {
            $pageJs = array($pageJs);
        }
        foreach($pageJs as $pageJsItem) {
            $content->addTextElement("script","",array("type"=>"text/javascript",
                "src"=>"/module/".$contentPage->getModule()
                        ->getName()
                    ."/"
                    .$pageJsItem));
        }
    }
    function drawMessages($type,$items) {
        $html = "";
        if (!$items) return "";
        if (count($items)==0) return "";
        $html.="<div class='alert alert-{$type}'>";
        $html.='<button type="button" class="close" data-dismiss="alert">&times;</button>';
        $html.="<ul>";
        foreach($items as $item) {
            $html.="<li>".$item."</li>";
        }
        $html.="</ul>";
        $html.="</div>";
        return $html;
    }
}