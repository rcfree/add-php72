<?php
class ADD_Component_DesignLink extends ADD_Component {
	function beforeGenerate($page) {
		$form = $page->getComponent('Form');
		$modal = ADD_FieldFactory::fromFieldData(
			array(
				'Field'=>'design_modal',
				'Type'=>'encounter_modal',
				'Level'=>1
		));
		$form->addField($modal);
		$name = ADD_FieldFactory::fromFieldData(
			array(
				'Field' => 'name',
				'Title'=>'Field Name',
				'Type'=>'text',
				'Level'=>2
		));
		
		$title = ADD_FieldFactory::fromFieldData(
			array(
				'Field' => 'title',
				'Title'=>'Title/Label',
				'Type'=>'text',
				'Level'=>2
		));
		
		$options=array("HorizContainer"=>"HorizContainer","Text"=>"Text","Date"=>"Date","Container"=>"Container");
		$type = ADD_FieldFactory::fromFieldData(
			array(
				'Field' => 'type',
				'Title'=>'Field Type',
				'Type'=>'dropdown',
				'Options'=>$options,
				'Level'=>2
		));
		
		$level = ADD_FieldFactory::fromFieldData(
			array(
				'Field' => 'level',
				'Title'=>'Field Level',
				'Type'=>'number',
				'Min'=>1,
				'Max'=>10,
				'Level'=>2
		));
		
		$options = ADD_FieldFactory::fromFieldData(
			array(
				'Field' => 'options',
				'Title'=>'Options',
				'Type'=>'text',
				'Level'=>2
		));
		
		$settings = ADD_FieldFactory::fromFieldData(
			array(
				'Field' => 'settings',
				'Title'=>'Settings',
				'Type'=>'text',
				'Level'=>2
		));
		
		$form->addField($name);
		$form->addField($title);
		$form->addField($type);
		$form->addField($level);
		$form->addField($options);
		$form->addField($settings);
		$form->_prepareChildrenAndTopLevelFields();
	}
	
	function afterGenerate($page) {
		$form = $page->getComponent('Form');
		$html = $page->html();
		
		foreach($form->getFieldsByName() as $name => $field) {
			$modalAction = array(
				"name"=>"design_modal",
				"action"=>"edit",
				"elementId"=>"design_modal",
				"description"=>"Field",
				"beforeFunctionName"=>null,
				"afterFunctionName"=>null,
				"ajax"=>array(
					"path"=>"/add-ci/index.php/api/field",
					"query"=>array("name"=>$field->getName(),"encounter"=>$page->getSetting('encounter'))
				)
			);
			$jsFunction = "modalEntry(".json_encode($modalAction).")";
			$data=array(
				"tag"=>"div",
				"attrs"=>array("style"=>'border:dotted 1px #dddddd;padding:0.2em'),
				"text"=>"<span class=\"label\" style='float:right'><a style=\"color:white\" href='#' onclick='$jsFunction'>".$field->getType()."</a></span>",
				"type"=>ADD_Content_Html::START
			);
			$html->addAfterElement($name."_start",$data);
			$html->addBeforeElement($name."_end",array("tag"=>"div","type"=>ADD_Content_Html::END));
		}
	}
}
