<?php
namespace COPD\Main\Action;
use CLE;
use ADD_Content_Html;

class EditExternalRecordAction implements \ADD_Client_Action_GridRowActionInterface {
	private $editPage;
	private $sectionIdFieldName = "parent_encounter_id";
	private $patientIdFieldName = "patient_id";

	private $buttonText = "Edit..";
	private $icon = "motorcycle";
	private $btnClass = "btn btn-small btn-inverse";

	function __construct($editPage, $sectionIdFieldName="section_id") {
		$this->editPage = $editPage;
		$this->sectionIdFieldName = $sectionIdFieldName;
	}
	function setButtonText($buttonText) {
		$this->buttonText = $buttonText;
	}

    /**
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @param string $btnClass
     */
    public function setBtnClass($btnClass)
    {
        $this->btnClass = $btnClass;
    }

    /**
     * @param string $patientIdFieldName
     */
    public function setPatientIdFieldName($patientIdFieldName)
    {
        $this->patientIdFieldName = $patientIdFieldName;
    }

	function isAvailable() {
		return CLE::sys()->getPageFromPim($this->editPage)->getPermission('edit');
	}
    function outputMarkup() {
        $html = new ADD_Content_Html();
        $menuPath = $this->editPage->createMenuPath();
        $menuUri = $menuPath."?pid=' + row.".$this->patientIdFieldName. " + '&eid=' + row.".$this->sectionIdFieldName;
        $html->addTextElement("a", "<i class='fa fa-".$this->icon."'></i>&nbsp;".$this->buttonText, array("class"=>$this->btnClass,"ng-show"=>"row.".$this->patientIdFieldName." && row.".$this->sectionIdFieldName,"ng-click" => "gotoPage('".$menuUri.")"));
        return $html;
    }
	function getColumnHeading() {
		return "";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
}
?>

		