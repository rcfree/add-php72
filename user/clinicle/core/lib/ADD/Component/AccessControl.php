<?php

class ADD_Component_AccessControl extends ADD_Component {
	
	function displayAccess() {
		$add = ADD::sys();

		$page = $this->_page;
		if(!$page->hasComponent('Encounter')) {
			if (!$page->getPermission('view')) {
				throw new ADD_NoPermissionToAccessException("You do not have permission to view this page.");
			}
			return;
		}
		$encounterRecord = $page->getEncounterRecord($add->getParam('eid'));

		if ($encounterRecord && !$encounterRecord->exists()) {
			$this->ensureCreateAccess();
		}
		else {
			$this->ensureViewOrEditAccess();
		}          
	}
	
	private function ensureCreateAccess() {
            $add = ADD::sys();
            $page = $this->_page;
            
            if ($page->getPermission('create')) return;
            if (!$page->getSetting('auto-create')) throw new ADD_NoPermissionToAccessException("You do not have permission to create this record.");
            $add->setState('readOnly',true);
            $page->addWarning("You only have permission to view this record. You will not be able to make changes.");

            return;
        }
        
	private function ensureViewOrEditAccess() {
		$add = ADD::sys();
		$page = $this->_page;

		if ($page->getExpectedPermissions()==array('view')) {
			if (!$page->getPermission('view')) {
				throw new ADD_NoPermissionToAccessException("You do not have permission to view this record.");
			}
			return;
		}

		if ($page->getPermission('edit')) return;

		if (!$page->getPermission('view')) throw new ADD_NoPermissionToAccessException("You do not have permission to view or edit this record.");

		$add->setState('readOnly',true);
		$page->addWarning("You only have permission to view this record. You will not be able to make changes.");

    }
	
	function submitAccess($values) {
		$page = $this->_page;
        $add = ADD::sys();
        $pageTitle = $page->getSetting('title');

        if (!$page->hasComponent('Encounter')) {
            $this->guardAgainstNoPermissionSubmit($page, $pageTitle);
            return;
        }

        list($create,$edit,$view) = $page->getPermissions(array("create","edit","view"));

        $er = $page->getEncounterRecord($add->getParam('eid'));
        $encounterExists = $er->exists();

        $this->guardAgainstSubmitNewWithNoCreatePermission($create, $encounterExists, $pageTitle);

        if ($create && !$encounterExists) return;
        if ($edit && $encounterExists) return;

        $this->guardAgainstSubmitWhenOnlyViewPermission($view, $pageTitle);

        $this->guardAgainstSubmitOldWhenNoEditPermission($edit, $encounterExists, $pageTitle);
    }
	
	function expectedPermissions(&$expPerms) {
        $page = $this->_page;
        $acp = $page->getSetting('AccessControl.permissions');
		if ($acp) {
			foreach(explode(" ",$acp) as $perm) {
				$expPerms[]=$perm;
			}
		}
	}

   
    
    /**
     * @param $page
     * @param $title
     * @throws ADD_NoPermissionToAccessException
     */
    private function guardAgainstNoPermissionSubmit($page, $title)
    {
        if (!$page->hasAnyPermissions(array('create', 'edit', 'view'))) {
            throw new ADD_NoPermissionToSubmitException("Sorry you are not allowed to change '$title' data");
        }
    }

    /**
     * @param $create
     * @param $encounterExists
     * @param $pageTitle
     * @throws ADD_NoPermissionToSubmitException
     */
    private function guardAgainstSubmitNewWithNoCreatePermission($create, $encounterExists, $pageTitle)
    {
        if (!$create && !$encounterExists) throw new ADD_NoPermissionToSubmitException("Sorry you do not have permission to create '$pageTitle' data");
    }

    /**
     * @param $view
     * @param $pageTitle
     * @throws ADD_NoPermissionToSubmitException
     */
    private function guardAgainstSubmitWhenOnlyViewPermission($view, $pageTitle)
    {
        if ($view) {
            throw new ADD_NoPermissionToSubmitException("Sorry you do not have permission to edit '$pageTitle' data - you only have permission to view");
        }
    }

    /**
     * @param $edit
     * @param $encounterExists
     * @param $pageTitle
     * @throws ADD_NoPermissionToSubmitException
     */
    private function guardAgainstSubmitOldWhenNoEditPermission($edit, $encounterExists, $pageTitle)
    {
        if (!$edit && $encounterExists) throw new ADD_NoPermissionToSubmitException("Sorry you do not have permission to edit '$pageTitle' data");
    }


}