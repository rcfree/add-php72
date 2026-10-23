<?php



class ADD_Manage_RemoveForm implements ADD_ManageInterface {
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
        $message = "This will purge the '".$options['f']."' observation form and its observations from the following database:\n";
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
		$encForm = $edb->getEncounterFormWithType($options['f']);
		if(!$encForm) throw new Exception("Form with type '".$options['f']."' does not exist");
        $encs = $edb->prepare("select * from encounter where encounter_form_id=?");
		$encs->execute(array($encForm['id']));
		while($enc = $encs->fetch(PDO::FETCH_ASSOC)) {
			$edb->purgeRelatedEncounters(array($enc));
			$log->info("Remove encounter ".$enc['id']." with patient ID ".$enc['patient_id']);
		}
		$edb->purgeFormAndFields($encForm);
		$edb->commitTxn();
    }

    public function getDescription() {
        return "Remove form and data";
    }

    public function getOptsList() {
        return "f:";
    }

}
