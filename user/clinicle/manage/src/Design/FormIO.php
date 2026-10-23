<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 06/10/16
 * Time: 17:25
 */

namespace ClinicLE\Manage\Design;
use CLE;

class FormIO
{
    private $inputModuleName;
    private $inputPageName;

    public function getInputModuleName() {
        return $this->inputModuleName;
    }
    public function getInputPageName() {
        return $this->inputPageName;
    }
    public function __construct($inputModuleName, $inputPageName) {
        $this->inputModuleName = $inputModuleName;
        $this->inputPageName = $inputPageName;
    }

    public function isIniFile() {
        if(preg_match("/\.ini$/",$this->inputPageName)) return true;
    }
    public function isFTXFormat() {
        $page = $this->getInputPage();

        if ($page->getSetting('form_ext')=="ftx") return true;
        return false;
    }
    public function getInputPage() {
        return CLE::sys()->getPageInModule($this->inputModuleName,$this->inputPageName);
    }
    public function doesFileExist() {
        return file_exists($this->getFileName());
    }

    public function getFileName() {
        $add = CLE::sys();
        $filename=null;
        $baseFile = $add->getSourceModulePath($this->inputModuleName);
        $filename="";
        if ($this->inputPageName) {
            if($this->isIniFile()) {
                return "$baseFile/".$this->inputPageName;
            }
            else {
                $page = $this->getInputPage();
                $ext = $page->getSetting('form_ext');
                if ($ext=="ftx") {
                    return "$baseFile/form/".$page->getSetting('form').".ftx";
                }
                else {
                    return $page->getSetting('form').($ext ? ".".$ext : "");
                }
            }
        }
    }

    public function putFileContent($content) {
        $filename = $this->getFileName();
        CLE::log()->info("cwd:".getcwd().", filename:$filename");
        file_put_contents($filename,$content);
    }

    public function getFileOptions() {
        $add = CLE::sys();
        $options=array("page.ini"=>"page.ini","dictionary.ini"=>"dictionary.ini");
        $modTpl = $add->getModule($this->inputModuleName)->getTemplate();

        foreach($modTpl->getSections() as $name => $section) {
            if (!$section->getSetting('form')) continue;
            $fileExt = $section->getSetting('form_ext',"xlsx");
            $options[$name]="form - ".$name;
        }
        return $options;
    }
}