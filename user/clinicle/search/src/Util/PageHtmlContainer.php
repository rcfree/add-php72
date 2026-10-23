<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 21/03/2017
 * Time: 14:07
 */

namespace ClinicLE\Search\Util;


use ClinicLE\Content\Render\HtmlElement;

class PageHtmlContainer
{
    private $htmlElement;
    function __construct(HtmlElement $htmlElement) {
        $this->htmlElement = $htmlElement;
    }
    function html() {
        return $this->htmlElement;
    }
    function hasComponent($componentName) {
        return false;
    }
    function getComponent($componentName) {
        return null;
    }
    function addJavascript($javascript, $location = "document") {
        \CLE::web() -> addJavascript($javascript, $location);
    }
}