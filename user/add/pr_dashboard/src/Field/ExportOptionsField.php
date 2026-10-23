<?php
namespace ADD\PR\Field;

use ADD\PR\MultiGridHeader;
use ADD\PR\MultiGridLegend;
use ADD\PR\MultiGridTable;
use ADD\PR\SearchConfig;
use ADD\PR\SearchLegend;
use ADD\PR\SearchTable;
use ClinicLE\Client\Container\Cell\MatrixCell;
use ClinicLE\Client\Container\Table;
use ClinicLE\Search\Cell\SearchResultCell;


/**
 * Created by PhpStorm.
 * User: rob
 * Date: 09/10/17
 * Time: 20:12
 */
class ExportOptionsField extends \ADD_Field_List {
    private $fields = array();
    function beforeAddMarkup($page) {
        $options = $this->getOptions();
        $this->fields = array();
        foreach($options->getOptionList() as $value => $label) {
            $radios = new \ADD_Field_List_RadioButtons(
                array(
                    "Field"=>$this->getName()."[".$value."]",
                    "Type"=>"radio_buttons",
                    "Options"=>array($value."_x"=>"Exclude", $value."_f"=>"Full",$value."_s"=>"Short"),
                    "Title"=>$label
                )
            );
	    $radios->setValue($value."_x");
            $radios->finaliseField($page);
            $this->fields[]=$radios;
        }
    }
    function addMarkup($page)
    {
        $html = $page->html();
        foreach($this->fields as $field) {
            $html->startElement("div",array("style"=>"display:inline;width:10%","class"=>"pull-left"));
            $field->addMarkup($page);
            $html->endElement("div");
        }
    }

}
