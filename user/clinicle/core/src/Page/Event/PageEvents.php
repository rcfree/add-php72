<?php

/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 22/11/2016
 * Time: 14:21
 */
namespace Clinicle\Page\Event;

final class PageEvents {

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */


    const BREADCRUMB = "pageEvents.breadcrumb";
    const TITLE = "pageEvents.title";
    const DISPLAY_ACCESS = "pageEvents.displayAccess";
    const SUBMIT_ACCESS = "pageEvents.submitAccess";
    const BEFORE_POPULATE = "pageEvents.before_populate";
    const AFTER_POPULATE = "pageEvents.after_populate";
    const POPULATE = "pageEvents.populate";
    const BEFORE_GENERATE = "pageEvents.before_generate";
    const GENERATE = "pageEvents.generate";
    const AFTER_GENERATE = "pageEvents.after_generate";
    const AFTER_ALL_GENERATE = "pageEvents.after_all_generate";
    const BEFORE_ALL_GENERATE = "pageEvents.before_all_generate";
    const MENU = "pageEvents.menu";
    const AFTER_MENU = "pageEvents.after_menu";
    const SETUP= "pageEvents.setup";
    const INIT= "pageEvents.init";
    const INIT_CONTENT= "pageEvents.init_content";
    const EXPECTED_PERMS = "pageEvents.expectedPerms";
    const INIT_COMPONENTS= "pageEvents.initComponents";
    const MENU_PATH="pageEvents.menuPath";
    const MENU_PARAMS="pageEvents.menuParams";
    const MENU_ATTRS="pageEvents.menuAttrs";
    const MENU_LABEL="pageEvents.menuLabel";
    const MENU_ICON="pageEvents.menuIcon";
    const PATH_NOT_PAGE="pageEvents.pathNotPage";
    const VIEW_FILE="pageEvents.viewFile";
    const BASE_CONTENT="pageEvents.baseContent";
    const SUBMIT_FORM = "submitEvents.submit";
    const PERMISSIONS = "pageEvents.permissions";
}