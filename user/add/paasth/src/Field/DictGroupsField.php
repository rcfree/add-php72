<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 20/06/18
 * Time: 20:34
 */

namespace ADD\Paasth\Field;


use ADD_Form_FieldContainerInterface;
use ClinicLE\System\Factory\SystemFactories;
use ClinicLE\System\Service\CoreServices;
use CLE;

class DictGroupsField extends \ADD_Field_Container
{
    function beforeInstall(ADD_Form_FieldContainerInterface $form)
    {
        $ddb = CLE::db('dictionary');
        $dictName = $this->assertSetting("dictionary");
        $dictGroups = $ddb->getDictionaryGroups($dictName);

        $rff = CLE::sys()->getFactory(SystemFactories::ROW_FIELD);
        $defaultToNo = $this->getSetting("default_to_no");
        $groupFilter = $this->getSetting("group_filter");

        if ($groupFilter) {
            $groupFilter = explode(";",$groupFilter);
            $newDictGroups = array();
            foreach($groupFilter as $groupName) {
                $dictGroup = array_filter($dictGroups, function($x) use($groupName) { return $x['name']==$groupName; });
                if(count($dictGroup)==0) {
                    continue;
                }
                $newDictGroups[]=array_values($dictGroup)[0];
            }
            $dictGroups = $newDictGroups;
        }

        foreach($dictGroups as $dictGroup) {
            $fieldPrefix = $dictGroup['name'];
            if (!in_array($fieldPrefix,$groupFilter)) {
                continue;
            }

            $hcParams = array(
                "Field"=>$fieldPrefix."_hc",
                "Type"=>"Container"
            );

            $yesNoParams = array(
                "Title"=>$dictGroup['title'],
                "Field"=>$fieldPrefix."_flag",
                "Type"=>"YesNo",
                "Required"=>1,
                "Settings"=>"condition_test=Y,condition_target=".$fieldPrefix."_meds,condition_action=show"
            );
            if($defaultToNo) {
                $yesNoParams['Settings'].=",default=N";
            }
            $medParams = array(
                "Field"=>$fieldPrefix."_meds",
                "Type"=>"Checkboxes",
                "Settings"=>"indent=1,columns=4,inline=1,dictionary_option=DictionaryGroupItems,dictionary=$dictName,dictionary_group=".$dictGroup['name']
            );
            $hc = $rff->newInstance("Container",array($hcParams));
            $yn = $rff->newInstance("YesNo",array($yesNoParams));
            $rb = $rff->newInstance("RadioButtons",array($medParams));

            $form->addChildField($this,$hc);
            $form->addChildField($hc,$yn);
            $form->addChildField($hc,$rb);

            \CLE::info("Create Y/N field " . $dictGroup['title']);
        }
    }
}
