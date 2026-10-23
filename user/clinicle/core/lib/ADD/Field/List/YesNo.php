<?php

class ADD_Field_List_YesNo extends ADD_Field_List_RadioButtons {
    public function finaliseField($page) {
        $this->setFieldData('Options',array('Y'=>'Yes','N'=>'No'));
        $this->setSetting('inline',1);
        parent::finaliseField($page);
    }
}
?>