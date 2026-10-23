<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 08/08/17
 * Time: 12:46
 */

namespace ClinicLE\Search\Output;


use ClinicLE\Service\Email\Email;

class EmailSearchExportOutput extends SearchExportOutput
{
    private $emailAddress;
    private $siteHost;
    private $emailer;

    /**
     * @return mixed
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param mixed $emailAddress
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return mixed
     */
    public function getEmailer()
    {
        return $this->emailer;
    }

    /**
     * @param mixed $emailer
     */
    public function setEmailer($emailer)
    {
        $this->emailer = $emailer;
    }

    /**
     * @return mixed
     */
    public function getSiteHost()
    {
        return $this->siteHost;
    }

    /**
     * @param mixed $siteHost
     */
    public function setSiteHost($siteHost)
    {
        $this->siteHost = $siteHost;
    }

    private function sendEmail($fileName) {
        $email = new Email("Search export results","ADD export","no-reply@uhl-tr.nhs.uk");
        $body = "Your export has completed. The data file can be downloaded from:\n\n";
        $body.="http://".$this->getSiteHost()."/search/download?file=".$this->getExportPath()."/".$this->getFileName();

        $email->setBody($body);

        \CLE::info("sendEmail to ".$this->getEmailAddress());
        $this->getEmailer()->sendTo($email,$this->getEmailAddress());
    }
    function generateFinalResponse($exportFileName) {
        parent::generateFinalResponse($exportFileName);
        $this->sendEmail($exportFileName);
    }
}