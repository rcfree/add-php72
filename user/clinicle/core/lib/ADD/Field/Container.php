<?php

class ADD_Field_Container extends ADD_Field_Base {
	protected $stored = false;
	public $validated=false;
	
	function setHidden($hidden,$page) {
		parent::setHidden($hidden,$page);
		if (!$hidden) return;
		$form = $page->getComponent('Form');
		$childFields = $form->getChildFields($this);
		if ($childFields) {
			foreach($childFields as $childField) {
				$childField->setHidden($hidden,$page);
			}
		}
	}
	
	
	function addMarkup($page) {
		$form = $page->getComponent('Form');
		$childFields = $form->getChildFields($this);
		if ($childFields) {
			foreach($childFields as $childField) {
					$this->beforeAddChildHtml($childField,$page);
					$childField->createMarkup($page);
					$this->afterAddChildHtml($childField,$page);
			}
		}
	}
	function getClassType() {
		$classType = "container-element";
		if($this->getSetting('indent')) $classType.=" field-indent";
		return $classType;
	}
	function afterAddFields($page) {
		$form = $page->getComponent('Form');
		$html = $page->html();
		$childFields = $form->getChildFields($this);
		if ($childFields) {
			foreach($childFields as $childField) {
				$childField->afterAddFields($page);
			}
		}
	}
	
	function beforeAddChildHtml($childField,$page) { }
	function afterAddChildHtml($childField,$page) { }
	
}
?>