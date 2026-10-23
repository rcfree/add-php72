<?php

namespace ClinicLE\Security;
use ADD;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AccessManager
 *
 * @author rcf8
 */
class AccessManager implements \ADD_AccessManager {
    private $_currentPage = null;
    private $_user = null;
    private $_admin = false;
	private $_loggedIn = false;
	private $_context;
	
    function getAllPermissions() {
        $uniquePerms = array();
        $actualPerms = array();
        foreach (ADD::sys()->getModules() as $name => $mod) {
            if ($mod->getExtends())
                continue;
            foreach ($mod->getPagesInStructure() as $page) {
                $perms = $page->getExpectedPermissions();

                foreach ($perms as $perm) {
                    $uniquePerms[$mod->getAlias()][$perm] = 1;
                    $actualPerms[$mod->getAlias()][$page->getName()][$perm] = 1;
                }
            }
        }
        return array($uniquePerms, $actualPerms);
    }
	
    function populateUserGroups($groups,$isAdmin) {
        $groupByName = array();
        if ($groups) {
            foreach ($groups as $group) {
                $groupByName[$group->name] = true;
            }
        }
        foreach (ADD::sys()->getModules() as $name => $mod) {
            if ($mod->getExtends())
                continue;
            foreach ($mod->getPagesInStructure() as $page) {
                $pagePerms = array();
                foreach ($page->getExpectedPermissions() as $perm) {
                    $expPerm = $perm . "_" . $mod->getAlias() . "_" . $page->getName();
                    if (isset($groupByName[$expPerm]) || $isAdmin) {
                        $pagePerms[$perm] = true;
                    }
                }
                $page->setPermissions($pagePerms);
            }
        }
        $this->_admin = $isAdmin;
    }
    function isAdmin() {
        return $this->_admin;
    }
	
	public function getAvailChildPages($page, $checkPermissions=array("create","view","edit")) {
		$availPages = array();
		
        foreach ($page -> getChildPages() as $cpage) {
			if ($cpage -> hasAnyPermissions($checkPermissions)) {
				$availPages[] = $cpage;
			}
		}
		return $availPages;
	}
	
    function getCurrentPage($urlPath = null) {
		if ($urlPath === null) {
            if ($this->_currentPage)
                return $this->_currentPage;
            return null;
        }
        if (substr($urlPath, 0, 1) == '/')
            $urlPath = substr($urlPath, 1);
        if (strlen($urlPath) == 0)
            $urlPath = ADD::sys()->assertConfig('default_path');

        $pagesInMods = ADD::sys()->getModules()->getAllPages();
        foreach ($pagesInMods as $moduleName => $pages) {
            foreach ($pages as $name => $page) {
                if ($urlPath == $page->createMenuPath()) {
                    if ($page->isPathNotPage())
                        continue;
                    $this->setCurrentPage($page);
                    return $page;
                }
            }
        }
    }
    function setCurrentPage($page) {
        $superMod = $page->getModule()->getSuperModule();
        ADD::sys()->setState('openMenu', $superMod->getName());
		$this->_currentPage = $page;
    }
    function isCurrentPage($page) {
        if ($this->getCurrentPage() === $page)
            return true;
    }
    
    function setUser(&$user) {
		$this->_user = $user;
    }

    function getUser() {
            return $this->_user;
    }
	
	function setSessionData($sessionData) {
		$this->_sessionData = $sessionData;
	}
	function getSession() {
        return $this->_context->session;
    }
	function getSessionData() {
		return $this->_sessionData;
	}
	
	function setLoggedIn($loggedIn) {
    	$this->_loggedIn=$loggedIn;
	}
	
	function isLoggedIn() {
		return $this->_loggedIn;
	}
	function getContext() {
		return $this->_context;
	}
	function setContext($context) {
		$this->_context = $context;
	}
}
