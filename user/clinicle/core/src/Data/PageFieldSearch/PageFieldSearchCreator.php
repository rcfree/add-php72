<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 27/10/17
 * Time: 11:28
 */

namespace ClinicLE\Data\PageFieldSearch;


use ClinicLE\Common\Util;
use ClinicLE\Module\ModuleCollection;
use Symfony\Component\HttpFoundation\ParameterBag;

class PageFieldSearchCreator
{
    private $modules;
    private $idValue;
    private $queryParams;
    private $currentPage;
    private $message;
    private $warnings = array();

    /**
     * @return array
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * @param $warning
     */
    public function addWarning($warning)
    {
        $this->warnings[] = $warning;
    }
    public function hasWarnings() {
        return count($this->warnings)>0;
    }
    private $patientId;
    function __construct(ModuleCollection $modules, ParameterBag $queryParams, $currentPage, $message) {
        $idValue = $queryParams->get('eid');
        if(!$idValue) {
            $idValue = $queryParams->get('parentid');
        }
        $this->patientId = $queryParams->get('pid');
        $this->idValue = $idValue;
        $this->modules = $modules;
        $this->queryParams = $queryParams;
        $this->currentPage = $currentPage;
        $this->message = $message;
    }
    function createPageSearch($pageName,$ignoreMissing=false) {
        $pageBits = explode(":",$this->currentPage);
        if($pageBits[1]==$pageName) {
            $pageSearch = new SinglePageFieldSearch($this->currentPage, $this);
        }
        else {
            $pageSearch = new SiblingPageFieldSearch($this->currentPage, $pageName, $this);
        }
        $pageSearch->setIgnoreMissing($ignoreMissing);
        return $pageSearch;
    }

    /**
     * @return array|null|string
     */
    public function getPatientId()
    {
        return $this->patientId;
    }


    /**
     * @return mixed
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @return array|null|string
     */
    public function getIdValue()
    {
        return $this->idValue;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

}