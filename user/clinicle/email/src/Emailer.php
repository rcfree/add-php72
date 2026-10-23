<?php

namespace ClinicLE\Service\Email;
use ClinicLE\Service\Email\Exception\EmailSendException;

class Emailer {

	function sendTo(Email $email,$toAddress) {
		
        $sent = mail($toAddress, $email->getSubjectLine(), $email->getBodyAsText(), $email->getFromLine());

        if (!$sent) {
            throw new EmailSendException("Unable to send the email '".$email->getSubjectLine()."' to ".$email->getFromAddress());
        }
    }
}
