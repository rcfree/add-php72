<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RemovePatientsCommand
 *
 * @author rob
 */
namespace ClinicLE\Manage\Command;

use ClinicLE\Command\AbstractCommand;
use ClinicLE\Install\ModuleInstall;
use ClinicLE\Patient\Resource\PatientEvent;
use ClinicLE\Patient\Resource\PatientEvents;
use Symfony\Component\Filesystem\Filesystem;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Common\Config\AbstractConfig;
use ADD;

class RemovePatientsCommand extends AbstractCommand  {
     function runCommand(AbstractConfig $options) {
        $add = ADD::sys();
        $config = $add->getConfig()->getSection("Database");
        $pdb = ADD::db('patient');
		$edb = ADD::db('encounter');
			
        if($options->has("d")) {
			$dsn = $config->getSetting('dsn');
			$user = $config->getSetting('user');
			$pass = $config->getSetting('pass');
			$matches = array();
			preg_match("/host\=(.+)\;dbname\=(.+)/",$dsn,$matches);

			list($host,$dbname) = array($matches[1],$matches[2]);
			$tmpfile = tempnam($add->getPath()."/backup","add_data_").".sql.gz";
			$message = "This will purge all patients and encounter from the following database:\n";
			$message .= $config->getSetting('dsn')."\n";
			$message .= "N.B. A DB backup will be made before this process in $tmpfile\n";
			$message .= "Are you sure you want to do this [y/N]";
			print $message;

			$confirmation = trim( fgets( STDIN ) );
			if ( $confirmation !== 'y' ) {
			// The user did not say 'y'.
				throw new ADD_SystemException("User cancelled procedure");
			}
			system("mysqldump -h$host -u$user -p$pass --opt --single-transaction --quick $dbname | gzip -c > $tmpfile");
		}
		
        $log = ADD::log($this);
        if($options->has("p")) {
			$patients = ADD::db()->fetchAll("select id from patient where identifier=?",array($options->get("p")));
		}
		else {
			$patients = ADD::db()->fetchAll("select id from patient");
		}
		$events = ADD::sys()->getEventDispatcher();
        foreach($patients as $patient) {
			$pid = $patient['id'];
			$event = new PatientEvent($pid);
			$events->dispatch(PatientEvents::BEFORE_PURGE_PATIENT,$event);
			$edb->execute("delete from `domain` where patient_id=?",$pid);
			$edb->execute("delete from `section` where patient_id=?",$pid);
			$msgs = $edb->purgePatientEncounters($pid);
			$log->info("Remove encounters in patient ID ".$pid);
			$pdb->purgePatient($pid);
			$log->info("Remove patient ".$pid);
        }
    }

    public function getDescription() {
        return "Remove all patients and their data";
    }

    public function getOptsList() {
        return "";
    }

}

