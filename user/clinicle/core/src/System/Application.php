<?php

namespace ClinicLE\System;
use ClinicLE\Common\Exception\NullValueException;
use ClinicLE\System\Event\ContentEvent;
use ClinicLE\System\Event\ContentEvents;
use ClinicLE\System\Service\CoreServices;

use ADD;
class Application {

    private $_contentClass = null;
    private $_javascriptList = array();
    private $_moduleScripts=array();
    private $_content = array();
    private $_title = "Untitled";
	private $notices;
	private $_javascriptFiles=array();
	
    function __construct($contentClass,Notices $notices) {
        if (!$contentClass) throw new NullValueException("Content class can not be null in \ADD_Web");
        $this->_contentClass = $contentClass;
		$this->notices = $notices;
		
    }
	
    function addJavascript($javascript, $location = "document") {
        if (!$javascript)
            return;
        $this->_javascriptList[$location][] = $javascript;
    }
	function clearJavascript() {
		$this->_javascriptList=array();
	}
    function getJavascript($location = null) {
        if (!$location)
            return $this->_javascriptList;
        return isset($this->_javascriptList[$location]) ? $this->_javascriptList[$location] : null;
    }
	function getJavascriptFiles() {
		return $this->_javascriptFiles;
	}
	function addPageJavascriptFile($page) {
		$jsFile = $page->getSetting('javascript',array());
		$jsFiles = is_array($jsFile) ? $jsFile : array($jsFile);
		$filePath = $page->getFilePath();
		$filePath = preg_replace('/.+\/modules\//',"",$filePath);
		$jsFiles = array_map(function($value) use($page, $filePath) {
			return "/module/".$filePath."/".$value;
		},$jsFiles);
		$this->_javascriptFiles=array_merge($this->_javascriptFiles,$jsFiles);
	}
	function addJSAssign($assignClass,$field,$fieldName=null, $attrs=null) {
		if (!$fieldName) $fieldName = $field->getName();
		if(!$attrs) $attrs = $field->getSettings();
		$opts = json_encode($attrs);
		$this->addJavascript("ADD.assign('$assignClass','$fieldName',$opts)");
	}
	public function getContent($name = "main",$contentClass=null) {
    	if (!$contentClass) $contentClass = $this->_contentClass;
        if (isset($this->_content[$name])) {
            return $this->_content[$name];
        }
        $this->_content[$name] = $this->newContent($this->_contentClass);
        return $this->_content[$name];
    }
	public function setContent($content,$name="main") {
		$this->_content[$name]=$content;
	}
    public function newContent($contentClass=null) {
        if (!$this->_contentClass) throw new \ADD_SystemException("Content class has not been defined");
        $contentClass = $contentClass ? $contentClass : $this->_contentClass;
        return \ADD_Util::loadClass("\ADD_Content_" . $contentClass);
    }
    
    public function setContentClass($contentClass) {
        $this->_contentClass=$contentClass;
    }
    
	public function getContentClass() {
		return $this->_contentClass;
	}
	function getTitle() {
		return $this->_title;
	}
	function setTitle($title) {
		$this->_title = $title;
	}
	
