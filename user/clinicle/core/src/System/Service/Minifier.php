<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\System\Service;
use MatthiasMullie\Minify;
use ClinicLE\System\Environment;
use ClinicLE\System\Service\Minify\FullJS;
use DateTime;

class Minifier {
	private $css = array(
		'/bootstrap/css/bootstrap.css',
		'/bootstrap/css/bootstrap-responsive.css',
		'/css/jquery-ui.css'
	);
	private $platformJs = array(
		'/js/external/jquery.js',
		'/js/external/jquery.dirtyforms.js',
		'/js/external/idle-timer.js',
		'/bootstrap/js/bootstrap.min.js',
		'/js/external/jquery.maskedinput-1.3.min.js',
		'/js/external/jquery-ui-1.10.4.custom.min.js',
		'/js/external/angular.min.js',
		'/js/external/angular-sanitize.min.js',
        '/js/external/native.history.js',
		'/js/add/base.js',
		'/js/add/form.js',
		'/js/add/legacy.js',
		'/js/add/user.js',
		'/js/add/notice.js',
        '/js/add/noticePanel.js',
		'/js/add/util.js',
		'/js/add/feature/core.js',
		'/js/add/feature/validate.js',
		'/js/add/feature/required.js',
		'/js/add/feature/modal.js',
		'/js/add/feature/ajax.js',
		'/js/patientlookup/patientlookup.js',
		'/js/add/patientlookup-pmi.config.js',
		'/js/external/underscore-min.js',
		'/js/external/URI.min.js'
	);
	private $disableMinify=false;
	private $forceMinify=false;
	private $js=array();
	private $currentWeight = 1;
	private $fullFiles;
	public function __construct(Environment\AbstractEnvironment $env) {
		$this->disableMinify = $env->getConfig()->getSetting('disable_minify');
		$this->fullFiles = $env->getConfig()->getSetting('minify_full');
		$this->forceMinify = $env->getConfig()->getSetting('force_minify');
		$this->targetJs = $env->getConfig()->getSetting('minified_js','js/clinicle.min.js');
		$this->targetCss = $env->getConfig()->getSetting('minified_css','css/clinicle.min.css');
		$this->basePath = $env->getPath()."/vendor/clinicle/web/public";
		if($env->getConfig()->getSetting('theme_module')) {
			$this->addCss("/module/".$env->getConfig()->getSetting('theme_module')."/css/theme.css");
		}
		else {
			$this->addCss("/css/add.css");
		}
		
	}
	
	function addCss($css) {
		$this->css[] = $css;
	}
	function putJs($number,$js) {
		if(isset($this->js[$number])) {
			$number++;
			return $this->putJs($number,$js);
		}

		$this->js[$number] = $js;
	}
	function addModuleLevelJs(\ClinicLE\Module\ModuleCollection $modules) {
		foreach($modules->asArray() as $modName => $module) {
			$jsFile = $module->getSetting('javascript');
			$jsWeight = $module->getSetting('javascript_weight');
			if(!$jsFile) continue;
			
			$this->addModuleScript($jsFile,$jsWeight);
		}
	}
    function addModuleLevelCss(\ClinicLE\Module\ModuleCollection $modules) {
        foreach($modules->asArray() as $modName => $module) {
            $cssFile = $module->getSetting('css');
            if(!$cssFile) continue;

            $this->addCss($cssFile);
        }
    }
	private function addPageScript($jsFile, $page) {
		$jsFile=$page->getOrigPath()."/".$jsFile;
        $scriptPath = "/module/$jsFile";
		return $this->addModuleScript($jsFile);
    }
	private function addModuleScript($jsFile, $jsWeight = null) {
		if(!is_array($jsFile)) {
            $jsFile=array($jsFile);
        }
		$useIndexAsWeight = !isset($jsFile[0]);
		
        foreach($jsFile as $jsPossWeight => $jsFileSingle) {
			$scriptPath = "/module/$jsFileSingle";
        
		  if(in_array($scriptPath,$this->js)) return;
		  if($jsWeight === null) {
			$jsWeight = $useIndexAsWeight ? $jsPossWeight : $this->currentWeight;
		  }
		  	
          $this->putJs($jsWeight,$scriptPath);
        }
    }
	function addPageJavascript(\ADD_Page $page) {
		$jsFiles = $page->getSetting('javascript');
        if (!$jsFiles) {
        	return;
		}
		if(!is_array($jsFiles)) $jsFiles=array($jsFiles);
        foreach($jsFiles as $jsFile) {
        	$this->addModuleScript($jsFile,$currPage);
        }
	}
	 function isAnyFileNewer($files, $compareFile) {
	 	if($this->forceMinify) {
	 		return true;
		}
	 	if(!file_exists($this->basePath."/".$compareFile)) {
			return true;
		}
		
	 	$cmpFileTime = filemtime($this->basePath."/".$compareFile);
		foreach($files as $file) {
			$dateMod = filemtime($this->basePath.$file);
			$dateCmp = $dateMod - $cmpFileTime;
			if($dateCmp>0) {
				return true;
			}
		}
		return false;
	 }
    function rebuild() {
    	if($this->disableMinify) return;
		
		if($this->isAnyFileNewer($this->css, $this->targetCss)) {
	    	$minifyCss = new Minify\CSS();
			foreach($this->css as $css) {
				$minifyCss->add($this->basePath.$css);
			}
			$minifyCss->minify($this->basePath."/".$this->targetCss);
		}
		
		if($this->isAnyFileNewer(array_merge($this->platformJs,array_values($this->js)), $this->targetJs)) {
			$minifyJs = $this->fullFiles ? new FullJS() : new Minify\JS();
			foreach($this->platformJs as $js) {
				$minifyJs->add($this->basePath.$js);
			}
			ksort($this->js);
			foreach($this->js as $weight => $js) {
				if(!file_exists($this->basePath.$js)) {
					throw new \Exception("File to minify '".$this->basePath.$js."' does not exist");
				}
	 		
				$minifyJs->add($this->basePath.$js);
				
			}
			$minifyJs->minify($this->basePath."/".$this->targetJs);
		}
		
	}
	
}
