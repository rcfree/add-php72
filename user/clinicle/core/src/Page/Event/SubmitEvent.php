<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Page\Event;

use ClinicLE\Data\Repository\AuditRepository;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;


class SubmitEvent extends Event {
    private $formValues=array();
    private $redirectUri=null;
    private $request;
    private $audit;
    private $submitFailReason;

    /**
     * @return mixed
     */
    public function getSubmitFailReason()
    {
        return $this->submitFailReason;
    }

    /**
     * @param mixed $submitFailReason
     */
    public function setSubmitFailReason($submitFailReason)
    {
        $this->submitFailReason = $submitFailReason;
    }

    /**
     * @return AuditRepository
     */
    public function getAudit()
    {
        return $this->audit;
    }

    /**
     * @param mixed $audit
     */
    public function setAudit($audit)
    {
        $this->audit = $audit;
    }

    function getFormValues() {
        return $this->formValues;
    }
    function setFormValues(array $formValues) {
        $this->formValues = $formValues;
    }
    function setFormValue($valueName,$value) {
        $this->formValues[$valueName]=$value;
    }
    function getFormValue($valueName) {
        return isset($this->formValues[$valueName]) && $this->formValues[$valueName];
    }
    function setRedirect($redirectUri) {
        $this->redirectUri = $redirectUri;
    }
    function getRedirect() {
        return $this->redirectUri;
    }

    /**
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    function getRequest() {
        return $this->request;
    }
    function __construct(Request $request) {
        $this->request = $request;
        $this->formValues = $request->request->all();
    }
}