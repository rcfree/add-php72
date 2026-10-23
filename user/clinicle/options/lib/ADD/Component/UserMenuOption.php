<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserMenuOption
 *
 * @author rcf8
 */


class ADD_Component_UserMenuOption extends ADD_Component implements ADD_Component_OptionMenuInterface {
	public function optionMenu($optionMenu) {
        $add = ADD::sys();
        $page = $this->_page;
		if(!$page->getPermission($this->getSetting('permission'))) return;
        $title = $page->getSetting('title');
        $path = ADD_Util::uri($page->createMenuPath(), $page->createMenuParams());
		$icon = $this->getSetting('icon');
		$optionMenu->startElement("li");
        $optionMenu->startElement('a', array('href' => $path));
        $optionMenu->addHtml(($icon ? "<i class='$icon'></i>&nbsp;" : "") . $title);
        $optionMenu->endElement('a');
        $optionMenu->endElement("li");
        $optionMenu->startElement('li');
        $optionMenu->endElement('li');
    }
	public function expectedPermissions(&$expPerms) {
		$expPerms[]=$this->getSetting('permission');
	}
}
