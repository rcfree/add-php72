<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 22/11/2016
 * Time: 11:50
 */

namespace ClinicLE\Page\RequestHandler;
use ClinicLE\Application\Resource\ApplicationResources;
use ClinicLE\Page\PageResponse\ContentPageResponse;
use ClinicLE\System\Event\EventDispatcher;
use ClinicLE\System\Screen;
use ClinicLE\System\Event\ScreenEvent;
use ClinicLE\System\Event\ScreenEvents;
use ClinicLE\Page\Event\Breadcrumb;
use ClinicLE\Page\Event\TitleEvent;
use ClinicLE\Content\Render\HtmlElement;
use ClinicLE\System\Application;
use CLE;
use ClinicLE\System\Service\Services;
use ClinicLE\System\Service\SessionStorage;
use ClinicLE\System\WebApplication;
use Symfony\Component\HttpFoundation\Request;
use ClinicLE\Page\AbstractPage;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractRequestHandler {
    private $webApp;

    function __construct(WebApplication $webApp, EventDispatcher $events, Services $services) {
        $this->webApp = $webApp;
        $this->events = $events;
        $this->services = $services;
    }
    function generatePostContent(Request $request, AbstractPage $page) {
        $results = $this->triggerPost($request, $page);
        if(!$results) {
            return $this->generateGetContent($request, $page);
        }
        return $results;
    }
    abstract function triggerPost(Request $request, AbstractPage $page);
    private function generateLegacyNonWebContent(Request $request, AbstractPage $page) {
        $db = CLE::db();

        $page->setup();
        $values = $request->request->all();
        $ct = $page->createContentType();
        CLE::web()->setContentClass($ct);
        $output = $page->createOutput($values);
        if ($db->inTxn()) {
            $db->commitTxn();
        }
        $headers = $output->headers() ? : array();
        $headers['Content-Type']=$output->mimeType();
        if ($output->headers()) {
            foreach ($output->headers() as $header) {
                $headers[]=$header;
            }
        }
        $output->headers($headers);
        return new ContentPageResponse($output);
    }
    function generateGetContent(Request $request, AbstractPage $page) {
        if (is_a($page,"ADD_Page") && !$page->isWebContent()) {
            return $this->generateLegacyNonWebContent($request, $page);
        }
        $mainHtml = new HtmlElement();
        CLE::web()->setContent($mainHtml);
        $add = CLE::sys();

        $beforeRedirectState = $add->hasState('redirect');

        if($beforeRedirectState) {
            $mainHtml->addElement("input",array("id"=>"cle-redirect","type"=>"hidden",
                "value"=>$beforeRedirectState));
            $add->setState('redirect',null);
        }
        else {
            $session = $add->getAccessManager()->getSession();
            if($page->isStoredAsCurrent()) {
                CLE::info("set current to ".$request->getRequestUri());
                $session->setUserData('cle_path', $request->getRequestUri());
            }
        }
        //$this->createBaseContent();

        //CLE::info("messages:",CLE::web());
        //CLE::info("messages:",CLE::web());
        $am = CLE::sys()->getAccessManager();


        $this->generateDirectives($mainHtml,$page);
        $this->generateBreadcrumb($mainHtml,$request, $page);
        $this->generateTitle($mainHtml,$request, $page);
        $this->generateParentMenu($mainHtml,$request, $page);
        $this->generateMessages($mainHtml, $request, $page);

        $response = $this->generateMainContent($mainHtml, $request, $page);
        $afterRedirectState = $add->hasState('redirect');

        if ($afterRedirectState) {
            return $this->createRedirectedContent($afterRedirectState, $request);
        }
        $page->appendToResponse($response);

        return $response;
    }

    protected function createRedirectedContent($redirectPath,Request $request) {
        $add = CLE::sys();
        $add->setState("redirect",$redirectPath);

        CLE::web()->setContent(new HtmlElement());
        $request = Request::create($redirectPath);
        $session = $add->getAccessManager()->getSession();
        CLE::info("set current to redirect ".$redirectPath);


        $response = $this->services->get(ApplicationResources::PAGE_RESOLVER)->resolvePage($request);
        $currentPage = $add->getAccessManager()->getCurrentPage();
        if($currentPage->isStoredAsCurrent()) {
            $session->setUserData('cle_path', $redirectPath);
        }
        return $response;
    }

    abstract function generateTitle(HtmlElement $htmlElement, Request $request, AbstractPage $page);
    abstract function generateBreadcrumb(HtmlElement $htmlElement, Request $request, AbstractPage $page);

    /**
     * @param HtmlElement $htmlElement
     * @param Request $request
     * @param AbstractPage $page
     * @return Response
     */
    abstract function generateMainContent(HtmlElement $htmlElement, Request $request, AbstractPage $page);
    abstract function generateParentMenu(HtmlElement $htmlElement, Request $request, AbstractPage $page);

    function generateDirectives(HtmlElement $htmlElement, AbstractPage $page) {
        $module = $page->getModule();
        $add = CLE::sys();
        $htmlElement->addElement("input",array("id"=>"cle-sitename","type"=>"hidden","value"=>$add->getConfig()->getSetting
        ("site_name","ADD")));
        $htmlElement->addElement("input",array("id"=>"cle-sitetag","type"=>"hidden","value"=>$add->getConfig()->getSetting
        ("site_tag","LOCAL")));
        $htmlElement->addElement("input",array("id"=>"cle-menu","type"=>"hidden","value"=>$module->getAlias()));
        $htmlElement->addElement("input",array("id"=>"cle-current","type"=>"hidden","value"=>$module->getAlias()
            .":".$page->getName()));
        $am = CLE::sys()->getAccessManager();
        $beforeRedirectState = $add->hasState('redirect');

        if($beforeRedirectState) {
            $htmlElement->addElement("input",array("id"=>"cle-redirect","type"=>"hidden",
                "value"=>$beforeRedirectState));
            $htmlElement->setState('redirect',null);
        }
    }
    function addResponseHeaders(Response $response) {
        $am = CLE::sys()->getAccessManager();
        if($am->getSession()->getUserData(SessionStorage::SESSION_EXPIRED)) {
            CLE::info("Adding loggedout to request");
            $response->headers->set("Clinicle-Expired",1);
        }
    }
    function generateMessages(HtmlElement $htmlElement, Request $request, AbstractPage $page) {
        $htmlElement->startElement("div",array("id"=>"messages"));

        $htmlElement->addHtml($this->drawMessages("error",$this->webApp->getErrors()));
        $htmlElement->addHtml($this->drawMessages("warning",$this->webApp->getWarnings()));
        $htmlElement->addHtml($this->drawMessages("success",$this->webApp->getMessages()));

        $htmlElement->endElement("div");
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

    function addJavascriptToContent(HtmlElement $htmlElement,AbstractPage $page) {
        $this->addContentPageJs($htmlElement, $page);

        $jsList = CLE::web()->getJavascript();

        if (!isset($jsList['document'])) {
            return;
        }

        $htmlElement->startElement("script",array("type"=>"text/javascript"));
        $htmlElement->addHtml("ADD.Form.onModalReady['".$page->getJavascriptIdent()."']=function() {");
        foreach($jsList['document'] as $jsItem) {
            $htmlElement->addHtml($jsItem."\n");
        }
        $htmlElement->addHtml("};");
        $htmlElement->endElement("script");
    }

    function addContentPageJs(HtmlElement $htmlElement, AbstractPage $page) {
        $pageJs = $page->getSetting("javascript");
        if(!$pageJs) {
            return;
        }
        if(!is_array($pageJs)) {
            $pageJs = array($pageJs);
        }
        foreach($pageJs as $pageJsItem) {
            $htmlElement->addTextElement("script","",array("type"=>"text/javascript",
                "src"=>"/module/".$page->getModule()
                        ->getName()
                    ."/"
                    .$pageJsItem));
        }
    }
    function createBaseContent() {
        $mods = \ADD::sys()->getModules();
        foreach ($mods as $mod) {
            foreach ($mod->getPages()->asArray() as $pageName=>$page) {
                if(!is_callable(array($page,"createBaseContent"))) {
                    continue;
                }
                $page->createBaseContent();
            }
        }
    }

}