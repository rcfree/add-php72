<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 07/09/16
 * Time: 14:16
 */

namespace ClinicLE\Client\Panel;
use CLE;
use ADD_Content_Html;

class TopPanel extends AbstractPanel
{
    function generateContent($values = array()) {
        $event = new \ClinicLE\System\Event\ContentEvent(new ADD_Content_Html());

        CLE::sys()->getEventDispatcher()->dispatch(\ClinicLE\System\Event\ContentEvents::TOP_PANEL, $event);
        return $event->getContent();
    }

}