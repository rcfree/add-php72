<?php

abstract class ADD_Field_Base {
	private $_name = null;
	private $_settings = null;
	private $_type = null;
	private $_attributes=null;
	private $_size = null;
	protected $_value = null;
	protected $fieldData = null;
	protected $stored = true;
	private $_finalised = false;
	private $_idValue = null;
	private $_rowNo = null;
	private $_incomplete = false;
	private $_weight = 0;
	private $_default = null;
	private $_databaseId = null;
	protected $_readOnly = false;
	private $_hidden = false;
	private $_titleHidden = false;
	private $_html = null;
    private $_children = array();
	   
	function ignoreContainerTest() {
		return false;
	}
        
	function isTitleHidden() {
		return $this->_titleHidden;
	}
	function setTitleHidden($titleHidden) {
		$this->_titleHidden = $titleHidden;
	}
	function isHidden() {
		return $this->_hidden;
	}
	function setHidden($hidden,$page) {
		$this->_hidden = $hidden;
	}
	function isReadOnly() {
		return $this->_readOnly;
	}
	function setReadOnly($readOnly) {
		$this->_readOnly = $readOnly;
	}
	
	function setDatabaseId($databaseId) {
		$this->_databaseId = $databaseId;
	}
	
	function getDatabaseId() {
		return $this->_databaseId;
	}
	function setIncomplete($incomplete) {
		$this->_incomplete = $incomplete;
	}
	function isIncomplete() {
		return $this->_incomplete;
	}
	function setRowNo($rowNo) {
		$this->_rowNo = $rowNo;
	}
	function getRowNo() {
		return $this->_rowNo;
	}
	
	function setIdValue($idValue) {
		$this->_idValue=$idValue;
	}
	
	function getIdValue() {
		return $this->_idValue;
	}
	
	public function isFinalised() {
		return $this->_finalised;
	}
	
	public function setFinalised($finalised) {
		$this->_finalised = $finalised;
	}

	public function isStored() {
		return $this->stored;
	}
	
	public function setStored($stored) {
		$this->stored = $stored;
	}
	
	public function getFieldData($itemName=null) {
		if ($itemName) {
				return isset($this->fieldData[$itemName]) ? $this->fieldData[$itemName] : null;
		}
		else {
				return $this->fieldData;
		}
	}

    public function setFieldData($itemName,$itemData) {
        $this->fieldData[$itemName]=$itemData;
    }
	public function setWeight($weight) {
		$this->_weight = $weight;
	}
	public function getWeight() {	
		return $this->_weight;
	}
	public function setSettings($settings) {
		$this->_settings = $settings;
	}
	
	public function getSetting($settingName, $default = null) {
		return isset($this->_settings[$settingName]) ? $this->_settings[$settingName] : $default;
	}
        
    public function assertSetting($settingName) {
            if (!isset($this->_settings[$settingName])) throw new ADD_FieldException("Setting '$settingName' is mandatory in field '".$this->getName()."' (type:".$this->getType().")".print_r($this,1));
            return $this->_settings[$settingName];
	}
	public function defaultSetting($settingName,$default) {
		if (!isset($this->_settings[$settingName])) $this->_settings[$settingName]=$default;
	}
	public function setSetting($settingName, $value) {
		$this->_settings[$settingName]=$value;
	}
	
	public function getSettings() {
		return $this->_settings;
	}
	
	public function setType($type) {
		$this->_type = $type;
	}

	public function deleteSetting($settingName) {
		if (isset($this->_settings[$settingName])) {
			unset($this->_settings[$settingName]);
		}
	}
	public function getType() {
		return $this->_type;
	}
	public function getTitle() {
		return $this->_title;
	}
	public function getAttributes() {
		return $this->_attributes;
	}
	
	public function setTitle($title) {
		$this->_title = $title;
	}
	public function setSize($size) {
		$this->_size = $size;
	}

	public function setDefault($default) {
		$this->_default = $default;
	}
	
	public function getDefault() {
		return $this->_default;
	}
	
	public function setRequired($required) {
		$this->_required = $required;
	}
	public function setAttributes($attributes) {
		$this->_attributes = $attributes;
	}
	public function setValue($value) {
		$this->_value = $value;
	}
	
	public function setLevel($level) {
		$this->_level = $level;
	}
	public function getLevel() {
		return $this->_level;
	}
	
	public function getValue() {
		return $this->_value;
	}

