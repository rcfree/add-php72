<?php
namespace ADD\PR\Util;
use ADD\PR\SearchContent;
use ClinicLE\Search\Query\DataQueryRunnerFactory;
use ClinicLE\Search\Resource\SearchServices;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 27/10/17
 * Time: 20:50
 */

class FilterSearchContent
{
    private $searchContent;
    private $filterMessages=array();

    /**
     * @return array
     */
    public function getFilterMessages()
    {
        return $this->filterMessages;
    }
    function __construct(SearchContent $searchContent) {
        $this->searchContent = $searchContent;
    }

    /**
     * @param ParameterBag $params
     * @return \ClinicLE\Search\Query\QueryRunner
     */
    function createRunner(ParameterBag $params) {
        $this->searchContent->load();

        $factory = new DataQueryRunnerFactory(\CLE::svc(SearchServices::GROUP_SET));
        $runner = $factory->createRunner($this->searchContent->getSearchData());

        $referral = $runner->getGroupQueryByName("pr.prog.referral");
        $outcome = $runner->getGroupQueryByName("pr.prog.outcome");
        if($params->get("main_diagnosis")) {
            $referral->addFilter("main_diagnosis", "eq", array(
                $params->get("main_diagnosis")));
            $refPage = \CLE::sys()->getPageFromPim("pr:referral");
            $refPage->setupComponents();
            $refForm = $refPage->getComponent("Form");

            $diagField = $refForm->getField("main_diagnosis");
            $diagField->setValue($params->get("main_diagnosis"));
            $this->filterMessages[]= "Diagnosis = ".$diagField->getDisplayText($refPage,null);
        }
        if($params->get("ref_date_from") || $params->get("ref_date_to")) {
            if($params->get("ref_date_from")) {
                $this->filterMessages[] = "Referral date from = ".$params->get("ref_date_from");
            }
            if($params->get("ref_date_to")) {
                $this->filterMessages[] = "Referral date to = ".$params->get("ref_date_to");
            }
            $referral->addFilter("referral_date", "bt", array($params->get("ref_date_from"),
                $params->get("ref_date_to")));
        }
        if($params->get("ass_date_from")) {
            $this->filterMessages[] = "Assessment date from = ".$params->get("ass_date_from")."'";
        }
        if($params->get("ass_date_to")) {
            $this->filterMessages[] = "Assessment date to = '".$params->get("ass_date_to")."'";
        }

        if($params->get("ass_date_from") || $params->get("ass_date_to")) {
            $outcome->addFilter("date_pr_assessment","bt",array($params->get("ass_date_from"),
                $params->get("ass_date_to")));
        }
        if($params->get("cohort_no")) {
            $outPage = \CLE::sys()->getPageFromPim("pr:outcome");
            $outPage->setupComponents();
            $outForm = $outPage->getComponent("Form");

            $noField = $outForm->getField("cohort_no");
            $noField->setValue($params->get("cohort_no"));

            $outcome->addFilter("cohort_no", "eq", array($params->get("cohort_no")));
            $this->filterMessages[] = "Cohort no = ".$noField->getDisplayText($outPage,null);
        }
        return $runner;
    }
}