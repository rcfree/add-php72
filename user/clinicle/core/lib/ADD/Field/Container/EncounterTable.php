<?php

class ADD_Field_EncounterTable extends ADD_Field_Container_MultiEncBase {
	public function ignoreContainerTest() {
            return true;
        }
	function generateTable($page) {
		$add = ADD::sys();
		$edb = ADD::db('encounter');
		$form = $this->getContainingForm($page);
		$rowEncs = $this->getRowEncounters($page);
		$header = array();
		$rows = array();
		$colFieldNameList = $this->getSetting('cols');
		if (empty($colFieldNameList)) throw new ADD_FieldException("No 'cols' setting for EncounterTable field");
		$colFieldNames = $colFieldNameList ? explode(";",$colFieldNameList) : array();
		$colFields = array();
		
		foreach($colFieldNames as $colFieldName) {
			$field = $form->getField($colFieldName);
			if (!$field) throw new ADD_FieldException("Field name '$colFieldName' specified as column in EncounterTable not found in form definition");
			$colFields[] = $field;
		}
		if($this->getSetting('date_recorded')) $header[]="Record date";
		
		foreach($colFields as $colField) {
			$colTitle = $colField->getSetting('col_title');
			$header[]=$colTitle ? $colTitle : $colField->getTitle();
		}
		$filterField = $this->getSetting('filter_field');
		foreach($rowEncs as $encId => $er) {
			$row = array();
			
                        $er->retrieve();
			$values = $er->getObsValues();
				
			if ($filterField) {
				if (!$er->getObsValue($filterField)) {
					$page->addWarning("Filtered observation '".$filterField."' is missing from record (ID:$encId)");
					continue;
				}
				if ($values[$filterField]!=$this->getSetting('filter_value')) continue;
			}
			
			$data = array();
			if($this->getSetting('date_recorded')) {
				$data[]=ADD_Util::dateFromISOToDMY($er->getEncData('date_created'));
			}
			foreach($colFields as $colField) {
				$data[] = $this->_getCellData($encId, $colField, $values,$page);
			}  
			
			$row['data']=$data;
			
			$condition = $this->getSetting('condition');
			if ($condition) {
				$conditionsList = explode(";",$condition);
				$conditionField = $this->getSetting('condition_field');
				if (in_array($er->getObsValue($conditionField),$conditionsList)) {
					$row['class'] = $this->getSetting('condition_class');
				}
			}
			
			$rows[]=$row;
		}
		return array($header,$rows);
	}
	
	function getContainingForm($page) {
		return $page->getComponent('Form');
	}
	
	
	function _getCellData($id,$field,$values,$page) {
		$add = ADD::sys();
		$form = $this->getContainingForm($page);
		$field->setValue(null);
		$field->populateValue($values,$page);
       
		$childFields = $form->getChildFields($field->getName());
		$data = null;
		if (count($childFields)>0) {
                    
			$subResults = array();
			$joinChar = $field->getSetting('join_char') ? $field->getSetting('join_char') : " ";
			
			foreach($childFields as $childField) {
				$childField->setValue(null);
				$childField->populateValue($values,$page);
				$subResults[]=$childField->getDisplayText($page,$id);
			}
			$data = implode($joinChar,$subResults);
		}
		else {
                
			$data = $field->getDisplayText($page,$id);
			if ($field->getSetting('onclick')) {
				$onclick = sprintf($field->getSetting('onclick'),$data);
				$data = "<a href=\"javascript:;\" onclick=\"$onclick\">".$data."</a>";
			}
		}
				
		if ($field->getSetting('modal_action')) {
			$settings = $field->getSettings();
            $ff = new ADD_FieldFactory_RowData();
			if(strlen(trim($data))==0) $data="untitled";
			$action = $ff->newInstance(array(
				'Title'=>$data,
				'Field'=>$field->getName(),
				'Type'=>'modal_action',
				'Settings'=>$settings,
			));	
			
			$data = $action->getDisplayText($page,$id);
		}
		
		return $data;
	}
	
	function createMarkup($page) {
		list($headings,$rows) = $this->generateTable($page);
		
		$this->addTableMarkup($headings,$rows,$page);
		
	}		

	
		
}
?>