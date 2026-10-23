<?php

/*
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

/**
 * Description of Patient
 *
 * @author rcf8
 */
class ADD_Page_Api_Patient extends ADD_Page_Api {
    function apiOutput() {
        $add = ADD::sys();

        $id = $add->assertParam('identifier');
        $pdb = ADD::db('patient');
        $patient = $pdb -> getPatientWithIdentifier($id);
        if (!$patient) return array();
        return $patient;
    }
}
