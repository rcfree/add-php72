<?php

class ADD_Field_Image extends ADD_Field_Base {
        protected $stored = false;
        function addMarkup($page) {
                $html = $page->html();
                $html->startElement("div",array("style"=>"width:100%"));
                $html->addTextElement("a","<i class='icon-picture'></i>&nbsp;View image as popup..",array("style"=>"float:right", "class"=>"btn btn-small", "href"=>"javascript:;","onclick"=>"window.open('".$this->getSetting('src')."','_blank','width=800,height=600')"));
                $html->endElement("div");
                $html->addElement('img',$this->getSettings());
        }
}
?>