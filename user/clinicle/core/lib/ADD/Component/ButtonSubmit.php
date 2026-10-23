<?php

/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 18/10/2016
 * Time: 16:57
 */
class ADD_Component_ButtonSubmit extends ADD_Component
{
    public function submit($values) {
        $page = $this->_page;

        $form = $page->getComponent('Form');

        foreach($form->getFields() as $field) {
            \CLE::info("field",$field);
            if (is_subclass_of($field,"ADD_Field_SubmitInterface")) {
                $field->submitValues($values,$page);
            }
        }

        return array('redirect'=>$page->createMenuUri());
    }

}