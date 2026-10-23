<?php
namespace COPD\Main\Component;
use ADD_Component as AbstractComponent;
use ClinicLE\Common\Util;
use ClinicLE\Data\Query\AbstractEncounterQuery;
use ClinicLE\Data\Query\QueryRunner;
use ClinicLE\System\Service\CoreServices;
use EMBER\Blness\Data\EmberPatientExtractor;
use EMBER\Blness\Data\EmberPatientExtractorParams;
use CLE;
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 13/07/17
 * Time: 10:00
 */
class PRReferComponent extends AbstractComponent
{
    function setup() {
        $this->getPage()->getEvents()->addListener(\ClinicLE\System\Event\ContentEvents::OPTION_BUTTONS,array
        ($this,"onOptionButtons"));
    }
    function getType() {
            return "ReferralsButton";
    }

    function onOptionButtons(\ClinicLE\Component\Event\PageContentEvent $event) {
        $sys = CLE::sys();
        $html = $event->getContent();
        $html->addTextElement("a","<i class='icon-list icon-white'></i>&nbsp;Goto COPD referrals",array("class"=>"btn btn-info float-right",
            "href"=>"/copd/referrals"));
        $html->addTextElement("a","<i class='icon-arrow-right icon-white'></i>&nbsp;Refer to PR",array("class"=>"btn btn-info float-right",
            "href"=>"/pr/refer?pid=".$sys->getPatientId()."&eid=".$sys->getParentEncounterId()));
    }
}