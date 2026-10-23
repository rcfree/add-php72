<?php
namespace ClinicLE\Ajax;

    use Symfony\Component\HttpFoundation\Request;
    use Clinicle\Page\AbstractPage;
    use Clinicle\System\System;


abstract class AbstractAjaxFunction
{
    private $callingPage;
    public function __construct($callingPage) {
        $this->callingPage = $callingPage;
    }
    function getCallingPage() {
        return $this->callingPage;
    }
    abstract function runFunction(Request $request);
    public function isAvailable() {
        return true;
    }

    abstract function getInputFields();

}