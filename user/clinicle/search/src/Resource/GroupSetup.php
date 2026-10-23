<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 09:12
 */

namespace ClinicLE\Search\Resource;


use ClinicLE\Search\Group\AuditGroup;
use ClinicLE\Search\Group\AvailableRecruitConsentedGroup;
use ClinicLE\Search\Group\BasicDetailsPredefinedGroup;
use ClinicLE\Search\Group\CustomQueryGroup;
use ClinicLE\Search\Group\GroupSet;
use ClinicLE\Search\Group\IdentPatientPageGroup;
use ClinicLE\Search\Group\AnonPatientPageGroup;

use ClinicLE\Search\Group\EncounterPageGroup;
use ClinicLE\Search\Group\LivingPatientsGroup;
use ClinicLE\Search\Group\ResearchGroup;
use ClinicLE\Search\Group\RecruitConsentedResearchGroup;

class GroupSetup
{
    static function setup(GroupSet $groupSet,SearchablePages $spages, $overrideAccess=false) {

        $modules = $spages->getModules();

        $patientInfoAdded = false;

        $groupSet->setCategory("common", "Commonly used");
        $living = new LivingPatientsGroup();
        $groupSet->addGroup($living);
        $demogPage = $modules->getPageInModule("patient","demographics");
        if($demogPage->getPermission("ident_search") || $overrideAccess === true) {
            $pgroup = new IdentPatientPageGroup($modules);
            $bgroup = new BasicDetailsPredefinedGroup();
            $groupSet->addGroup($bgroup);
            $groupSet->addGroup($pgroup);
            $patientInfoAdded = true;
        }
        if($demogPage->getPermission("anon_search") || $overrideAccess === true) {
            $pgroup = new AnonPatientPageGroup($modules);
            $groupSet->addGroup($pgroup);
            $patientInfoAdded = true;
        }

        if($modules->get("research")) {
            $consentPage = $modules->getPageInModule("research", "details");

            if ($consentPage->getPermission("search") || $overrideAccess === true) {
                $rgroup = new ResearchGroup();
                $groupSet->addGroup($rgroup);

                $groupSet->setCategory("research_db", "Research database information");

                $rgroup2 = new AvailableRecruitConsentedGroup();
                $groupSet->addGroup($rgroup2);


                //$groupSet->setCategory("research2", "Research database information2");
            }
        }

        $customSearch = \CLE::sys()->getPageFromPim("search:group");

        if($customSearch->getPermission("custom_search") || $overrideAccess === true) {
            $cgroup = new CustomQueryGroup();
            $groupSet->addGroup($cgroup);
            $groupSet->setCategory("custom", "Custom query");
        }
/*
        if($customSearch->getPermission("audit_search") || $overrideAccess === true) {
            $agroup = new AuditGroup();
            $groupSet->addGroup($agroup);
            $groupSet->setCategory("audit","Audit records");
        }
*/
        if($patientInfoAdded) {
            $groupSet->setCategory("patient", "Patient information");
        }


        foreach($spages->getAvailablePages() as $page) {
            $group = new EncounterPageGroup($page);
            $groupSet->addGroup($group);
        }
        foreach($spages->getAvailableModules() as $module) {
            $categoryTitle = $module->getSetting("title");
            if (!$categoryTitle) {
                $topPages = $module->getTopPages();
                $categoryTitle = count($topPages) == 0 ? $module->getAlias() : $topPages[0]->getSetting("title");
            }
            $groupSet->setCategory($module->getAlias(), $categoryTitle);
        }
    }
}