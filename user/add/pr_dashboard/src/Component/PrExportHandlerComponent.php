<?php
namespace ADD\PR\Component;
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 09/10/17
 * Time: 20:41
 */
class PrExportHandlerComponent extends \ADD_Component
{
    function title(&$title) {
        $title="PR exports";
    }
    function menuPath(&$menuPath) {
        $menuPath="pr/exports";
    }
    function getType() {
        return "PrExportHandler";
    }
}