	public function getName() {
		return $this->_name;
	}
	
	public function setName($name) {
		$this->_name = $name;
	}
	
	function __construct($fieldData=array()) {
		//add fieldData to private value - used by extractLevelAndFieldName
		$this->fieldData = $fieldData;
		
		//extract field name and outline level (e.g. if text field in container then container level =1, text field level = 2
		list($level,$name) = ADD_Form_Util::extractLevelAndFieldName($this);
		if (!$name) throw new ADD_FieldException("Field name was not supplied in row:".var_export($fieldData,true));
		
		$this->setName($name);
		$this->setLevel($level);
		
		//store key information in accessors
		$this->setFieldData('Options',ADD_Util::getCsvArrayValues('Options',$fieldData));
		$type = ADD_Util::convertToPascal(ADD_Util::getArrayValue('Type',$fieldData,"text"));
		$this->setType($type);
		$this->setTitle(ADD_Util::getArrayValue('Title',$fieldData));
		$this->setAttributes(ADD_Util::getCsvArrayValues('Attributes',$fieldData));
	
		
		//less important data is added to settings accessor (e.g. required, minimum, maximum)
        
		$settings = $this->determineSettings($fieldData);
		
		$this->setSettings($settings);
		if ($this->getSetting('no_store')) $this->setStored(false);
	}
	function determineSettings($fieldData) {
		$settings = ADD_Util::getCsvArrayValues('Settings',$fieldData);
		if(!is_array($settings)) throw new ADD_FieldException("Settings in field '".$this->getName()."' are not an array");
		$initSettings = array("required","minimum","maximum","min","max","size","prefix","suffix","default");
       
		foreach($initSettings as $initSetting) {
			if (!isset($settings[$initSetting])) {
				$val = ADD_Util::getArrayValue(ucfirst($initSetting),$fieldData);
				if(ADD_Util::isEmpty($val)) continue;
				$settings[$initSetting]=$val;
			}
		}
		if (isset($settings['default'])) $this->setDefault($settings['default']);
		
		return $settings;
	}
	function beforePopulateFields($page) {}
	function afterPopulateFields($page) {}
	function finaliseField($page) {
		$this->setFinalised(true);
	}
	
	function populateValue($values, $page, $ignoreNew=false) {
		$add = ADD::sys();
		$fieldName = $this->getName();
		if ($this->getSetting('use_default') || (!$ignoreNew && $add->hasState('new') && !$add->hasState('view')) ) {
			$eval = ADD_Util::evaluate($this->getDefault());
			$this->setValue($eval);
			return;
		}
		if(isset($values[$fieldName])) {
			$this->setValue($values[$fieldName]);
		}
	}


	public function addJavascriptToFormStart(&$form) {}
	
	public function getDisplayText($page,$id) {
		return $this->getValue();
	}
	
	function getJQuery($page,$event,$code) {
		$add = ADD::sys();
		if (is_array($code)) $code = json_encode($code);
		$jquerySel = $this->getJQuerySelector($page);
		return $jquerySel.".{$event}({$code})";
	}
	
	function getJQuerySelector($page) {
		$add = ADD::sys();
		$name = $this->getName();
		if ($this->isMultiRecord($page) && $this->getIdValue()) {
			$idValue = $this->getIdValue();
			return "$('#{$name}-{$idValue}')";
		}
		else {
			return "$('{$this->generateJQueryFieldSel()}')";
		}
	}
	
	function getLabel($page) {
		$name = $this->getName();
		$add = ADD::sys();
		if ($this->isMultiRecord($page) && $this->getIdValue()) {
			$idValue = $this->getIdValue();
			return "#{$name}-{$idValue}";
		}
		else {
			return $name;
		}
	}
	
	function addJQuery($page,$event,$code) {
		$page->addJavascript($this->getJQuery($page,$event,$code));
	}
	
	function addJQueryOn($event,$code,$page) {
		$page->addJavascript($this->getJQuerySelector($page).'.on("'.$event.'",'.$code.')');
	}
	
	public function generateJQueryFieldSel() {
		return "[name=\"".$this->getName()."\"]";
	}
	public function generateJQueryBlockSel($page) {
		$name = $this->getName();
		if ($this->isMultiRecord($page) && $this->getIdValue()) {
			$idValue = $this->getIdValue();
			return ".content-$name-$idValue";
		}
		else {
			//$targetNameUs=preg_replace("/_/","-",$name);
			return ".content-$name";
		}
	}
	
