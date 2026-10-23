<?php

namespace ClinicLE\Manage\Command;

class ADD_Manage_Remove implements ADD_ManageInterface {
    function manage($options) {
        $add = ADD::sys();
        $config = $add->getConfig()->getSection("Database");
        
        $pdb = ADD::db('patient');
        $edb = ADD::db('encounter');
		$edb->beginTxn();
        $dsn = $config->getSetting('dsn');
        $user = $config->getSetting('user');
        $pass = $config->getSetting('pass');
        $matches = array();
        preg_match("/host\=(.+)\;dbname\=(.+)/",$dsn,$matches);

        list($host,$dbname) = array($matches[1],$matches[2]);
        $tmpfile = tempnam(getcwd(),"add_data_").".sql.gz";
        $message = "This will purge the '".$options['m']."' module and all associated data from the following database:\n";
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
        $log = ADD::log($this);
		$config = new ADD_Config();
		$confPath = $add->getWorkModulePath($options['m'])."/page.ini";
		$log->info("confPath:$confPath");
		$config->loadFile($confPath);
		foreach($config->getSections() as $page) {
			if (!$page->getSetting('encounter') && !$page->getSetting('form')) continue;
			$encForm = $edb->getEncounterFormWithType($page->getSetting('encounter'));
			if(!$encForm) {
				$log->warn("Form with type '".$page->getSetting('encounter')."' does not exist");
				continue;
			}
			$encs = $edb->prepare("select * from encounter where encounter_form_id=?");
			$encs->execute(array($encForm['id']));
			while($enc = $encs->fetch(PDO::FETCH_ASSOC)) {
				$edb->purgeRelatedEncounters(array($enc));
				$log->info("Remove encounter ".$enc['id']." with patient ID ".$enc['patient_id']);
			}
			$edb->purgeFormAndFields($encForm);
			
		}
		$dictConfig = new ADD_Config();
		$dictConfPath = $add->getWorkModulePath($options['m'])."/dictionary.ini";
		$dictConfig->loadFile($dictConfPath);
		$ddb = ADD::db('dictionary');
		foreach($dictConfig->getSections() as $name => $page) {
			$dict = $ddb->getDictionaryByName($name);
			if(!$dict) continue;
			$ddb->execute("delete from dictionary_group where dictionary_id=?",array($dict['id']));
			$ddb->execute("delete from dictionary_item where dictionary_id=?",array($dict['id']));
			$ddb->execute("delete from dictionary where id=?",array($dict['id']));
			$log->info("Deleted dictionary ".$dict['name']);
		}
		$edb->commitTxn();
		exec("rm -rf ".$add->getWorkModulePath($options['m']));
    }

    public function getDescription() {
        return "Remove module";
    }

    public function getOptsList() {
        return "f:";
    }

}
