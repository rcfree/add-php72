<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:16
 */

namespace ClinicLE\Page\PageResponse;
use CLE;
use ClinicLE\System\Util\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectPageResponse extends RedirectResponse
{
    function send()
    {
        $web = CLE::web();
        $session = CLE::sys()->getAccessManager()->getSession();
        $session->setUserData('cle_messages', $web->getMessages());
        $session->setUserData('cle_warnings', $web->getWarnings());
        return parent::send();
    }
}