<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 30/10/17
 * Time: 11:38
 */

namespace ClinicLE\RiskScore;
use ClinicLE\Ajax\AbstractAjaxFunction;
use ClinicLE\Data\PageFieldSearch\PageFieldSearchCreator;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractRiskScoreAjaxFunction extends AbstractAjaxFunction
{

    abstract function getScoreMessage();
    abstract function createParamsGenerator(PageFieldSearchCreator $creator);
    abstract function getOutputField();
    abstract function createScoreClass();
    function runFunction(Request $request)
    {
        $callPage = $this->getCallingPage();

        $queryParams = $request->query;
        $creator = new PageFieldSearchCreator(\CLE::sys()->getModules(),$queryParams,$callPage->getPim(),$this->getScoreMessage());
        $generator = $this->createParamsGenerator($creator);

        $params = $generator->createParams();
        if($creator->hasWarnings()) {
            foreach($creator->getWarnings() as $warning) {
                \CLE::web()->addWarning($warning);
            }
            return array($this->getOutputField()=>null);
        }
        $score = $this->createScoreClass();
        return array($this->getOutputField()=>$score->calculateScore($params));
    }
}