    function createOutput($options) {
    	 $add = \ADD::sys();
        $db = \ADD::db();
        $method = \ADD_Util::val("method", $options, "GET");
        $path = \ADD_Util::val("path", $options, \ADD::sys()->assertConfig('default_path'));
        $values = \ADD_Util::val("values", $options, array());
        
        if ($add->getParam('clear')) {
            $add->clearCache();
        	$this->addMessage("Cache cleared");
	}
        
        try {
            $am = $add->getAccessManager();
            $add->setPatientId($add->getParam('pid'));
            $currPage = $am->getCurrentPage($path);
           
            if (!$currPage) return array("view"=>"not_found","status"=>404);
            $currPage->checkPublicPage();
           
		   	$minifier = ADD::sys()->getService(CoreServices::MINIFIER);
			
			
            $mods = $add->getModules();
			
            $options = array();
            
            $db->beginTxn();
			$currPage->setupComponents();
             
            if (!$currPage->isWebContent()) {
            	$ct = $currPage->createContentType();
            	$this->setContentClass($ct);
           
                $content = $currPage->createOutput($values);
                if ($db->inTxn()) $db->commitTxn();
				
				return array("content" => $content);
            } 
            
            $this->setContentClass("Html");
			
			$minifier->addModuleLevelJs($mods);
			    
            $minifier->rebuild();
			
			$this->addPageJavascriptFile($currPage);

            $this->createSideCommonOptions();
			if(!$currPage->isFormOnly()) {
				$currPage->createBreadcrumb();
				$title = $currPage->createTitle();
				$this->setTitle($title);

				$this->createBaseContent();
			}
            if ($method == "POST") {
                $result = $currPage->submitForm($values);
                if (count($currPage->getErrors()) == 0) {
                    $db->commitTxn();

                    $db->beginTxn();
                    $this->afterSubmit($currPage, $values);
                    if ($db->inTxn()) $db->commitTxn();
					if (isset($result['redirect'])) {
                        return array(
                            "redirect" => $result['redirect'],
                            "messages" => $currPage->getMessages(),
                            "warnings" => $currPage->getWarnings(),
							"errors"=>$currPage->getErrors()
                        );
                    }
                }
                $currPage->setData($values);
            }
            
            $currPage->createContent();
			
            
			if(!$currPage->isFormOnly()) {
				$this->createMenus();
			}
            $viewFile = $currPage->createViewFile();
                                
            if ($add->hasState('redirect')) {
                if ($db->inTxn()) $db->commitTxn();
				
                return array(
                    "redirect" => $add->hasState('redirect'),
                    "messages" => $currPage->getMessages(),
                    "warnings" => $currPage->getWarnings()
                );
            }
			
            if ($db->inTxn()) $db->commitTxn();
			
            if ($viewFile) return array("view"=>$viewFile);	
      
        } catch (\Exception $ex) {
            $this->addError($ex);
            
            if ($db->inTxn()) $db->rollbackTxn();
            return array("view"=>\ADD::web()->getThemeView("content"));	
       }

    }
    function createSideCommonOptions() {
        $sideCommon = $this->getContent("sideOptions");
        $sideCommon->startElement("div",array("class"=>"btn-group","style"=>"top:12px"));
        $contentEvent = new ContentEvent($sideCommon);
        \CLE::sys()->getEvents()->dispatch(ContentEvents::SIDE_COMMON_OPTIONS,$contentEvent);
        $sideCommon->endElement("div");
    }
    function createMenus() {
    	$add = \ADD::sys();
        $mods = $add->getModules()->asArray();
        $menus = array();
		usort($mods,function($a,$b) { 
            return strcmp($a->getAlias(), $b->getAlias());
        });
		foreach ($mods as $mod) {
        	//if($mod->getAlias()!="acute") continue;
        	foreach ($mod->getTopPages() as $page) {
                
		try {
                        $menuHtml = $page->createMenu();
                }
                catch(Exception $ex) {
                        if(!$menuHtml) $menuHtml = \ADD::web()->newContent();
                        $menuHtml->addTextElement("p","Unexpected menu error:".\ADD_Web::errorMessage($ex));
                }

                if ($menuHtml && $menuHtml->output()) $menus[$mod->getName().":".$page->getName()]=$menuHtml;
				

        
            }

        }
        if (count($menus)==0) return;
        
        $finalMenu = \ADD::web()->getContent("menu");
        $finalMenu->startElement("div",array("id"=>"options"));
        $finalMenu->addTextElement("div",$add->getConfig()->getSetting('patient_menu_text',"Click on a heading below to view patient options."),array("class"=>"muted"));
        foreach($menus as $pim => $menu) {
            if (!$menu->output()) continue;
			$page = $add->getPageFromPim($pim);
			$mod = $page->getModule();
			$menuRefresh = $mod->getSetting('menu_refreshable');
			
			
			$domainAttrs = array("id"=>$mod->getAlias()."-domain-menu-app");
			
			if($menuRefresh) {
				$currentPage = $add->getAccessManager()->getCurrentPage();
				$currentPim = $currentPage->getModule()->getName().":".$currentPage->getName();
				$domainAttrs = $domainAttrs + array(
					'ng-app'=>"",
					'ng-controller'=>"MenuController",
					'add-pid'=>$add->getPatientId(),
					'add-eid'=>$add->getParam('eid'),
					'add-pim'=>$pim,
					'add-current'=>$currentPim
				);
			}
			$finalMenu->startElement("div",$domainAttrs);
			
			$finalMenu->addHtml($menu);
			$finalMenu->endElement("div");
        }
		
		$finalMenu->endElement("div");
    }
    
