<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 06/10/16
 * Time: 17:26
 */

namespace ClinicLE\Manage\Design;
use ClinicLE\Common\Util;

class FormContent
{
    private $formIO;
    public function __construct(FormIO $formIO) {
        $this->formIO = $formIO;
    }
    public function getFileContent() {
        $filename = $this->formIO->getFileName();
        //if(!$this->_configAdmin->doesFileExist()) return "";

        if ($this->formIO->isIniFile() || $this->formIO->isFTXFormat()) {
            return file_get_contents($filename);
        }

        $currPage = $this->formIO->getInputPage();
        if(!$currPage) return "";

        $ff = new \ADD_Component_Form_File($currPage);
        $ff->setFileName($filename);
        $ff->setup();

        $lines = array();
        foreach($ff->getFields() as $field) {
            $level = $field->getLevel();
            if($level=="DEL") {
                continue;
            }
            $line = str_repeat("\t",$level-1). $field->getTitle().(strlen($field->getTitle())>0 ? " ":"");
            $line.="$".$field->getName();
            $line.=" :".Util::convertToPerlish($field->getType());
            if (is_a($field,"ADD_Field_List") && is_a($field->getOptions(),"ADD_Field_Option_Data")) {
                $optItems = array();
                foreach($field->getOptions()->getOptionList() as $key => $value) {
                    $optItems[]=$key."=".$value;
                }
                $line.=" [".implode(",",$optItems)."] ";
            }

            $setItems = array();
            foreach($field->getSettings() as $key => $value) {
                if(is_array($value)) {
                    $counter=0;
                    foreach($value as $arrayValue) {
                        $setItems[]=$key."[".$counter."]=".$arrayValue;
                        $counter++;
                    }
                }
                else {
                    if(Util::isEmpty($value)) continue;
                    $setItems[]=$key."=".$value;
                }
            }
            if (count($setItems)>0) {
                $line.=" {".implode(",",$setItems)."}";
            }

            if(is_a($field,"ADD_Field_Container")) {
                $lines[]="";
            }
            $lines[]=$line;
        }

        return implode("\n",$lines);

    }
}