<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Install\Event;

/**
 * Description of InstallEvents
 *
 * @author rob
 */
class InstallEvents {
	const BEFORE_DICTIONARIES = "installEvents.beforeDictionaries";
	const BEFORE_FORMS = "installEvents.beforeForms";
	const AFTER_FORMS = "installEvents.afterForms";
	const AFTER_SYNC = "installEvents.afterSync";
	const BEFORE_RUN = "installEvents.beforeRun";
	const INSTALL_FORM = "installEvents.installForm";
}
