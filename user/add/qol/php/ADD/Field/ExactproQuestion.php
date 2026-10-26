<?php

class ADD_Field_ExactproQuestion extends ADD_Field_List {
    public function beforeInstall(ADD_Form_FieldContainerInterface $form)
    {
        $level = $this->getLevel();

        $days = range(1,7);
        foreach($days as $day) {
            $existingField = $form->getField($this->getName() . "_day$day");
            if($existingField) {
                if($this->getLevel()=="DEL") {
                    $existingField->setLevel("DEL");
                }
                continue;
            }
            $field = new ADD_Field_Hidden(array("Field" => $this->getName() . "_day$day", "Level"=>$level,
                "Required"=>1));

            $form->addField($field);
        }
    }

    function addMarkup($page) {
		$add = ADD::sys();
		$html = $page->html();
		$radioClass = "radio";
		$ocount = 1;
        $days = range(1,7);


        $html->startElement("table",array("class"=>"table table-condensed table-striped","style" => "font-weight:bold;width:100%"));

		$html->startElement("thead");
		$html->startElement("tr");
		$html->addTextElement("td",$this->getTitle(),array("style"=>"width:50%"));

        foreach($days as $day) {
            $html->addTextElement("td", "Day" . $day, array("style" => "font-weight:bold"));
        }
		$html->endElement("tr");
		$html->endElement("thead");

		$html->startElement("tbody");

        foreach($this->getOptions()->getOptionList() as $value => $label) {

            $html->startElement("tr");
            $html->addTextElement("td",$label,array("style"=>"width:50%"));
            foreach($days as $day) {
                $html->startElement("td",array("class"=>"content-".$this->getName()."_day$day"));

                $attrs = array("name" => $this->getName()."_day$day", "type" => "radio", "value" => $value,
                "autocomplete" =>
                    "no",);
                if ($add->hasState('readOnly') || $this->getSetting('viewonly'))
                    $attrs['disabled'] = 'disabled';
                if ($this->getOptions()->countOptions() == $ocount)
                    $radioClass = $radioClass . " last";

                $dayValue = $this->getDayValue($page,$day);
                if (strlen($dayValue) > 0 && $dayValue == $value)
                    $attrs['checked'] = "checked";

                $html->addElement("input", $attrs);
                $html->endElement("td");
            }
			$html->endElement("tr");
		}
		$html->endElement("tbody");
		$html->endElement("table");


	}
	function getDayValue($page,$day) {
        $form = $page->getComponent('Form');
        $field = $form->getField($this->getName()."_day$day");
        return $field->getValue();
    }
}
?>