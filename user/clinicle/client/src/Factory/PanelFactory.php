<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 07/09/16
 * Time: 13:50
 */

namespace ClinicLE\Client\Factory;
use CLE;
use ClinicLE\Client\Panel\AbstractPanel;
use ClinicLE\Common\Factory\AbstractFactory;
use ClinicLE\Common\Util;

class PanelFactory extends AbstractFactory
{
    function getClassType() {
        return "Panel";
    }

    public function newInstance($classLabel, $arguments=array()) {
        $classLabel = Util::convertToPascal($classLabel);
        return parent::newInstance($classLabel, $arguments);
    }
    protected function createInstance($classFullName,$arguments) {
        $panel = parent::createInstance($classFullName,array($this));
        $sys = CLE::sys();
        if($sys->getParam('pagePath')=="") {
            $context = $sys->getAccessManager()->getContext();
            $sys->setParam('pagePath',$context->session->userdata('cle_path'));
            $sys->setState('redirect',$context->session->userdata('cle_path'));
        }

        $this->setContentPageFromPath($sys->getParam('pagePath'), $panel);
        $contentPage = $panel->getContentPage();
        if(is_a($contentPage,"ADD_Page")) {
            $contentPage->checkPublicPage();
        }
        else {
            $contentPage->getAccess()->dealWithInitialAccess($sys->getAccessManager(), $sys->getPageResolver());
        }
        return $panel;
    }
    function setContentPageFromPath($pagePath, AbstractPanel $panel) {
        $add = CLE::sys();
        $url = parse_url($pagePath);
        $params = array();
        parse_str(Util::val('query',$url,""),$params);

        $existingParams = $add->getParams() + $params;

        $add->setParams($existingParams);
        $add->setPatientId(Util::val('pid',$existingParams));

        $currentPage = $add->getCurrentPage($url['path']);

        if (!$currentPage) {
            throw new \ClinicLE\Module\Exception\ItemUndefinedException("Page with path '".$pagePath."' not found");
        }
        $add->getAccessManager()->setCurrentPage($currentPage);
        $panel->setContentPage($currentPage);
    }
}