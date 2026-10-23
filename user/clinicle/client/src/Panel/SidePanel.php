<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 07/09/16
 * Time: 14:16
 */

namespace ClinicLE\Client\Panel;
use CLE;

class SidePanel extends AbstractPanel
{
    function generateContent($values = array()) {
        $add = CLE::sys();
        $mods = $add->getModules()->asArray();
        $currentPage = $this->getContentPage();

        usort($mods, function($a, $b) {
            return strcmp($a->getAlias(), $b->getAlias());
        });

        $finalMenu = CLE::web()->getContent("menu");
        $menus = $this->createModuleMenus($mods);

        if (count($menus) == 0) {
            return $finalMenu;
        }
        $content = $this->renderFinalMenus($finalMenu, $menus, $currentPage);
        $content->addTextElement("script","ADD.sideInit();",array("type"=>"text/javascript"));

        return $content;
    }
    function renderFinalMenus($finalMenu, $menus, $currentPage) {
        $add = CLE::sys();
        $finalMenu->startElement("div", array("id" => "options"));
        $finalMenu->addTextElement("div", $add->getConfig()->getSetting('patient_menu_text', "Click on a heading below to view patient options."), array("class" => "muted"));
        foreach ($menus as $pim => $menu) {
            $this->addMenuToContent($pim,$menu,$finalMenu);
        }

        $finalMenu->endElement("div");
        return $finalMenu;
    }

    private function createModuleMenus($mods) {
        $menus = array();

        foreach ($mods as $mod) {
            $this->populateModuleMenu($mod, $menus);
        }
        return $menus;
    }
    function populateModuleMenu($mod, &$menus) {
        foreach ($mod->getTopPages() as $page) {
            $menuHtml = null;
            try {
                $menuHtml = $page->createMenu();
            } catch (Exception $ex) {
                if (!$menuHtml) {
                    $menuHtml = CLE::web()->newContent();
                }
                $menuHtml->addTextElement("p", "Unexpected menu error:" . CLE::web()->errorMessage($ex));
            }

            if ($menuHtml && $menuHtml->output()) {
                $menus[$mod->getName() . ":" . $page->getName()] = $menuHtml;
            }
        }
    }

    function addMenuToContent($pim, $menu, $content) {
        $currentPage = $this->getContentPage();
        $add = CLE::sys();
        $menuOutput = $menu->output();
        if (!$menuOutput) {
            return;
        }
        $page = $add->getPageFromPim($pim);
        $mod = $page->getModule();
        $domainAttrs = array("id" => $mod->getAlias() . "-domain-menu", "class" => "accordion-group");

        $currentPim = $currentPage->getModule()->getName() . ":" . $currentPage->getName();
        $domainAttrs = $domainAttrs + array(
                'ng-controller' => "MenuController",
                'cle-pim' => $pim,
                'cle-current' => $currentPim
            );

        $content->startElement("div", $domainAttrs);
        $content->addHtml($menuOutput);
        $content->endElement("div");
    }
}