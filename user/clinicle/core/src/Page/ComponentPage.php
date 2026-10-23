<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 16/11/2016
 * Time: 17:29
 */

namespace ClinicLE\Page;


use ClinicLE\Application\Resource\ApplicationResources;
use ClinicLE\Common\Exception\UserExceptionInterface;
use ClinicLE\Component\ComponentCollection;
use ClinicLE\Component\Event\BreadcrumbEvent;
use ClinicLE\Component\Event\ComponentEvent;
use ClinicLE\Component\Event\MenuItemEvent;
use ClinicLE\Component\Event\TitleEvent;
use ClinicLE\Component\Exception\SubmitFailException;
use ClinicLE\Content\Render\HtmlElement;
use ClinicLE\Page\Event\PageEvents;
use ClinicLE\Page\Event\SubmitEvent;
use ClinicLE\Page\Event\SubmitEvents;
use ClinicLE\Page\PageResponse\ContentPageResponse;
use ClinicLE\Page\PageResponse\ErrorViewPageResponse;
use ClinicLE\Page\PageResponse\RedirectPageResponse;
use ClinicLE\Page\PageResponse\ViewPageResponse;
use ClinicLE\Page\RequestHandler\StandardRequestHandler;
use ClinicLE\Security\Access;
use ClinicLE\Page\Event\PageAccessEvent;
use ClinicLE\Component\Event\MenuEvent;
use ClinicLE\System\Event\ContentEvent;
use ClinicLE\System\Event\ContentEvents;
use ClinicLE\System\Service\CoreServices;
use Symfony\Component\HttpFoundation\Request;
use CLE;

class ComponentPage extends AbstractPage
{
    function createMenuParams() {
        $mpEvent = new MenuItemEvent();
        $this->getEvents()->dispatch(PageEvents::MENU_PARAMS,$mpEvent);
        return $mpEvent->getParams();
    }
    function createMenuPath() {
        $mpEvent = new MenuItemEvent();
        $mpEvent->setPath($this->getSetting("path"));
        $this->getEvents()->dispatch(PageEvents::MENU_PATH,$mpEvent);
        return $mpEvent->getPath();
    }
    function createMenuAttrs() {
        $mpEvent = new MenuItemEvent();
        $this->getEvents()->dispatch(PageEvents::MENU_ATTRS,$mpEvent);
        return $mpEvent->getAttrs();
    }
    function createMenuIcon() {
        $mpEvent = new MenuItemEvent();
        $this->getEvents()->dispatch(PageEvents::MENU_ICON,$mpEvent);
        return $mpEvent->getIcons();
    }
    function createMenuUri($extraParams = array()) {
        $mpEvent = new MenuItemEvent();
        $this->getEvents()->dispatch(PageEvents::MENU_PATH,$mpEvent);
        $this->getEvents()->dispatch(PageEvents::MENU_PARAMS,$mpEvent);
        return $mpEvent->generateUri($extraParams);
    }
    function processError(UserExceptionInterface $ex) {
        CLE::web()->addError($ex);
        return new ErrorViewPageResponse($ex);
    }
    function processGetMethod(Request $request) {
        $headerHtml = new HtmlElement();
        $contentHtml = new HtmlElement();

        CLE::web()->setContent($contentHtml);

        $add = CLE::sys();

        $this->handleRedirectState($request, $add, $contentHtml);

        $this->generateDirectives($contentHtml);

        $bEvent = new BreadcrumbEvent();
        $this->getEvents()->dispatch(PageEvents::BREADCRUMB,$bEvent);
        $bEvent->generateCrumb($headerHtml);

        $title = $this->createTitle();
        $headerHtml->addTextElement("p",$title,array("class"=>"lead"));

        $this->generateParentMenu($headerHtml,$request);

        $response  = $this->generateMainContent($contentHtml, $request);
        if($response) {
            return $response;
        }

        CLE::svc(ApplicationResources::MODULE_ACTIONS)->generateMessages($headerHtml, $request);

        $fullHtml = new HtmlElement();
        CLE::info("addContentPageJs");
        CLE::svc(ApplicationResources::MODULE_ACTIONS)->addContentPageJs($headerHtml, $this);

        $fullHtml->addHtml($headerHtml);
        $fullHtml->addHtml($contentHtml);
        CLE::svc(ApplicationResources::MODULE_ACTIONS)->addJavascriptToContent($contentHtml,$this);

        $response = new ContentPageResponse($fullHtml,200);

        $redirectState = $add->hasState('redirect');

        if ($redirectState) {
            return $this->createRedirectedContent($redirectState, $request);
        }
        $this->appendToResponse($response);
        return $response;
    }

