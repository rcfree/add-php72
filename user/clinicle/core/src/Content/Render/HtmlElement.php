<?php
namespace ClinicLE\Content\Render;
use ClinicLE\Common\Util;
use CLE;

class HtmlElement {
    private $idValues=array();
    private $html = array();
    private $viewFile = null;
    private $includeFile = null;

    const START=1;
    const END=2;
    const CLOSED=3;
    const TEXT=4;
    const MARKUP=5;

    public function init() {
    }
    public function countElements() {
        return count($this->html);
    }

    private function generateId($type) {
        if (isset($this->idValues[$type])) {
            $this->idValues[$type]++;
        }
        else {
            $this->idValues[$type]=1;
        }
        return $type."_".$this->idValues[$type];
    }
    function addElement($tag,$attrs=array(),$id=null) {
        if (!$id) $id = $this->generateId($tag);
        $this->processAttrs($tag, $attrs);

        $this->html[$id]=array('tag'=>$tag,'attrs'=>$attrs,'type'=>HtmlElement::CLOSED);
        return $id;
    }

    function unshiftHtml($html,$id=null) {
        if (!$id) $id = $this->generateId("html");
        $this->html=array('id'=>$html) + $this->html;
        return $id;
    }

    function clear() {
        $this->idValues=array();
        $this->html=array();
    }
    protected function createAttrList($attrs) {
        if(is_object($attrs)) {
            CLE::info("attrs",$attrs);
        }
        $attrList = array();
        if (!is_array($attrs)) throw new \InvalidArgumentException("Attribute list ".var_export($attrs,1)." is not an array");
        foreach($attrs as $attrName => $attrValue) {
            $attrList[]="{$attrName}=\"{$attrValue}\"";
        }
        return join(" ",$attrList);
    }

    function startElement($tag,$attrs=array(),$id=null) {
        if (!$id) $id = $this->generateId($tag);
        $this->processAttrs($tag, $attrs);
        $this->html[$id]=array('tag'=>$tag,'attrs'=>$attrs,'type'=>HtmlElement::START);

        return $id;
    }
    function processAttrs($tag, &$attrs) {
        if(isset($attrs['cle-nong'])) {
            return;
        }
        if($tag=="a") {
            $this->processAnchor($attrs);
        }
        if($tag=="button") {
            $this->processButton($attrs);
        }
    }
    function processButton(&$attrs) {
        if(isset($attrs['ng-click'])) {
            return;
        }
        if(Util::val('type',$attrs)=="button") {
            return;
        }
        $attrs['ng-click']="save(\$event)";
        $attrs['ng-disabled']="hasBeenSubmitted()";
    }
    function processAnchor(&$attrs) {
        if(!isset($attrs['href'])) {
            return;
        }
        if(isset($attrs['target'])) {
            return;
        }
        if(isset($attrs['ng-click'])) {
            return;
        }

        if(preg_match("/^#|\/user|\/auth|javascript|^\/report/",$attrs['href'])) {
            return;
        }

        $href = $attrs['href'];
        $attrs['ng-click']="gotoPage('" . $href."')";
        $attrs['href']="javascript:;";
        $attrs['ng-disabled']="hasBeenClicked()";
    }
    function addTextElement($tag,$text,$attrs=array(),$id=null) {
        if (!$id) $id = $this->generateId($tag);
        $this->processAttrs($tag, $attrs);

        if (is_array($text)) {
            $this->startElement($tag,$attrs);

            $this->html[$id]=$text;
            $this->endElement($tag);
        }
        else {

            $this->html[$id]=array('tag'=>$tag,'attrs'=>$attrs,'text'=>$text, 'type'=>HtmlElement::TEXT);
        }
    }

    function endElement($tag,$id=null) {
        if (!$id) $id = $this->generateId($tag);
        $this->html[$id]=array('tag'=>$tag,'type'=>HtmlElement::END);
        return $id;
    }
    function addAfterElement($parentId, $html,$id=null) {
        if (!$id) $id = $this->generateId("html");
        if(isset($html['attrs'])) {
            $this->processAttrs($html['tag'],$html['attrs']);
        }
        $counter=0;
        foreach($this->html as $hid => $item) {
            if ($parentId==$hid) break;
            $counter++;
        }
        $firstChunk = array_slice($this->html,0,$counter+1,true);
        $lastChunk = array_slice($this->html,$counter+1,null,true);
        $firstChunk[$id]=$html;
        $newList = $firstChunk  + $lastChunk;

        $this->html=$newList;
    }

    function addBeforeElement($parentId, $html,$id=null) {
        if (!$id) $id = $this->generateId("html");
        if(isset($html['attrs'])) {
            $this->processAttrs($html['tag'],$html['attrs']);
        }
        $counter=0;
        foreach($this->html as $hid => $item) {
            if ($parentId==$hid) break;
            $counter++;
        }
        $firstChunk = array_slice($this->html,0,$counter,true);

        $lastChunk = array_slice($this->html,$counter,null,true);

        $firstChunk[$id]=$html;

        $newList = $firstChunk  + $lastChunk;
        $this->html=$newList;
    }

    function getHtmlList() {
        return $this->html;
    }
    function get($id) {
        return isset($this->html[$id]) ? $this->html[$id] : null;
    }
    function set($id,$html) {
        $this->html[$id]=$html;
    }

