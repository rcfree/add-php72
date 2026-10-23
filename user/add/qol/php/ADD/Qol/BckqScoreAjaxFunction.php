<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 03/01/17
 * Time: 11:16
 */

namespace ADD\Qol\AjaxFunction;


use ClinicLE\Ajax\AbstractAjaxFunction;
use Symfony\Component\HttpFoundation\Request;
use CLE;

class BckqScoreAjaxFunction extends AbstractAjaxFunction
{
    function runFunction(Request $request)
    {
        $inputs = $request->query;

        if($inputs->get("scores_only")) {
            return null;
        }
        $answers = $this->extractAnswers();
        $score = 0;
        foreach($answers as $fieldName => $answer) {
            if(!$inputs->has($fieldName)) {
                return array("score"=>"");
            }
            if($inputs->get($fieldName)==$answer) {
                $score++;
            }
        }
        return array("score"=>$score);
    }

    private function extractAnswers() {

        $fields = $this->getQuestionFields();

        $answers = array();
        foreach($fields as $field) {
            $answer = $field->getSetting("answer");
            if(!$answer) {
                continue;
            }
            $answers[$field->getName()]=$answer;
        }
        return $answers;
    }
    /**
     * @return array
     */
    function getInputFields()
    {
        $fields = $this->getQuestionFields();
        $inputFields = array_map(function($field) { return $field->getName(); },$fields);
        return $inputFields;
    }

    private function getQuestionFields() {
        $callingPage = $this->getCallingPage();
        $callingForm = $callingPage->getComponent("Form");
        $callingForm->setup();
        $questionFields = array();
        foreach($callingForm->getFields() as $field) {
            if(!is_a($field,"ADD_Field_RadioItem")) {
                continue;
            }
            $questionFields[]=$field;
        }
        return $questionFields;
    }
}