<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/08/16
 * Time: 15:44
 */

namespace ADD\Research\Resource;
use ClinicLE\Manage\Event\ManageEvents;
use Pimple\Container;
use ClinicLE\System\Event\EventDispatcher;
use CLE;

class ResearchResource
{
    function initResource(Container $services, EventDispatcher $events) {
        $events->addListener(ManageEvents::DATA_VIEW,array($this,"onDataView"));
    }
    function onDataView($event) {
        if($event->getOptions()->get("r")!="res") {
            return;
        }

        $cdb = new \ADD_Database_Consent();
        $cdb->createConsentRecord();

        $sql = "select * from consent_record";
        if($event->getOptions()->get("l")) {
            $sql.=" limit ".$event->getOptions()->get("l");
        }
        $rows = $cdb->fetchAll($sql);
        $event->setTitle("database consent records");
        $event->setSubTitle("database consent record");
        $event->setData($rows);
        $event->setIdFieldName("ilh_identifier");
        $event->setFields(array("patient_id" => 10,"consent_form_date" => 10, "add_to_recruit_db" => 10,"add_to_research_db" => 10,"current_status"=> 15,"ilh_identifier"=>10));
    }
}