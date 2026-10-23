<?php

class ADD_Component_MenuItem extends ADD_Component {
	public function menu($options) {
        $page = $this->_page;

			if ($page->getSetting('no-menu')) return null;
            if (!$page->hasAnyPermissions(array('view','edit','create'))) return null;

			$add = ADD::sys();

			$label = $page->createMenuLabel();
            if (!$label) return null;

            $oattrs = $add->getAccessManager()->isCurrentPage($page) ? array('class'=>'active') : array();

                    $options->startElement('li',$oattrs);
					$attrs = $page->createMenuAttrs();
					$attrs['href']=$page->createMenuUri();
		
                $options->startElement('a',$attrs,"option-".$page->getName());

                $menuIcon = $page->createMenuIcon($page);
                if ($menuIcon) {
                    if (!is_array($menuIcon)) $menuIcon = array($menuIcon);
                    foreach($menuIcon as $icon) {
                        $options->addTextElement("i","",array("class"=>$icon));
                        $options->addTextElement("span","&nbsp;",array(),"option-prefix-".$page->getName());

                    }
                }
                    $options->addHtml($label);
                    $options->endElement('a',"option-".$page->getName()."-end");

                    $options->endElement('li');
            }
    }
?>