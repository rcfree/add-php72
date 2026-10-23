<?php
/**
 * EncounterRecord makes it easier to access and manage encounter record data in the database.
 * 
 * The EncounterRecord makes it provides a wrapper for DB functions which create, edit, delete etc. encounters and their associated observation data.
 * It is used alongside the EncounterManager to create and retrieve child encounter records.
 * The preferred approach is to use EncounterManager to create EncounterRecord instances.
 * 
 * @package Main
 * @example $em = new EncounterManager();
 * @example $er = $em->record();
 * @example $er->setId(3);
 * @example if ($er->exists()) $er->store(array("test"=>"1"));
 * @version 2.00
 * @since 14/03/2014
 * @author Rob Free
 */
namespace ClinicLE\Data\Record;
use ClinicLE\System\Service\CoreServices;

use ADD;
class SectionRecord extends AbstractRecord {
	function retrieve($force=false) {
            return ADD::sys()->getService(CoreServices::SECTION_REPOSITORY)->retrieveSection($this);
            return $this;
	}
	function store($obsValues = null) {
            ADD::sys()->getService(CoreServices::SECTION_REPOSITORY)->storeSection($this);
            return $this;
        }
	function getParent() {
		return null;
	}

	
	function audit($action) {
		//ADD::db('audit') -> auditEncounter($action, $this -> _encounter);
	}
   
	
    function getObsFields() {
		return array();
	}
	
	function delete() {
            return ADD::sys()->getService(CoreServices::SECTION_REPOSITORY)->deleteSection($this);
	}
	
	function purge() {
		
        }
		
		/**
		 * Required by some legacy code. Always returns empty array
		 * @deprecated
		 * @return array
		 */
		function getObsValues() {
			return array();
		}

}
