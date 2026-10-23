<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 19/07/18
 * Time: 20:11
 */

namespace COPD\Main\Field;


class ReferralField extends \ADD_Field_Container
{
    function beforeInstall(\ADD_Form_FieldContainerInterface $form) {
        $ff = new \ADD_FieldFactory_RowData();
        $referralPim = $this->assertSetting("referral_pim");
        $convertFieldType = $this->assertSetting("convert_field_type");
        $targetIdField = $this->assertSetting("target_id_field");
        $successMessage = $this->getSetting("referral_success_message");
        $referralIdFieldName = $this->getName()."_id";
        $referralIdField = $ff->newInstance(array("Type"=>"hidden","Field"=>$this->getName()."_id"));
        $convertToButton = $ff->newInstance(array("Title"=>$this->getTitle(),"Type"=>$convertFieldType,"Field"=>$this->getName()."_convert","Settings"=>array("referral_id_field"=>$referralIdFieldName, "referral_pim"=>$referralPim,"target_id_field"=>$targetIdField)));
        $gotoButton = $ff->newInstance(array("Title"=>$this->getTitle(),"Type"=>"goto_referral_button","Field"=>$this->getName()."_goto","Settings"=>array("success_message"=>$successMessage, "referral_pim"=>$referralPim,"referral_id_field"=>$referralIdFieldName)));

        $form->addChildField($this,$referralIdField);
        $form->addChildField($this,$convertToButton);
        $form->addChildField($this,$gotoButton);
    }
}