	function createConditionJs($page, $readOnly=false) {
		
		$conditionTest = ADD_Util::makeArray($this->getSetting('condition_test'));
		if (count($conditionTest)==0) return null;
		
		$form = $page->getComponent('Form');
		
		$jqFldSel=$this->generateJQueryFieldSel();
		
		$conditionTargets = ADD_Util::makeArray($this->getSetting('condition_target'));
		$conditionActions=ADD_Util::makeArray($this->getSetting('condition_action'));
		$conditionTargetValues = ADD_Util::makeArray($this->getSetting('condition_target_value'));
		$conditionOps = ADD_Util::makeArray($this->getSetting('condition_op'));
		
		$ignoreClearContent = $this->getSetting('ignore_content',false);
		
		$conditionCount = 0;
		$opts = array();
		foreach($conditionActions as $conditionAction) {
			if (!isset($conditionTargets[$conditionCount])) {
				ADD::web()->addWarning("No condition_target[{$conditionCount}] provided for field ".$this->getName());
				continue;
			}
			$targetNames = explode(";",$conditionTargets[$conditionCount]);
			$conditionList = explode(";",$conditionTest[$conditionCount]);
			$conditionList = str_replace('"','',$conditionList);
			if (count($targetNames)==0) throw new ADD_FieldException("No 'condition_target' setting for condition number $conditionCount");
			$conditionTrue = "";
			$conditionFalse = "";
			$conditionOp = isset($conditionOps[$conditionCount]) ? $conditionOps[$conditionCount] : "eq";
			
			$opts[]=array("action" => $conditionAction, "test"=>$conditionList, "target"=>$targetNames, "value"=>ADD_Util::getArrayValue($conditionCount,$conditionTargetValues),"op"=>$conditionOp,"ignoreClearContent"=>$ignoreClearContent,"readOnly"=>$readOnly);
			$conditionCount++;
		}
		
		$js="ADD.assign('Condition','".$this->getName()."',".json_encode($opts, JSON_HEX_APOS | JSON_HEX_QUOT).")";
		return $js;
	}
	
	function addJsValidation($jsFunction, $page) {
		$add = ADD::sys();	
		if ($add->hasState('report')) return;
		$title = $this->getTitle();
		$form = $page->getComponent('Form');
		if ($this->getRowNo()) {
			$title.=" (row ".$this->getRowNo().")";
		}
		$settings = $this->getSettings();
		$settings['title']=$title;
		$args = json_encode($settings);
		
		$page->addJavascript("ADD.assign('$jsFunction','".$this->getLabel($page)."',".$args.");");
	}
	
	function addValue($newValue) {
		if ($newValue==$this->getValue()) return false;
		$this->setValue($newValue);
		return true;
	}
	function clearValue() {
		$this->setValue(null);
	}
	
	function removeValue($newValue) {
		$oldValue = $this->getValue();
		if (ADD_Util::isEmpty($oldValue)) return false;
		$this->setValue(null);
		return true;
	}

	public function removeFieldFromValues(&$values) {
		if (array_key_exists($this->getName(),$values)) unset($values[$this->getName()]);
	}
	function valueEqual($testValues) {
		if (in_array($this->getValue(),$testValues)) return true;
	}
	function createMarkup($page) {
		if($this->getSetting('hide_on_view') && ADD::sys()->hasState('readOnly')) return;
		$this->startFieldHtml($page);
		$this->beforeAddMarkup($page);	
		$this->addMarkup($page);
		$this->afterAddMarkup($page);
		
		$this->endFieldHtml($page);
		$add = ADD::sys();

		$page->addJavascript($this->createConditionJs($page,$add->hasState('readOnly')));
        $page->addJavascript($this->createAjaxJs($page));
	}
	