    function createBaseContent() {
        $mods = \ADD::sys()->getModules();
        foreach ($mods as $mod) {
            foreach ($mod->getPages()->asArray() as $pageName=>$page) {
                $page->createBaseContent();
            }
        }
        
    }

    function afterSubmit($submitPage) {
        $add = \ADD::sys();
        $subTxn = false;


        try {
            foreach ($add->getModules() as $module) {
                foreach ($module->getPages() as $page) {
                    $page->afterSubmit($submitPage);
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    function errorMessage($ex) {
		return $this->notices->errorMessage($ex);
        $add = \ADD::sys();
        $message = $ex->getMessage();
        if ($add->config('nocatch'))
            throw $ex;
        if ($add->config('stacktrace'))
            $message.="<br/>Stacktrace:<pre>" . $ex->getTraceAsString() . "</pre>";
        return $message;
    }

    function addWarning($warning, $noDuplicates = null) {
		$this->notices->addWarning($warning,$noDuplicates);
    }

    function addError($error, $noDuplicates = null) {
		$this->notices->addError($error,$noDuplicates);
    }
	
    function addMessage($message, $noDuplicates = null) {
        $this->notices->addMessage($message,$noDuplicates);
    }

    function setMessages($messages = null) {
		$this->notices->setMessages($messages);
    }

    function setWarnings($warnings = null) {
        $this->notices->setWarnings($warnings);
    }

    function setErrors($errors = null) {
		$this->notices->setError($errors);
    }

    function getWarnings() {
        return $this->notices->getWarnings();
    }

    function getErrors() {
        return $this->notices->getErrors();
    }

    function getMessages() {
        return $this->notices->getMessages();
    }
/*
    function createContentType(\ADD_Page $page = null) {
        if (!$page) {
            $page = \ADD::sys()->getCurrentPage();
        }
        $contentType = "Html";
        foreach($page->getComponents() as $name => $component) {
            $component->contentType($contentType);
        }
        $this->_contentType = $contentType;

        return $contentType;
    }
 * 
 */
    function reset() {
        $this->notices->reset();
		
    }
    
    function clear() {
        $this->_content=array();
    }

	function doUserLogin(\ClinicLE\Security\AccessManager $accessManager) {
        $mods = \ADD::sys()->getModules();
        foreach ($mods as $mod) {
            foreach ($mod->getPages() as $page) {
                $page->userLogin($accessManager);
            }
        }
    }
	
	function doUserLogout(\ClinicLE\Security\AccessManager $accessManager) {
        $mods = \ADD::sys()->getModules();
        foreach ($mods as $mod) {
            foreach ($mod->getPages() as $page) {
                $page->userLogout($accessManager);
            }
        }
    }
     function getThemeView($fileName, $asFilePath=false) {
        $add = \ADD::sys();
        $customTheme = $add->config("theme_module");
        if($customTheme) {
            $fileName = "module/".$customTheme."/".$fileName;
        }
        
        return $asFilePath ? \ADD::sys()->getWebAppPath("views/".$fileName) : $fileName;
    }
    
    function getThemeWebPath() {
        $add = \ADD::sys();
        $customTheme = $add->config("theme_module");
        if($customTheme) {
            return "/module/".$customTheme;
        }
        else {
            return "/";
        }
    }
}
