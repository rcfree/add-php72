<?php

interface ADD_Component_AjaxInterface {
    function ajaxFunction($values, ADD_Page $callPage);
    function ajaxInputs(ADD_Page $callPage);
}