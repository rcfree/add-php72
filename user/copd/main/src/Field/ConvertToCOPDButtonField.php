<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace COPD\Main\Field;

use CLE;
use ClinicLE\System\Service\CoreServices;


/**
 * Description of AddContactButtonField
 *
 * @author rcf8
 */
class ConvertToCOPDButtonField extends ConvertToReferralButtonField {

    function manageEncounterCreation($referralSection, $sourceId) {
        $dm = CLE::sys()->getDataManager();
        $prSection = $dm->sectionById($sourceId);
        $prSection->retrieve();
        $this->copyComorbsAndMeds($prSection, $referralSection);

        $locale = \CLE::svc(CoreServices::LOCALE);

        $referral = $dm->newChild($referralSection, "copd-visit-admission");
        $referral->setObsValues(array(
            $this->targetIdField=>$sourceId,
            "date_seen"=>$locale->date("d/m/Y")));

        $referral->store();
    }
}
