<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 10/11/2016
 * Time: 10:39
 */

namespace ClinicLE\Client\Container;


use ClinicLE\Content\Render\HtmlElement;

class Container
{
    private $properties = array();

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }


    /**
     * @param array $controllerAttrs
     */
    public function appendProperties($properties)
    {
        foreach($properties as $name => $prop) {
            $this->putProperty($name,$prop);
        }
    }

    /**
     * @param array $controllerAttrs
     */
    public function appendAttrs($attrs)
    {
        foreach($attrs as $name => $attr) {
            $this->putAttr($name,$attr);
        }
    }

    public function __construct($id, $controller)
    {
        $this->content = new \ADD_Content_Html();
        $this->properties['id'] = $id;
        $this->properties['ng-controller'] = $controller;
        $this->properties['ng-cloak']="";
        $this->properties['cle-page_no']=1;
    }
    public function getContent()
    {
        return $this->content;
    }
    public function startHtml(\ADD_Content_Html $html) {
        $html->startElement("div",$this->getProperties());
    }
    public function endHtml(\ADD_Content_Html $html) {
        $html->endElement("div");
    }
    public function createHtml() {
        $html = \ADD::web()->newContent();

        $this->startHtml($html);
        $html->addHtml($this->content->output());

        $this->endHtml($html);

        return $html;
    }
    public function addToContent($html) {
        if(is_a($html,HtmlCreatorInterface::class)) {
            $html = $html->createHtml();
        }
        $this->content->addHtml($html);
    }
    public function putProperty($prop,$value) {
        $this->putAttr("cle-".$prop,$value);
    }
    public function putAttr($attr,$value) {
        $this->properties[$attr]=$value;
    }
}