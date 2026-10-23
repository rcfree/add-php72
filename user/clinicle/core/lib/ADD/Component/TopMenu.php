<?php



class ADD_Component_TopMenu extends ADD_Component {

    function generate() {
        $page = $this->_page;
        $topOptions = ADD::web()->getContent('topMenu');
        $ppage = $page->getParentPage();
        $add = ADD::sys();
        //$penc = $ppage->getComponent('Encounter');

        $limit = $page->getSetting('TopMenu.limit');
        $cpages = array();
        if ($limit) {
            $limitPage = $page->getModule()->getPage($limit);
            $cpageNames = $limitPage->getSetting('child');
            if ($cpageNames) {
                foreach ($cpageNames as $cpageName) {
                    $cpages[] = $page->getModule()->getPage($cpageName);
                }
            }
        } else {
            $cpages = $ppage->getChildPages();
        }
        $availPages = array();
        foreach ($cpages as $cpage) {
			if ($cpage->hasAnyPermissions(array('create', 'view', 'edit'))) {
                $availPages[] = $cpage;
            }
        }

        $topOptions->startElement("ul", array("class" => "nav nav-pills"));
        foreach ($availPages as $cpage) {
            $attrs = array();
            if ($cpage === $page)
                $attrs['class'] = 'active';
            if ($cpage->getSetting('no-menu'))
                continue;

            $topOptions->startElement("li", $attrs);
            $cpath = $cpage->createMenuUri();
            $attrs = $cpage->createMenuAttrs();
            $attrs['href'] = $cpath;
            $topOptions->startElement("a", $attrs);
            $menuIcon = $cpage->createMenuIcon();
            if ($menuIcon) {
                if (!is_array($menuIcon))
                    $menuIcon = array($menuIcon);
                foreach ($menuIcon as $icon) {
                    $topOptions->addTextElement("i", "", array('class' => $icon));
                }
                $topOptions->addHtml("&nbsp;");
            }
            $menuTitle = $cpage->createMenuLabel($cpage);

            $topOptions->addHtml($menuTitle);
            $topOptions->endElement("a");
            $topOptions->endElement("li");
        }
        $topOptions->endElement("ul");
    }

}
