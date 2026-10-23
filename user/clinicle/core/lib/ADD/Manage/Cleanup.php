<?php
/**
 * Description of Load
 *
 * @author rcf8
 */

use ClinicLE\System\Service\CoreServices;
class ADD_Manage_Cleanup implements ADD_ManageInterface
{   
    function getOptsList() {
        return "";
    }
    function getDescription() {
        return "Cleanup";
    }
    function manage($options) {
    	$log = ADD::log($this);
		
		$ffinder = ADD::sys()->getService(CoreServices::FILEFINDER);
		$ffinder->in(ADD::sys()->getPath()."/tmp");
		$ffinder->date("before yesterday");
		$ffinder->files();
		
		$fileCount = 0;
		foreach($ffinder as $file) {
			unlink($file->getPathname());
			$fileCount++;
		}
		$log->info("Cleaned up ".$fileCount." temp file(s)");
		
		$db = ADD::db('encounter');
		$timestamp = time() - (24*60*60);
	
		$db->execute("delete from submit_token where timestamp < ?",array($timestamp));
		
		$log->info("Deleted submit tokens from before today"); 
		
		$db->execute("delete from recent_patient where patient_id is null");
			
		$recentPatientUsers = $db->fetchAll("select * from recent_patient group by username");
		
		foreach($recentPatientUsers as $recentPatientUser) {
			$recentPatients = $db->fetchAll("select * from recent_patient where username=? group by patient_id order by timestamp desc limit 0,30",$recentPatientUser['username']);
			$recentCount = count($recentPatients);
			if($recentCount==0) continue;
			$log->info("Clean up recent patients for user ".$recentPatientUser['username']);
			$db->execute("delete from recent_patient where timestamp<? and username=?",array($recentPatients[$recentCount-1]['timestamp'],$recentPatientUser['username']));
		}
    }
}
