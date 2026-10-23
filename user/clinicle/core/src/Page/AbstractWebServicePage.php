<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 16/11/2016
 * Time: 17:23
 */

namespace ClinicLE\Page;
use ClinicLE\Common\Exception\SystemUserException;
use ClinicLE\Common\Exception\UserExceptionInterface;
use ClinicLE\Page\PageResponse\PageResponseUtils;
use Symfony\Component\HttpFoundation\Response;
use ClinicLE\Common\Log;
#use ClinicLE\Security\Access;
use CLE;
use ClinicLE\Page\PageResponse\ContentPageResponse;

abstract class AbstractWebServicePage extends AbstractPage
{

    function processError(UserExceptionInterface $ex) {

        $data = $ex->outputError(true);
        CLE::info("error data",$data);
        $data["error"]=1;

        return $data;
    }
    function finaliseOutput($output) {
        $application = CLE::web();
        if(!$output) {
            $output=array();
        }
        if(Log::isDebugOn()) {
            $output['debug']=Log::outputDebug(true);
        }
        if ($application->getMessages()) {
            $output['message']=1;
            $output['message_text']=implode("\n",$application->getMessages());
        }
        if ($application->getWarnings()) {
            $output['warning']=1;
            $output['warning_text']=implode("\n",$application->getWarnings());
        }
        $jsonOutput = new \ADD_Content_Json();
        $jsonOutput->setJsonData($output);
        $response = new ContentPageResponse(
            $jsonOutput,
            Response::HTTP_OK
        );
        return $response;
    }
    function createAccess()
    {
//        return new Access();
    }
    function createTitle() {
        return null;
    }

}
