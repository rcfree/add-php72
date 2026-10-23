<?php
namespace ClinicLE\Patient\Command;
use CLE;
use ClinicLE\Common\Util;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 06/01/17
 * Time: 16:36
 */

class PatientUpdateCommand extends \ClinicLE\Command\AbstractCommand
{
    function getOptsList()
    {
        return "";
    }

    function getDescription()
    {
        return "Patient update";
    }

    function runCommand(\ClinicLE\Common\Config\AbstractConfig $options)
    {

        $pdb = CLE::db('patient');
        $adb = CLE::db('audit');
        $user = new \stdClass;
        $user->username = "patient_update";

        CLE::sys()->getAccessManager()->setUser($user);
        $stmt = $pdb->execute("select * from patient p where p.deceased!='1' or p.is_deleted!='1'");
        $pmi = \ADD_Util::createPMI(null);

        $statusCheck = new \ADD_Patient_StatusCheck();

        $counter = 0;
        $log = CLE::log();
        while ($dbPatient = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $pmiPatient = null;
            $ident = $dbPatient['identifier'];

            try {
                $pmiPatient = $pmi->get_patient_by_id($ident);
            } catch (Exception $ex) {
                if (preg_match("/identifier .+ was not found/", $ex->getMessage())) {
                    $log->warn("IGNORE: patient ID '$ident' not present in PMI");
                    continue;
                }
            }
            $pagData = $pdb->getPatientDemographics($dbPatient['id']);

            $counter++;
            $errors = array();

            try {
                list($patientChanged, $fieldsChanged) = $statusCheck->changedPatient($pagData, $pmiPatient);
            } catch (\ADD_PatientFieldMissingException $ex) {
                $error = "RESOLVE: " . $ex->getMessage();
                $log->error($error);
                $errors[] = $error;
                continue;
            }
            if (!$patientChanged) {
                $log->debug("IGNORE: patient '$ident' has not changed");
                continue;
            }
            if (array_key_exists("identifier", $fieldsChanged)) {
                $existingPatient = $pdb->getPatientWithIdentifier($fieldsChanged['identifier']);
                if ($existingPatient) {
                    $error = "RESOLVE: patient '$ident' identifier has changed to '" . $fieldsChanged['identifier'] . "' but that patient already exists";
                    $log->error($error);
                    $errors[] = $error;
                    continue;
                }
            }

            CLE::log()->info("UPDATE: patient '$ident' has changed (" . json_encode($fieldsChanged) . ")");

            list($p, $a, $g) = $pdb->updatePatientDemographics($dbPatient['id'], $patientChanged);
            $adb->auditPatient("update", $p, $a, $g);
        }
        $pdb->commitTxn();

    }
}