    function processPostMethod(Request $request)
    {
        $submitEvent = new SubmitEvent($request);

        $this->dispatchSubmitEvents($submitEvent);

        if ($submitEvent->getRedirect()) {
            CLE::info("redirect:",$submitEvent->getRedirect());
            return $this->createRedirectedContent($submitEvent->getRedirect(),$request);
        }
        return $this->processGetMethod($request);
    }

    function createAccess()
    {
        return new Access();
    }

    function createTitle()
    {
        $tEvent = new TitleEvent();
        $this->getEvents()->dispatch(PageEvents::TITLE,$tEvent);
        return $tEvent->getTitle();
    }

    function createMenu() {
        $htmlElement = new HtmlElement();
        $mEvent = new ContentEvent($htmlElement);
        $mEvent->setContent($htmlElement);
        $this->getEvents()->dispatch(PageEvents::MENU,$mEvent);
        $this->getEvents()->dispatch(PageEvents::AFTER_MENU,$mEvent);
        return $mEvent->getContent();
    }

    function init() {
        $compCollection = new ComponentCollection($this);
        $this -> initComponents($compCollection);
        $accessEvent = new PageAccessEvent($this->getAccess());
        $this->getEvents()->dispatch(PageEvents::EXPECTED_PERMS,$accessEvent);
    }

    function initComponents(ComponentCollection $components) {
        $components -> initialise();
        $this -> components = $components;
    }

    function setup() {
        if($this->isSetup()) return;
        $this->getEvents()->dispatch(PageEvents::SETUP);
        $this->setSetup(true);
    }

    function hasComponent($name) {
        return $this -> components -> has($name);
    }

    function html() {
        return CLE::web()->getContent();
    }

    function getComponents() {
        return $this -> components;
    }

    function setComponents($components) {
        $this -> components = $components;
    }

    function getComponent($name) {
        return $this -> components -> get($name);
    }

    function setComponent($name, $component) {
        $this -> components -> add($name, $component);
    }

    function createMenuLabel() {
        $mpEvent = new MenuItemEvent();
        $this->getEvents()->dispatch(PageEvents::MENU_LABEL,$mpEvent);
        return $mpEvent->getLabel();
    }


    function createPermissions() {
        $paEvent = new PageAccessEvent($this->getAccess());
        $this->getEvents()->dispatch(PageEvents::PERMISSIONS,$paEvent);
        return $paEvent->getAccess()->getPermissions();
    }
    function isPathNotPage()
    {
        $mpEvent = new MenuItemEvent();
        $this->getEvents()->dispatch(PageEvents::PATH_NOT_PAGE,$mpEvent);
        return $mpEvent->isPathNotPage();
    }

    function dispatchSubmitEvents(SubmitEvent $submitEvent)
    {
        $db = CLE::db();
        $submitEvent->setAudit(CLE::svc(CoreServices::AUDIT_REPOSITORY));
        $events = $this->getEvents();

        $events->dispatch(SubmitEvents::ACCESS,$submitEvent);
        if($submitEvent->isPropagationStopped()===true) {
            CLE::db('token') -> unregister();
            $submitEvent->stopPropagation();
            return;
        }
        $events->dispatch(SubmitEvents::BEFORE,$submitEvent);
        if($submitEvent->isPropagationStopped()===true) {
            CLE::db('token') -> unregister();
            $submitEvent->stopPropagation();
            return;
        }
        $events->dispatch(SubmitEvents::SUBMIT,$submitEvent);

        if($submitEvent->isPropagationStopped()===true) {
            $db->rollback();
            return;
        }
        $this->setData($submitEvent->getFormValues());

        $db->commitTxn();

        $db->beginTxn();
        CLE::svc(ApplicationResources::MODULE_ACTIONS)->triggerAfterSubmit($submitEvent, $this);

        if ($db->inTxn()) {
            $db->commitTxn();
        }
        if(CLE::sys()->hasState("redirect")) {
            $submitEvent->setRedirect("/".CLE::sys()->hasState("redirect"));
        }
    }
    function createMainContent(ComponentEvent $componentEvent) {
        $events = $this->getEvents();
        $events->dispatch(PageEvents::VIEW_FILE,$componentEvent);
        $events->dispatch(PageEvents::DISPLAY_ACCESS,$componentEvent);
        $events->dispatch(PageEvents::BEFORE_POPULATE,$componentEvent);
        $events->dispatch(PageEvents::POPULATE,$componentEvent);
        $events->dispatch(PageEvents::AFTER_POPULATE,$componentEvent);
        $events->dispatch(PageEvents::BEFORE_ALL_GENERATE,$componentEvent);
        $events->dispatch(PageEvents::BEFORE_GENERATE,$componentEvent);
        $events->dispatch(PageEvents::GENERATE,$componentEvent);
        $events->dispatch(PageEvents::AFTER_GENERATE, $componentEvent);
        $events->dispatch(PageEvents::AFTER_ALL_GENERATE, $componentEvent);
    }
    function generateMainContent(HtmlElement $htmlElement, Request $request)
    {
        $componentEvent = new ComponentEvent($htmlElement,$request);
        $this->createMainContent($componentEvent);

        if($componentEvent->getRedirect()) {
            return new RedirectPageResponse($componentEvent->getRedirect());
        }
        if($componentEvent->getViewFile()) {
            return new ViewPageResponse($componentEvent->getViewFile(),$componentEvent->getData());
        }
    }

