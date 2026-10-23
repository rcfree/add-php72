<?php

namespace ClinicLE\Ajax;

use Symfony\Component\HttpFoundation\Request;
use ADD_Component_AjaxInterface;

class LegacyAjaxFunction extends AbstractAjaxFunction
{
    private $legacyFunction;
    function __construct(ADD_Component_AjaxInterface $legacyFunction) {
        $this->legacyFunction = $legacyFunction;
        parent::__construct($legacyFunction->getPage());
    }
    function runFunction(Request $request)
    {
        $values = $request->query->all();
        return $this->legacyFunction->ajaxFunction($values, $this->getCallingPage());
    }

    function getInputFields()
    {
        return $this->legacyFunction->ajaxInputs($this->getCallingPage());
    }
}