<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 07/09/16
 * Time: 14:25
 */

namespace ClinicLE\Client\Panel;
use CLE;
use ADD_Content_Html;

class MenuSidePanel extends SidePanel
{
    function generateContent($values=array()) {
        $menus = array();
        $menuPage = CLE::sys()->getPageFromPim(CLE::sys()->getParam('pim'));
        $menuMod = $menuPage->getModule();
        $this->populateModuleMenu($menuMod, $menus);
        $content = new ADD_Content_Html();
        foreach($menus as $pim=>$menu) {
            $menuOutput = $menu->output();
            if (!$menuOutput) {
                return;
            }
            $content->addHtml($menuOutput);
        }
        $content->addTextElement("script","ADD.sideInit();",array("type"=>"text/javascript"));
        return $content;
    }
}