	function createAjaxJs($page) {
        $add = ADD::sys();
        if ($add->hasState('readOnly')) return;
        $exp = $this->getSetting('expression');
        $ajaxFunc = $this->getSetting('ajax-func');
        $fields = array();

        if (!$exp && !$ajaxFunc) return;

        if ($exp) {
            $fields = ADD_Field_Expression::extractFieldsFromExpression($exp, $page->getComponent("Form"));
        }
        
        $opts = array(
            "expression" => str_replace("|",",",$exp),
            "decimalPlaces" => $this->getSetting('fixed'),
            "fields"=>$fields,
			"asString"=>$this->getSetting("as_string")
        );
		if($this->getSetting("sync")) {
			$opts['sync']=1;
		}
        if ($ajaxFunc) {
            $apage = $add->getPageInModule('ajax','ajax');
            
            $acomp = $apage->getComponent('AjaxExecute');
            $fields = $acomp->createAjaxInputs($page,$ajaxFunc, $this->getName());
			
			$counter=0;
			if (!is_array($fields)) {
                $page->addWarning("Ajax inputs function '$ajaxFunc' not defined");
                return;
            }
			
			foreach($fields as $field) {
				if($fields[$counter] == "SELF") {
					$fields[$counter]= $this->getName();
				}
				$counter++;
			}
            
			$opts['modName']=$page->getModule()->getName();
			$opts['pageName']=$page->getName();
			$opts['funcName']=$ajaxFunc;
			$opts['fields']=$fields;
			$js = "ADD.assign('AjaxPopulate','" . $this->getName()."',".json_encode($opts).");";
		}
		else {
			$js = "ADD.assign('Calculate','" . $this->getName()."',".json_encode($opts).");";
		}
        return $js;
    }
	function beforeSubmit(&$values,$page) { }
	
	function afterAddFields($page) {
	}
	
	function beforeAddMarkup($page) {
		$add = ADD::sys();
		if ($this->getSetting('update_field')) {
			$fieldName = $this->getName();
			
			if ($this->isMultiRecord($page)) {
				$idValue = $this->getIdValue();
				if ($idValue) {
					$page->addJavascript("jQuery('#{$fieldName}-{$idValue}').change(function() { set('#".$this->getSetting('update_field')."-{$idValue}',get('#{$fieldName}-{$idValue}'));})");
				}
			}
			
		}
		
		if ($this->getSetting('viewonly')) {
			$this->setReadOnly(true);
		}
	}
	
