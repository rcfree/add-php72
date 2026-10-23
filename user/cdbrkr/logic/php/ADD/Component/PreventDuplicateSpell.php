<?php
class ADD_Component_PreventDuplicateSpell extends ADD_Component implements ADD_Component_AjaxInterface {
    function ajaxFunction($values, ADD_Page $callPage) {
		$currentParentId = $callPage->getComponent('Form')->getUncleanValue('__parent');
		
		$rows = self::getMatchingSpells($values['pid'],$values['eid'],$values['date_of_admission'],$values['ward']);
	$count = count($rows);
	if($count==0) return;
	
	$warning = self::getDuplicateWarning($rows);
	
	ADD::web()->addWarning($warning);
		
   }
	static function getDuplicateWarning($rows, $warning=null) {
		$count = count($rows);
		if(!$warning) {
			$warning = "There ".ADD_Util::pluralize($count,"is","are")." already <b>$count</b> ".ADD_Util::pluralize($count,"wardspell")." with this patient, ward and date. Click below to select:<ul class='nav nav-list'>";
		}
		foreach($rows as $row) {
			$warning.="<li><a  class='btn' href='/cdbrkr/wspell/sdetail?pid=".$row['patient_id']."&eid=".$row['parent_encounter_id']."'>".$row['date_of_admission'] . " - ".$row['ward']." (".$row['consultant'].")</a></li>";
		}
		$warning."</ul>";
		return $warning;
	}
	static function getMatchingSpells($patientId,$sectionId,$dateOfAdmission,$ward) {
		$dict = ADD::db('dictionary')->getDictionaryByName("cdbrkr_consultants");
		$sql = "select sdetail.parent_encounter_id, sdetail.patient_id, (select o.value from observation o 
				left join observation_field of on o.observation_field_id=of.id
				where o.encounter_id=sdetail.id and of.name='date_of_admission'
			) as 'date_of_admission',
			(select o.value from observation o 
				left join observation_field of on o.observation_field_id=of.id
				where o.encounter_id=sdetail.id and of.name='ward'
			) as 'ward',
			(select di.title from observation o 
				left join dictionary_item di on di.value = o.value and di.dictionary_id=?
				left join observation_field of on o.observation_field_id=of.id
				where o.encounter_id=sdetail.id and of.name='consultant'
			) as 'consultant'
from encounter sdetail where 
(select o.value from observation o 
				left join observation_field of on o.observation_field_id=of.id
				where o.encounter_id=sdetail.id and of.name='date_of_admission'
			)=? and (select o.value from observation o 
				left join observation_field of on o.observation_field_id=of.id
				where o.encounter_id=sdetail.id and of.name='ward'
			)=? and sdetail.is_deleted!=1 and sdetail.patient_id=?";
	$params = array($dict['id'],$dateOfAdmission,$ward,$patientId);		
	if($sectionId) {
		$sql.=" and sdetail.parent_encounter_id!=?";
		$params[]=$sectionId;
	}
	
 	$rows = ADD::db('encounter')->fetchAll($sql,$params);
	return $rows;		
}
    function ajaxInputs(ADD_Page $callPage) {
    	return array("date_of_admission","ward");
    }
}