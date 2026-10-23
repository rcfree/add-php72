<?php
use ClinicLE\Manage\Resource\AjaxFactories;

class ADD_Page_Ajax extends ADD_Page {
    function isWebContent() {
        return false;
    }
    function isFormOnly() {
        return true;
    }
    function createMenuPath() {
        return "ajax";
    }
    function getComponent($name) {
        if($name!="AjaxExecute") return;
        return $this;
    }
    function createOutput($values=array()) {
        $content = ADD::web()->newContent('Json');
        $add = ADD::sys();

        $funcName = $add->assertParam('f');
        $pageName=$add->assertParam('p');
        $modName=$add->assertParam('m');
        $fieldName = $add->getParam('field');
        $contPage = $add->getPageInModule($modName,$pageName);

        $messages = array();

        $output = null;
        try {
            $ajaxFunction = $this->createAjaxFunction($contPage,$funcName);
            $output = $ajaxFunction->runFunction(\Symfony\Component\HttpFoundation\Request::createFromGlobals());
        }
        catch(ADD_AjaxFieldException $ex) {
            $messages['errors']=array(array("fieldTitle"=>$ex->getFieldTitle(),"fieldName"=>$ex->getFieldName(),"message"=>ADD::web()->errorMessage($ex)));
        }
        catch(Exception $ex) {
            $messages['errors']=array(array("fieldTitle"=>"Unexpected error","message"=>ADD::web()->errorMessage($ex)));
        }
        if ($this->getWarnings()) {
            $messages['warnings']=$this->getWarnings();
        }

        if($add->config("debug")) {
            $messages['debug']=ADD::outputDebug();
        }
        $content->setJsonData(array(
            "outputs"=>$output,
            "messages"=>$messages,
            "fields"=>$this->createAjaxInputs($contPage,$funcName, $fieldName)
        ));
        return $content;
    }

    /**
     * @param $funcName
     * @param null $callingPage
     * @return AbstractAjaxFunction
     */
    function createAjaxFunction($callingPage, $funcName) {
        $system = CLE::sys();

        $factory = $system->getFactory(AjaxFactories::AJAX_FUNCTION);

        return $factory->newInstance($funcName,array($callingPage));
    }
    function createPagePath() {
        return "ajax";
    }

    function createAjaxInputs($page, $funcName, $fieldName=null) {
        $ajaxFunction = $this->createAjaxFunction($page,$funcName);
        $inputs = $ajaxFunction->getInputFields();
        $counter = 0;
        foreach($inputs as $input) {
            if($input == "SELF") {
                $inputs[$counter]= $fieldName;
            }
            $counter++;
        }
        return $inputs;
    }
}