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
class ADD_EncounterRecord {
	
}
