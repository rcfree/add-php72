<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Research
 *
 * @author rcf8
 */
class ADD_Constants_Research {
	const SENT_CONSENT_FIELD = 'pat_sent_db_consent';
	const ID_FIELD = "ilh_identifier";
	const CURRENT_STATUS_FIELD= "current_status";
	const PATIENT_STATUS_NOT_SET=1;
	const PATIENT_REQUEST_NO_CONTACT=2;
	const PATIENT_ENROL_BUT_NOT_INTERESTED=3;
	const PATIENT_NO_IDENTIFIER=4;
	const PATIENT_UNSUITABLE_RECRUIT=5;
	const PATIENT_UNAVAILABLE_RECRUIT=6;
	const PATIENT_DECEASED=7;
	
	const ADD_TO_RECRUIT_DB_FIELD='add_to_recruit_db';
	const ADD_TO_RESEARCH_DB_FIELD='add_to_research_db';
	const CONSENT_DATE_FIELD="consent_form_date";
}
