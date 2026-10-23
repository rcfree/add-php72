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

class ADD_Page_Api_Research_GlobalStructure extends ADD_Page_Api_Research {
	private $ignoreModules = array("cap", "research", "patient", "qol");
    
	function apiOutput() {
    	$add = ADD::sys();
        $log = ADD::log($this);

        $add->initModules();
        $mods = $add->getModules(true);

        $mcount=1;
		
		$structure = array();
        foreach ($mods as $mod) {
            $lcount=0;
            if (in_array($mod->getName(), $this->ignoreModules)) continue;

            $tpages = $mod->getTopPages();
			$modName = $mod->getAlias();
            foreach ($tpages as $tpage) {
                if (!$tpage->getSetting('encounter')) continue;

                $opath = array(sprintf("%03d",$mcount) . "." . $tpage->getTitle());
				//$oinfo = array("title"=>$tpage->getTitle(),"encounter"=>$tpage->getSetting('encounter'));
                $structure[]=array("path"=>$opath,"name"=> $modName.".".$tpage->getName());
                $pcount = 1;
                $lcount++;
                foreach ($tpage->getChildPages() as $page) {
                    if (!$page->getSetting('encounter')) continue;
                    if (!$page->getSetting('components')) continue;

                    $path = array_merge($opath, array(sprintf("%03d",$pcount) . "." . $page->getTitle()));
					$info = array("title"=>sprintf("%03d",$pcount) . "." . $page->getTitle(),"encounter"=>$page->getSetting('encounter'));
                    $structure[]=array("path"=>$path,"name"=>$modName.".".$page->getName());
                    $lcount++;
                    $ccount = 1;
					
                    foreach ($page->getChildPages() as $cpage) {
                        if (!$cpage->getSetting('encounter')) continue;
                        if (!$cpage->getSetting('components')) continue;

                        $cpath = array_merge($path, array(sprintf("%03d",$ccount) . "." . $cpage->getTitle()));
                        if(!isset($info['children'])) {
							$info["children"]=array();
						}
						//$oinfo[]=$cinfo;
						$structure[]=array("path"=>$cpath, "name"=>$modName.".".$cpage->getName(), "date_field"=> $cpage->getSetting('date_field'));
                        $ccount++;
                        $lcount++;
                    }
					/*
					if(!isset($oinfo['children'])) {
						$oinfo["children"]=array();
					}
					 * 
					 */
					
						
                    $pcount++;
                }
                //$opath = $this->getOutputPath($page,$count);

                //$modPageCount++;
                //$this->cpcount++;
                //$count++;
				/*
				if(!isset($structure[$mod->getName()])) {
					$structure[$mod->getAlias()]=array();
				}
				 * 
				 */
				//$structure[$mod->getAlias()][]=$oinfo;
            }
            if ($mod->getSetting('extends')) continue;
            if ($lcount==0) continue;
            $mcount++;
		}
    	return array("data"=>$structure);
	}
	function getExpectedPermissions() {
		return array('get');
	}
}
