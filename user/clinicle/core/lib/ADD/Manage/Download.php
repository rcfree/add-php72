<?php
/**
 * Description of Load
 *
 * @author rcf8
 */


class ADD_Manage_Download implements ADD_ManageInterface
{
    function getOptsList() {
        return "m::ic";
    }
    function getDescription() {
        return "Download module";
    }
    function manage($options) {
    	$add = ADD::sys();
		$log = ADD::log($this);
		$modName = ADD_Util::val('m',$options);
		
    	$modPath = $add->config('source_module_path')."/".$modName;
		
		if (isset($options['c'])) {
			if(file_exists($modPath)) exec("rm -rf $modPath");
		}
		if (file_exists($modPath)) {
				chdir($modPath);
				$log->info("Pulling latest version of '".$modName."' module from ADD repo");
				exec("hg pull https://bitbucket.org/leicrespbru/add_".$modName." || echo \"Unable to pull module '".$modName."' from ADD repo\"");
		}
		else {
			chdir($add->config('source_module_path'));
			mkdir($modName);
			$log->info("Cloning latest version of '".$modName."' module from ADD repo");
			exec("hg clone https://bitbucket.org/leicrespbru/add_".$modName." ".$modName." || echo \"Unable to clone module '".$modName."' from ADD repo\"");
		}
		chdir($add->getPath());
		chdir($modPath);
		exec("hg update || echo \"Unable to update module '".$modName."'\"");
	}
}
?>