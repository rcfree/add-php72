<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 22/11/2016
 * Time: 11:50
 */

namespace ClinicLE\Page\RequestHandler;
use ClinicLE\Page\PageRequest;
use ClinicLE\System\Screen;
use ClinicLE\System\Event\ScreenEvent;
use ClinicLE\System\Event\ScreenEvents;
use ClinicLE\Page\Event\Breadcrumb;
use ClinicLE\Page\Event\TitleEvent;
use ClinicLE\Content\Render\HtmlElement;
use CLE;
use ClinicLE\Page\AbstractPage;
use Symfony\Component\HttpFoundation\Request;

class LegacyRequestHandler extends AbstractRequestHandler {

    function triggerPost(Request $request, AbstractPage $page) {
        $values = $request->request->all();
        $db = CLE::db();
        $result = $page->submitForm($values);
        $errors = $page->getErrors();
        if (!$errors || count($errors) == 0) {
            $db->commitTxn();

            $db->beginTxn();
            $this->triggerAfterPost($page);

            if ($db->inTxn()) $db->commitTxn();
            if (isset($result['redirect'])) {
                return array(
                    "redirect" => $result['redirect'],
                    "messages" => $page->getMessages(),
                    "warnings" => $page->getWarnings(),
                    "errors"=>$page->getErrors()
                );
            }
        }
        return null;
    }

    function generateGetContent(Request $request, AbstractPage $page)
    {
        if (!$page->isWebContent()) {
            return $this->generateNonWebContent($request, $page);
        }
        return parent::generateGetContent($request,$page);
    }



    function triggerAfterPost(Request $request, AbstractPage $submitPage) {
        $add = \ADD::sys();

        try {
            foreach ($add->getModules() as $module) {
                foreach ($module->getPages() as $page) {
                    if(!method_exists($page,"afterSubmit")) {
                        continue;
                    }
                    $page->afterSubmit($submitPage);
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        }

    }

    function generateTitle(HtmlElement $htmlElement,Request $request, AbstractPage $page) {
        $page->createTitle();
        $htmlElement->addHtml(CLE::web()->getContent("title"));
    }
    function generateBreadcrumb(HtmlElement $htmlElement,Request $request, AbstractPage $page) {
        $page->createBreadcrumb();
        $htmlElement->addHtml(CLE::web()->getContent("breadcrumb"));
    }
    function generateMainContent(HtmlElement $htmlElement,Request $request, AbstractPage $page) {
        $this->generateMessages($htmlElement);
        $page->createContent();
        $htmlElement->addHtml(CLE::web()->getContent("main"));
        $viewFile = $page->createViewFile();
        if($viewFile) {
            return array("view"=>$viewFile);
        }
    }

    function generateParentMenu(HtmlElement $htmlElement,Request $request, AbstractPage $page) {
        $this->createBaseContent();
        $htmlElement->addHtml(CLE::web()->getContent("topMenu"));
    }

    function createBaseContent() {
        $mods = \ADD::sys()->getModules();
        foreach ($mods as $mod) {
            foreach ($mod->getPages()->asArray() as $pageName=>$page) {
                $page->createBaseContent();
            }
        }
    }
}