    function output() {
        $output = "";
        //echo "<pre>".var_export($this->_html,1)."</pre>";
        foreach($this->html as $id => $html) {
            $markup = is_object($html) ? $html->output() : $this->_singleToHtml($html);
            $output.=$markup;
        }
        return $output;
    }

    function _singleToHtml($info) {
        list($type,$tag,$attrs,$text) = Util::val(array('type','tag','attrs','text'),$info);

        if ($attrs) {
            $attrs=$this->createAttrList($attrs);
            if (strlen($attrs)>0) $attrs=" ".$attrs;
        }
        else {
            $attrs = "";
        }
        $html=null;
        switch($type) {
            case HtmlElement::CLOSED:
                $html="<$tag$attrs/>";
                break;
            case HtmlElement::START:
                $html="<$tag$attrs>";
                if ($text) $html.=$text;
                break;
            case HtmlElement::TEXT:
                $html="<$tag$attrs>".$text."</$tag>";
                break;
            case HtmlElement::END:
                $html="</$tag>";
                break;
            case HtmlElement::MARKUP:
                $html=$text;
                break;
        }
        return $html;
    }

    function addHtml($html,$id=null,$treat_array_as_item=false) {

        if (is_array($html) && !$treat_array_as_item) {
            foreach($html as $item) {
                $this->addHtml($item);
            }
            return;
        }
        if (!$id) $id = $this->generateId("html");
        $htmlArray = is_array($html);
        if($htmlArray && isset($html['attrs'])) {
            $this->processAttrs($html['tag'],$html['attrs']);
        }
        $this->html[$id]=is_object($html) ? $html : $htmlArray ? $html : array("text"=>$html,"type"=>HtmlElement::MARKUP);
        return $id;
    }
    function addHtmlArray($html) {
        if (is_array($html)) {
            foreach($html as $item) {
                $this->addHtml($item,null,true);
            }
            return;
        }
    }

    function startTable($attrs=null) {
        $this->startElement("table",$attrs);
    }
    function addTableHead($theads, $attrs=null) {
        $this->startElement("thead");
        $this->startElement("tr");
        foreach($theads as $thead) {
            $this->addTextElement("th",$thead);
        }
        $this->endElement("tr");
        $this->endElement("thead");
    }
    function startTableBody($attrs=null) {
        $this->startElement("tbody",$attrs);
    }
    function endTableBody() {
        $this->endElement("tbody");
    }
    function endTable() {
        $this->endElement("table");
    }
    function startTableRow($attrs=null) {
        $this->startElement("tr",$attrs);
    }
    function endTableRow() {
        $this->endElement("tr");
    }
    function addTableCell($cell,$attrs = null) {
        $this->addTextElement("td",$cell,$attrs);
    }

    function __toString() {
        return $this->output();
    }
    function setViewFile($viewFile) {
        $this->viewFile = $viewFile;
    }
    function getViewFile() {
        return $this->viewFile;
    }
    function toHtml() {
        return $this->output();
    }
    public function setIncludeFile($includeFile) {
        $this->includeFile = $includeFile;
    }
    function toText($removeNbsp=false) {
        $text = strip_tags($this->output());
        if ($removeNbsp) $text=str_replace("&nbsp;","",$text);
        return $text;
    }
    public function getIncludeFile() {
        return $this->includeFile;
    }
    function addButton($text, $class, $icon, $isIconWhite=false) {
        $this->startElement("button",array("class"=>"btn $class"));
        $this->addTextElement("i","",array("class"=>"icon-".$icon.($isIconWhite ? " icon-white" : "")));
        $this->addHtml("&nbsp;$text");
        $this->endElement("button");
    }

    function getFieldsFromHtml() {
        $htmlKeyedList = $this->getHtmlList();
        $htmlList = array_values($htmlKeyedList);
        $fields = array();
        $hcount = 0;
        $checks = array();
        while($hcount<count($htmlList)) {
            $html = $htmlList[$hcount];
            $tag = Util::val("tag",$html);

            if (is_object($html) || ($tag!="input" && $tag!="select")) {
                $hcount++;
                continue;
            }
            $attrs = Util::val("attrs",$html);
            list($type,$value,$name)=Util::vals(array("type","value","name"),$attrs);
            if ($tag=="select") {
                $hcount++;
                $opt = $htmlList[$hcount];

                while(Util::val('tag',$opt)=="option") {
                    if (Util::val("selected",$opt['attrs'])=="selected") {
                        $fields[$name]=Util::val("value",$opt['attrs']);
                    }
                    $opt=$htmlList[$hcount];
                    $hcount++;
                }

            }
            if ($tag=="input") {
                if ($type=="radio") {
                    if (!isset($fields[$name])) $fields[$name]="";
                    if (Util::val("checked",$attrs)=="checked") $fields[$name]=$value;
                }
                elseif($type=="checkbox") {
                    $name=preg_replace("/\[\d+\]/","",$name);
                    if (!isset($fields[$name])) {
                        $fields[$name]=array();
                        $checks[$name]=0;
                    }
                    $checks[$name]++;
                    if (Util::val("checked",$attrs)=="checked") $fields[$name][$value]=$value;
                }
                else {
                    $fields[$name]=$value;
                }
            }

            $hcount++;
        }
        foreach($checks as $name=>$ccount) {
            if ($ccount!=1) continue;
            if (count($fields[$name])==0) {
                $fields[$name]=0;
            }
            else {
                $fields[$name]=1;
            }
        }
        return $fields;
    }

    function mimeType()
    {
        return "text/html";
    }
    function headers() {
        return null;
    }
}