<?php



class ADD_Component_BlankReports extends ADD_Component {
    function getReportTemplate($module) {
        $tpl = new ADD_Config();
        if ($tpl->loadFile($module->getPath()."/report.ini")) return $tpl;
        return null;
    }
	
	function getBlankReportLinks($rtemplate,$module) {
		$breports = array();
		foreach($rtemplate->getSections() as $name=>$section) {
			if($section->getSetting('no_empty')) continue;
			$breports[$section->getSetting('title')] = ADD_Util::uri("report",array("name"=>$name,"empty"=>"1","module" => $module->getAlias()));
		}
		return $breports;
	}
	function init()
    {
        $events = CLE::sys()->getEvents();
        $events->addListener(\ClinicLE\System\Event\ContentEvents::SIDE_COMMON_OPTIONS, array
        ($this, "onSideCommonOptions"),1000);
    }
    function onSideCommonOptions($event) {

        $content = $event->getContent();
		$add = ADD::sys();
                
        $blankReports = array();
        $breportCount=0;
        CLE::info("onSideCommonOptions");

        foreach($add->getModules()->asArray() as $mname => $mod) {
			if (!$mod->hasAnyPermission()) {
                continue;
            }
            $rtpl = $this->getReportTemplate($mod);
            if (!$rtpl) continue;
            $rheader=$rtpl->getSetting('header');
            if(!$rheader) $rheader = $mod->getSetting('title',$mod->getName());
            $blankReports[$rheader] = $this->getBlankReportLinks($rtpl,$mod);
            $breportCount=$breportCount+count(array_keys($blankReports[$rheader]));
		}	
		
		if($breportCount==0) {
		    return;
        }
		
		$content->startElement("a",array("class"=>"btn btn-mini dropdown-toggle", "data-toggle"=>"dropdown",
            "href"=>"javascript:;"));
		$content->addTextElement("i","",array("class"=>"icon-print"));
		$content->addHtml("&nbsp;Blank reports ");
		$content->addTextElement("span","",array("class"=>"caret"));
		$content->endElement("a");
		$content->startElement("ul",array("class"=>"dropdown-menu"));
		
		$currBlankCount = 0;
	
		foreach($blankReports as $modTitle => $blankList) {
			if (count($blankList)==0) continue;
			
			$content->addTextElement("li",$modTitle,array("class"=>"nav-header"));
				
			foreach($blankList as $label => $link) {
				$content->startElement("li");
				$content->addTextElement("a",$label,array("href"=>$link,"target"=>"_blank"));
				$content->endElement("li");
			}
	
			if ($currBlankCount<count($blankReports)-1) $content->addTextElement("li","",array("class"=>"divider"));
	
			$currBlankCount++;
		}
		$content->endElement("ul");	
	}
}
?>