	function isMultiRecord($page) {
		$multiRecord = false;
		if ($page->hasComponent('Content')) {
			$contComp = $page->getComponent('Content');
			
			if (get_class($contComp)=="ADD_Component_Content_InlineEntry") $multiRecord = true;
		}
		return $multiRecord;
	}
	function addMarkup($page) {
		$this->addLabelMarkup($page);
		$this->addFieldMarkup($page);
	}
	function addLabelMarkup($page) {}
	function addFieldMarkup($page) {}
	function afterAddMarkup($page) {}
	function startFieldHtml($page) {
		$html = $page->html();
		$add = ADD::sys();
		$classType = $this->getClassType();
		$name = $this->getName();
		if ($this->isMultiRecord($page) && $this->getIdValue()) {
			$idValue = $this->getIdValue();
			$name.="-{$idValue}";
		}
		$contentClass = substr($this->generateJQueryBlockSel($page),1);
		$fieldClass = $this->getSetting('hidden') ? "hidden" : "field";
		$html->startElement('div',array("class"=>"$fieldClass $classType $contentClass"),$name."_start");
		if ($add->config('show_field_names')) $html->addTextElement("span",$this->getName(),array("class"=>"label label-info","style"=>"font-weight:normal"));
	}
	function endFieldHtml($page) {
		$html = $page->html();
		$name = $this->getName();
		if ($this->isMultiRecord($page) && $this->getIdValue()) {
			$idValue = $this->getIdValue();
			$name.="-{$idValue}";
		}
		$html->endElement("div",$name."_end");
	}
	function getClassType() {
		$classType = "field-element";
		if($this->getSetting('indent')) $classType.=" field-indent";
		return $classType;
	}
	function createPdfLabel($rptField,$page,$currentX,$currentY) {
		list($fontSize, $lineHeight) = $rptField->getLineHeight(10);
		
		$pdf = $page->getPdf();
		$value = $this->getDisplayText($page,null);
		$title = $rptField->getTitle();
		if (!$title) $title = $this->getTitle();
		if ($title=="&rarr;") {
			$title=chr(187);
		}

		$pdf->SetFont('Arial','B',$fontSize);
		$labelAbove = $this->getSetting('label-above',null);
		
		$labelWidth = $pdf->GetStringWidth($title);
		$pdf->SetXY($currentX,$currentY);
		
		$valueHeight = $rptField->getSetting('height',$lineHeight * 1.55);
		$pdf->Write($valueHeight,$title);
		return $labelWidth;
	}
	function createPdfElement($rptField,$page,$currentX,$currentY) {
		$labelWidth = $this->createPdfLabel($rptField,$page,$currentX,$currentY);
		$pdf = $page->getPdf();
		$gap = $rptField->getSetting('gap',2);
		
		list($fontSize, $lineHeight) = $rptField->getLineHeight(10);
		$labelAbove = $rptField->getSetting('label-above',null);
		
		$valueHeight = $rptField->getSetting('height',$lineHeight * 1.55);
		
		$pdf->SetFont('Arial','',$fontSize);
		$border = $rptField->getSetting('border',1);
		
		$value = $page->getReportConfig()->isEmpty() ? str_repeat($border ? " " : "_",$this->getSetting('size',$rptField->getSetting('length')," ")) : $this->getDisplayText($page,null);
		
		$valueWidth = $rptField->getValueWidth($value,$page);
		
		$fill = $rptField->getSetting('no-fill') ? false : true;
		
		$pdf->SetDrawColor(120,120,120);
		$pdf->SetFillColor(246,246,246);
		
		$width = $rptField->calcWidth($labelWidth,$valueWidth,$labelAbove);
		if ($labelAbove) {
			$pdf->SetX($labelWidth+$currentX+$gap);
			
			$pdf->Cell($valueWidth+5,$valueHeight,$value,$border,1,"L",$fill);
			return array($width + $gap, $valueHeight);
		}
		else {
			$pdf->SetY($currentY+($lineHeight * 1.8));
			
			if ($border) {
				$pdf->SetX($currentX+$gap);
				$pdf->Cell($valueWidth+5,$valueHeight,$value,$border,1,"L",$fill);
				$pdf->SetY($currentY+($lineHeight * 1.8));
				$pdf->SetX($currentX+$gap+$valueWidth+5);
				$suffix = $this->getSetting('suffix');
				if ($suffix && strlen($value)>0) {
					$suffix = strip_tags($suffix);
					$pdf->Write($valueHeight,$suffix);
					$width+=$pdf->GetStringWidth($suffix);
				}
			}
			else {
				$pdf->SetX($currentX);
				$suffix = $this->getSetting('suffix');
				if ($suffix && strlen($value)>0) {
					$suffix = strip_tags($suffix);
					$value.=" ".$suffix;
					$sufWidth = $pdf->GetStringWidth($suffix);
					$valueWidth.=$sufWidth;
					
				}
				$width=$valueWidth;
					
				$pdf->Cell($valueWidth,$valueHeight,$value,$border,1,"L",$fill);
			}
			$height = $valueHeight + ($lineHeight * 1.8);
			return array($width,$height + $gap);
		}
	}
	function calcPdfHeight($rptField,$page) {
		list($fontSize, $lineHeight) = $rptField->getLineHeight(10);
		$valueHeight = $rptField->getSetting('height',$lineHeight * 1.55);
		$gap = $rptField->getSetting('gap',2);
		
		$labelAbove = $rptField->getSetting('label-above',null);
		
		if ($labelAbove) {
			return $valueHeight;
		}
		else {
			return $valueHeight + ($lineHeight * 1.8) + $gap;
		}
	}
	function setup($page) {
	}
	function _setHiddenFields($page) {
		$form = $page->getComponent('Form');
                
		if ($this->getLevel()=="DEL") return;
                
		$conditionTests = ADD_Util::makeArray($this->getSetting('condition_test'));
		$conditionTargets = ADD_Util::makeArray($this->getSetting('condition_target'));
		
		$conditionActions = ADD_Util::makeArray($this->getSetting('condition_action'));
		
		$conditionCount = 0;
		$hiddenFields = array();
		foreach($conditionActions as $conditionAction) {
			if (!isset($conditionTargets[$conditionCount])) {
				ADD::web()->addWarning("Field '{$this->getName()}' setting condition_target[$conditionCount] not defined");
				continue;
			}
			$targetNames = explode(";",$conditionTargets[$conditionCount]);
			$conditionArray = explode(";",$conditionTests[$conditionCount]);
			$counter = 0;
			$targetFields = array();

			foreach($targetNames as $targetName) {
				if($form->hasGeneratedFieldName($targetName)) return;

				$targetField = $form->getField($targetName);
				if (!$targetField || $targetField->getLevel()=="DEL") {
					if($this->getSetting('ignore_condition_error')) return;
					ADD::web()->addWarning("Field '{$this->getName()}' setting condition_target[$conditionCount] not defined");
					continue;
				}
			}			
		
			foreach($conditionArray as $conditionItem) {
				$conditionArray[$counter] = ADD_Util::evaluate($conditionItem);
				$counter++;
			}
				
			if ($conditionAction=='show') {
				if ($this->valueEqual($conditionArray)) continue;
			}
			elseif ($conditionAction=='hide') {
				if (!$this->valueEqual($conditionArray)) continue;
			}
			else {
				continue;
			}
			$conditionCount++;
            foreach($targetNames as $targetName) {
				$targetField = $form->getField($targetName);
				if (!$targetField) {
					continue;
				}
				$targetField->setHidden(true,$page);
				$hiddenFields[]=$targetField;
			}       
		}
		return $hiddenFields;
	}
	function updateExistingData($dbField) {
		return true;
	}
	function saveChangedObservations($newValues, $obs) {
		$edb = ADD::db('encounter');
		$eh = new ADD_Database_Util_EncounterHelper();
		foreach($obs as $ob) {
			if (!isset($newValues[$ob['id']])) continue;
			$newValue = $newValues[$ob['id']];
			$updObs = $eh->populateObsData($ob,$newValue);
			$edb->updateObservation($updObs);
		}
	}
	function afterAllGenerate($page) {
		$add = ADD::sys();
		
		if ($this->isTitleHidden()) return;
		if ($add->hasState('readOnly')) return;
		if ($this->getSetting('viewonly')) return;
		if (!$this->isStored()) return;
		if ($this->isReadOnly()) return;
		$html = $page->html(); 
		$fieldName = $this->getName();
		
		$label = $html->get($fieldName."_label");
		if ($label) {
			$link = ADD::web()->newContent();
            $clearOpt = $this->getSetting('clear_option','clear..');
			if ($clearOpt!='x') {
				$link->addTextElement("a",$clearOpt,array("class"=>"ignoredirty clear-link","href"=>"javascript:;","onclick"=>"window.clear('".$fieldName."')"));
			}
			$label['text'].= " ".$link->toHtml()." ";
			$html->set($fieldName."_label",$label);
		}
	}

