<?php
namespace ADD\PR\Component;
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 09/10/17
 * Time: 20:41
 */
class PrDashboardHandlerComponent extends \ADD_Component
{
    function title(&$title) {
        $title="PR data dashboard";
    }
    function menuPath(&$menuPath) {
        $menuPath="pr/dashboard";
    }
    function getType() {
        return "PrDashboardGridHandler";
    }
}