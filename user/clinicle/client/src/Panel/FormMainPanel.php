<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 07/09/16
 * Time: 14:28
 */

namespace ClinicLE\Client\Panel;
use CLE;
use ClinicLE\Common\Util;

class FormMainPanel extends MainPanel
{
    function generateContent($values=array()) {
        $db = CLE::db();
        $currPage = $this->getContentPage();

        if (!$currPage) {
            throw new Exception("Page not found");
        }
        $db->beginTxn();
        $currPage->setupComponents();
        $redirectPath = null;
        if(Util::requestMethod()=="POST") {
            $result = $currPage->submitForm($values);
            $db->commitTxn();

            $db->beginTxn();
            CLE::web()->afterSubmit($currPage, $values);

            if ($db->inTxn()) {
                $db->commitTxn();
            }
            $redirectPath = Util::val('redirect',$result);
        }
        if($redirectPath) {
            $content = $this->createRedirectedContent($redirectPath,$values);
        }
        else {
            $content = $this->createMainContent($values);
        }
        return $content;
    }
}