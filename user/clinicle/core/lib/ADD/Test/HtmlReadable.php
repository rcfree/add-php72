<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HtmlReadable
 *
 * @author rcf8
 */
class ADD_Test_HtmlReadable {
    private $_html;
    private $_ignoreTags;
    
    public function __construct(ADD_Content_Html $html,$ignoreTags=array(),$currLevel=0) {
        $this->_html = $html;
        $this->_ignoreTags = $ignoreTags;
        $this->_currLevel=$currLevel;
    }
    
    function _outputLine($item) {
        $text = ADD_Util::val("text",$item) ? $text=" '".$item['text']."'" : "";
        $attrs="";
        if(isset($item['attrs']) && count($item['attrs'])>0) {
            $attrList = array();
            foreach($item['attrs'] as $attr => $val) {
                $attrList[]=$attr."=".$val;
            }
            $attrs=": ".implode(", ",$attrList);
        }
        return str_repeat("\t",$this->_currLevel).$item['tag']."$text$attrs\n";
    }  
    
    function getReadableText($item) {
		$text = is_object($item) ? $item : ADD_Util::val("text",$item);
		if(is_object($text)) {
			$hr = new ADD_Test_HtmlReadable($text,$this->_ignoreTags,$this->_currLevel);
			$rt = $hr->outputReadable();
			return $rt;
        }
        if(isset($item['tag']) && in_array($item['tag'],$this->_ignoreTags)) return "";
        if ($item['type']==ADD_Content_Html::END) {
            $this->_currLevel--;
            return "\n";
        }

        if ($item['type']==ADD_Content_Html::MARKUP) {
            return str_repeat("\t",$this->_currLevel)."*".$item['text']."\n";
        }

        $readable=$this->_outputLine($item);
        if ($item['type']==ADD_Content_Html::START) {
            $this->_currLevel++;
        }
        return $readable;
    }
        
    function outputReadable() {
        $currLevel=0;
        $readable ="";
        foreach($this->_html->getHtmlList() as $id => $item) {
            $rt=$this->getReadableText($item);
			$readable.=$rt;
        }
        return $readable;
    }
}
