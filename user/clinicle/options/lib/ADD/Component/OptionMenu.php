<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OptionMenu
 *
 * @author rcf8
 */
class ADD_Component_OptionMenu extends ADD_Component {
    
    function baseContent() {
        $om = ADD::web()->getContent('optionMenu');

        $om->startElement("ul",array("class"=>"dropdown-menu"));

        $om->startElement("li");
        $om->addTextElement("a","<i class='icon-star'></i>&nbsp;Change Password",array("href"=>"/user/change"));
        $om->endElement("li");
        $om->startElement("li");
        $om->addTextElement("a","<i class='icon-star'></i>&nbsp;Password reset question",array("href"=>"/user/question"));
        $om->endElement("li");

        $divider = false;
        if (ADD::sys()->getAccessManager()->isAdmin()) {
            $om->addTextElement("li","",array("class"=>"divider"));
            $divider = true;
            $om->startElement("li",array("class"=>"dropdown-submenu"));
            $om->addTextElement("a","Administrator options",array("href"=>"#","tab-index"=>"-1"));

            $om->startElement("ul",array("class"=>"dropdown-menu"));

            $om->startElement("li");
            $om->addTextElement("a","<i class='icon-user'></i>&nbsp;Users",array("href"=>"/auth"));
            $om->endElement("li");
            $om->startElement("li");
            $om->addTextElement("a","<i class='icon-th-list'></i>&nbsp;Roles",array("href"=>"/user/roles"));
            $om->endElement("li");
            $om->endElement("ul");
            $om->endElement("li");
        }

        foreach(ADD::sys()->getModules()->asArray() as $module) {
			$moduleOptionItems = new ADD_Content_Html();
			foreach($module->getPages()->asArray() as $page) {
                $page->getComponents()->callFilteredFunction("optionMenu",array($moduleOptionItems),"ADD_Component_OptionMenuInterface");
            }  
			if(ADD_Util::isEmpty($moduleOptionItems->output())) continue;
			if(!$divider) {
                $om->addTextElement("li","",array("class"=>"divider"));
                $divider = true;
            }
			$title = $module->getSetting('title');
			if(!$title) $title=$module->getName();
            $om->startElement("li",array("class"=>"dropdown-submenu"));
            $om->addTextElement("a",$title,array("href"=>"#","tab-index"=>"-1"));

            $om->startElement("ul",array("class"=>"dropdown-menu"));
			$om->addHtml($moduleOptionItems);
            $om->endElement("ul");
            $om->endElement("li");
        
        }        
        $om->endElement("ul");
    }
    
}