    static function extractFieldsFromExpression($exp, $form) {
        $fields = array();
        preg_match_all('/get\([\'|\"].+?[\'|\"]\)/',$exp,$fields);
        $uniqueFlds = array();
        if (count($fields)>0) {
            foreach($fields[0] as $field) {
                $fieldMatch = array();
                preg_match('/get\([\'|\"](.+)[\'|\"]\)/',$field,$fieldMatch);
                if (count($fieldMatch)>0) {
                    $uniqueFlds[$fieldMatch[1]]=true;
                }
            }
        }
        preg_match_all('/sum\([\'|\"].+?[\'|\"]\)/',$exp,$fields);

        if (count($fields)>0) {
            foreach($fields[0] as $field) {
                $fieldMatch = array();
                preg_match('/sum\([\'|\"](.+)[\'|\"]\)/',$field,$fieldMatch);
                if (count($fieldMatch)>0) {
                    $fieldList = explode(";",$fieldMatch[1]);
                    foreach($fieldList as $fieldItem) {
                        $uniqueFlds[$fieldItem]=true;
                    }
                }
            }
        }
        $fieldNames = array_keys($uniqueFlds);
        $finalFields = array();
        foreach($fieldNames as $fieldName) {
        	$field = $form->getField($fieldName);
        	if(is_a($field,"\ADD_Field_Container")) {
        		$finalFields = array_merge($finalFields, array_map(function($f) { return $f->getName(); }, $form->getChildFields($field)));
        		continue;
			}
			$finalFields[]=$field->getName();
		}
		return $finalFields;
    }
	public function addChild($field) {
		$this->_children[]=$field;
	}
	public function getChildren() {
		return $this->_children;
	}
	public function beforeInstall(ADD_Form_FieldContainerInterface $form) {
	}
        
        public function getDataType() {
            return "varchar";
        }
        
       public function getDataLength() {
            return $this->getSetting("size",30);
        }
        function createAngularCell() {
        	$fieldText = "row.".$this->getName().".label";
			return '<span ng-bind-html="' . $fieldText . '"></span>';
		}
}	
?>
