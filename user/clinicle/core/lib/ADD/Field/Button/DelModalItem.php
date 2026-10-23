<?php

class ADD_Field_Button_DelModalItem extends ADD_Field_Button {
	protected function getOnClick($page) {
            $args = array(
                "action"=>"delete",
                "id"=>$this->getIdValue(),
                "lid"=>$page->getModule()->getName(),
                "fid"=>$page->getName(),
                "description"=>$page->getSetting('single'),
                "pid"=>ADD::sys()->getPatientId()
            );
            $json = json_encode($args);
            $json = str_replace('"',"'",$json);
            return "modalEntry($json)";
	}
	protected function getLinkAttrs($page) {
            return array(
                    "class"=>"btn btn-small btn-danger",
                    "type"=>"button"
            );
	}
	protected function getLinkTitle($page) {
            return "<i class='icon-remove icon-white'></i>";
	}
        public function getRequiredPermission() {
            return "delete";
        }
}
?>