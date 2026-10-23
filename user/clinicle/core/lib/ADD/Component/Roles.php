<?php


class ADD_Component_Roles extends ADD_Component {
	function title(&$title) {
		$title="Roles";
	}
	function generate() {
		$add = ADD::sys();
		$uniquePerms = array();
		$actualPerms = array();
		foreach($add->getModules() as $mod) {
			foreach($mod->getPages() as $curPage) {
				$perms = $curPage->getExpectedPermissions();
				foreach($perms as $perm) {
					$uniquePerms[$mod->getName()][$perm]=1;
					$actualPerms[$mod->getName()][$curPage->getName()][$perm]=1;
				}
			}
		}
		$html = $page->html();
		
		foreach($add->getModules() as $mod) {
				$modName = $mod->getName();
				$html->addTextElement("div",$mod->getName()." permissions");
				$html->addElement("input",array("type"=>"checkbox"));
				$html->startElement("table",array("class"=>"table table-hover"));
				$html->startElement("thead");
				$html->startElement("tr");
				$html->addTextElement("th","Page",array("style"=>"width:8em"));
				foreach($uniquePerms[$modName] as $uniquePerm => $flag) {
					$html->addTextElement("th",ucfirst($uniquePerm),array("style"=>"width:3em"));
				}
				$html->endElement("tr");
				$html->endElement("thead");
				$html->startElement("tbody");
				$modPerms = ADD_Util::getArrayValue($modName,$actualPerms,array());
				foreach($mod->getPages() as $curPage) {
					if (count($modPerms)==0) continue;
					$pageName = $curPage->getName();
					$pagePerms = ADD_Util::getArrayValue($pageName,$modPerms,array());
					if (count($pagePerms)>0) {
						$html->startElement("tr");
						$html->startElement("td",array("style"=>"width:8em"));
						$html->addHtml($curPage->getSetting('title',$pageName));
						$html->addElement("input",array("type"=>"checkbox"));
	
						$html->endElement("td");
						foreach($uniquePerms[$modName] as $uniquePerm => $flag) {
							$html->startElement("td",array("style"=>"width:3em"));
							if (ADD_Util::getArrayValue($uniquePerm,$pagePerms)) $html->addElement("input",array("type"=>"checkbox"));
							$html->endElement("td");
						}
						$html->endElement("tr");
					}
				}
				$html->endElement("tbody");
				$html->endElement("table");
			
		}		
	}
	function menuPath(&$menuPath) {
		$menuPath="roles";
	}
}
