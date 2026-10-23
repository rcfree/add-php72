<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/11/2016
 * Time: 11:12
 */

namespace ClinicLE\Page\RequestHandler;


use ClinicLE\Content\Render\HtmlElement;
use ClinicLE\Page\Event\AfterSubmitEvent;
use ClinicLE\Page\Event\SubmitEvent;
use ClinicLE\Page\Event\SubmitEvents;
use ClinicLE\Page\PageResponse\ContentPageResponse;
use ClinicLE\Page\PageResponse\RedirectPageResponse;
use ClinicLE\Page\PageResponse\ViewPageResponse;
use ClinicLE\System\Event\ContentEvent;
use ClinicLE\Page\PageRequest;
use CLE;
use ClinicLE\Page\Event\PageEvents;
use ClinicLE\System\Event\ContentEvents;
use ClinicLE\System\Service\CoreServices;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use ClinicLE\Page\AbstractPage;

class ComponentRequestHandler extends AbstractRequestHandler
{

    function triggerPost(Request $request, AbstractPage $page)
    {
        $system = CLE::sys();
        $db = CLE::db();
        $submitEvent = new SubmitEvent($request);
        $submitEvent->setAudit(CLE::svc(CoreServices::AUDIT_REPOSITORY));
        $events = $page->getEvents();
        CLE::info("submitEvent",$submitEvent->getFormValues());
        $events->dispatch(SubmitEvents::ACCESS,$submitEvent);
        if ($system -> hasState('cancel')) {
            CLE::db('token') -> unregister();
            return;
        }
        $events->dispatch(SubmitEvents::BEFORE,$submitEvent);

        CLE::info("submitEvent",$submitEvent->getFormValues());
        $events->dispatch(SubmitEvents::SUBMIT,$submitEvent);

        $page->setData($submitEvent->getFormValues());

        if($submitEvent->isPropagationStopped()) {
            return;
        }

        if (count(CLE::web()->getErrors()) > 1) {
            $db->rollback();
        }

        $db->commitTxn();

        $db->beginTxn();
        $this->triggerAfterPost($submitEvent, $page);

        if ($db->inTxn()) {
            $db->commitTxn();
        }
        if ($submitEvent->getRedirect()) {
            CLE::info("redirect:",$submitEvent->getRedirect());
            return new RedirectPageResponse("/".$submitEvent->getRedirect());
        }
        return $this->generateGetContent($request, $page);
    }
    function triggerAfterPost(SubmitEvent $event, AbstractPage $submitPage) {
        $afterSubmitEvent = new AfterSubmitEvent($event, $submitPage);
        CLE::sys()->getEvents()->dispatch(SubmitEvents::AFTER,$afterSubmitEvent);
    }
    function generateTitle(HtmlElement $htmlElement, Request $request, AbstractPage $page)
    {
        $title = $page->createTitle();
        $htmlElement->addTextElement("p",$title,array("class"=>"lead"));
    }

    function generateBreadcrumb(HtmlElement $htmlElement, Request $request, AbstractPage $page)
    {
        $breadcrumb = $page->createBreadcrumb();
        $htmlElement->addHtml($breadcrumb);
    }

    function generateMainContent(HtmlElement $htmlElement, Request $request, AbstractPage $page)
    {
        $contentEvent = new ContentEvent($htmlElement);
        $events = $page->getEvents();
        $events->dispatch(PageEvents::VIEW_FILE,$contentEvent);
        $events->dispatch(PageEvents::DISPLAY_ACCESS,$contentEvent);
        $events->dispatch(PageEvents::BEFORE_POPULATE,$contentEvent);
        $events->dispatch(PageEvents::POPULATE,$contentEvent);
        $events->dispatch(PageEvents::AFTER_POPULATE,$contentEvent);
        $events->dispatch(PageEvents::BEFORE_ALL_GENERATE,$contentEvent);
        $events->dispatch(PageEvents::BEFORE_GENERATE,$contentEvent);
        $events->dispatch(PageEvents::GENERATE,$contentEvent);
        $events->dispatch(PageEvents::AFTER_GENERATE, $contentEvent);
        $events->dispatch(PageEvents::AFTER_ALL_GENERATE, $contentEvent);

        if($contentEvent->getRedirect()) {
            return new RedirectPageResponse($contentEvent->getRedirect());
        }
        if($contentEvent->getViewFile()) {
            return new ViewPageResponse($contentEvent->getViewFile(),$contentEvent->getData());
        }
        $this->addJavascriptToContent($htmlElement,$page);

        return new ContentPageResponse($contentEvent->getContent(),200);
    }

    function generateParentMenu(HtmlElement $htmlElement, Request $request, AbstractPage $page)
    {
        $html = CLE::web()->getContent("topMenu");
        $event = new ContentEvent($html);
        CLE::info("section menus",count($page->getEvents()->getListeners(ContentEvents::SECTION_MENU)));
        $page->getEvents()->dispatch(ContentEvents::SECTION_MENU,$event);
        $htmlElement->addHtml($html);
    }

}