    protected function createRedirectedContent($redirectPath,Request $request) {
        $add = CLE::sys();
        $add->setState("redirect",$redirectPath);

        CLE::web()->setContent(new HtmlElement());
        $resolver = CLE::svc(ApplicationResources::PAGE_RESOLVER);
        $redirectPath = $resolver->cleanUrl($redirectPath);
        $request = Request::create($redirectPath);
        $session = $add->getAccessManager()->getSession();
        //CLE::info("set current to redirect ".$redirectPath);

        $response = $resolver->resolvePage($request);
        $currentPage = $add->getAccessManager()->getCurrentPage();
        if($currentPage->isStoredAsCurrent()) {
            $session->setUserData('cle_path', $redirectPath);
        }
        return $response;
    }

    function generateDirectives(HtmlElement $htmlElement) {
        $module = $this->getModule();
        $add = CLE::sys();
        $htmlElement->addElement("input",array("id"=>"cle-sitename","type"=>"hidden","value"=>$add->getConfig()->getSetting
        ("site_name","ADD")));
        $htmlElement->addElement("input",array("id"=>"cle-sitetag","type"=>"hidden","value"=>$add->getConfig()->getSetting
        ("site_tag","LOCAL")));
        $htmlElement->addElement("input",array("id"=>"cle-menu","type"=>"hidden","value"=>$module->getAlias()));
        $htmlElement->addElement("input",array("id"=>"cle-current","type"=>"hidden","value"=>$this->getPim()));
        $beforeRedirectState = $add->hasState('redirect');

        if($beforeRedirectState) {
            $resolver = CLE::svc(ApplicationResources::PAGE_RESOLVER);

            $beforeRedirectState = $resolver->cleanUrl($beforeRedirectState);
            $htmlElement->addElement("input",array("id"=>"cle-redirect","type"=>"hidden",
                "value"=>$beforeRedirectState));
            $htmlElement->setState('redirect',null);
        }
    }
    function generateParentMenu(HtmlElement $htmlElement, Request $request)
    {
        $html = CLE::web()->getContent("topMenu");
        $event = new ContentEvent($html);
        $this->getEvents()->dispatch(ContentEvents::SECTION_MENU,$event);
        $htmlElement->addHtml($html);
    }

    /**
     * @param Request $request
     * @param $add
     * @param $mainHtml
     */
    public function handleRedirectState(Request $request, $add, $mainHtml)
    {
        $redirectState = $add->hasState('redirect');
        $resolver = CLE::svc(ApplicationResources::PAGE_RESOLVER);

        $redirectState = $resolver->cleanUrl($redirectState);
        if ($redirectState) {

            $mainHtml->addElement("input", array("id" => "cle-redirect", "type" => "hidden",
                "value" => $redirectState));
            $add->setState('redirect', null);
        } else {
            $session = $add->getAccessManager()->getSession();
            if ($this->isStoredAsCurrent()) {
                CLE::info("set current to " . $request->getRequestUri());
                $session->setUserData('cle_path', $request->getRequestUri());
            }
        }
        return $redirectState;
    }
}