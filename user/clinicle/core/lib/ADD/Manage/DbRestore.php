<?php
/**
 * Description of Load
 *
 * @author rcf8
 */


class ADD_Manage_DbRestore implements ADD_ManageInterface
{
    function getOptsList() {
        return "u:tq";
    }
    function getDescription() {
        return "Database restore";
    }

    function manage($options) {
    	$add = ADD::sys();
		$log = ADD::log($this);
		$db = ADD::db();
		$create=false;
		$restConf = $add->getConfig()->getSection("DbRestore");
		if(!$restConf) {
			$restConf = new \Clinicle\Common\Config\ArrayConfig(array());
		}
		try {
			@$db->getConn();
		}
		catch(PDOException $ex) {
			if(preg_match("/Unknown database/",$ex->getMessage())) {
				$log->warn("Creating new database");
				$create=true;
			}
		}
		
		$config = $add->getConfig()->getSection('Database');
		$now = new DateTime();
		
		$dbPath = ADD_Util::val("t",$options,$now->format("Y/m/d/H").$restConf->getSetting('backup_pattern',"00"));
		
		echo "This WILL drop the existing ADD database (".$config->getSetting('dsn').") and restore  from '$dbPath'\nAre you sure (Y/n)?\n";
        if(!isset($options['q'])) {
	        $value = ADD_Util::inputLine(1);
	        if ($value!="Y") throw new Exception("User cancelled operation");
		}
		if(!isset($options['p'])) {
			echo "Enter the DB password for this user (".$options['u']."):\n";
			
			$pass = self::getPassword(true);
		}
		else {
			$pass = $options['p'];
		}
		
        if (!$pass) throw new Exception("User cancelled operation");
		echo "\n";
		
		if (!$create) system("mysqladmin -u".$options['u']." -p$pass -h ".$db->getDbHost().(isset($options['q']) ? " -f" : "")." drop ".$db->getDbName());
		
		system("mysqladmin -u".$options['u']." -p$pass -h".$db->getDbHost()." create ".$db->getDbName());
		$backupPath = $restConf->getSetting('backup_path',"/media/uhlbackup/live_db/");
		$backupDbName = $restConf->getSetting('backup_db',"add_data_live");
		
		$fullDbPath = $backupPath."/".$dbPath;
		
		chdir($fullDbPath);
		echo "Restoring file: ".$fullDbPath."/$backupDbName.sql.gz\n";
		if(!file_exists($backupDbName.".sql.gz")) {
			throw new Exception("Database backup file '$backupPath/$backupDbName.sql.gz' does not exist");
		}
		$sys = "zcat $backupDbName.sql.gz | mysql -u".$options['u']." -p$pass -h".$db->getDbHost()." ".$db->getDbName();
		system($sys);
		
	}
	static function getPassword($stars = false)
	{
	    // Get current style
	    $oldStyle = shell_exec('stty -g');
	
	    if ($stars === false) {
	        shell_exec('stty -echo');
	        $password = rtrim(fgets(STDIN), "\n");
	    } else {
	        shell_exec('stty -icanon -echo min 1 time 0');
	
	        $password = '';
	        while (true) {
	            $char = fgetc(STDIN);
	
	            if ($char === "\n") {
	                break;
	            } else if (ord($char) === 127) {
	                if (strlen($password) > 0) {
	                    fwrite(STDOUT, "\x08 \x08");
	                    $password = substr($password, 0, -1);
	                }
	            } else {
	                fwrite(STDOUT, "*");
	                $password .= $char;
	            }
	        }
	    }
	
	    // Reset old style
	    shell_exec('stty ' . $oldStyle);
	
	    // Return the password
	    return $password;
	}

}
?>