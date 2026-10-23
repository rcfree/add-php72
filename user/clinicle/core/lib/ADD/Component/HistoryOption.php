<?php

class ADD_Component_HistoryOption extends ADD_Component {
	function afterGenerate() {
		$add = ADD::sys();
                $page = $this->_page;
                $hdb = ADD::db('history');
            
                $form = $page->getComponent('Form');
		$html = $page->html();
		$er = $page->getEncounterRecord();
               
                $er->retrieve();
                
                if (!$er->exists()) return;
                $historyCounts = $hdb->getAuditCountsForFieldsInEncounter($er->getId());
                	
		foreach($form->getFields() as $name => $field) {
                    $name = $field->getName();
			$currLabel = $html->get($name."_label");
                        $fieldCount = ADD_Util::val($name,$historyCounts);
			if (!$fieldCount) continue;
                        $currLabel['text']=$currLabel['text']."<a href='javascript:;' onclick='showHistory(\"$name\",".$er->getId().")' class='btn btn-link btn-mini'><i class='icon-calendar'></i></a>";
			$html->set($name."_label",$currLabel);
		}
		
	}
        function getHistory($encounterRecord) {
            $historyRows = $db->fetchAll("select of.name as name, count(*) as revision_count from observation_audit oa "
                    . "left join observation o on oa.observation_id = o.id "
                    . "left join observation_field of on o.observation_field_id = of.id "
                    . " where o.encounter_id=? group by o.observation_field_id having revision_count>0",$encounterRecord->getId());
            $historyCounts = array();
            if ($historyRows) {
                foreach($historyRows as $hRow) {
                    $historyCounts[$hRow['name']]=$hRow['revision_count'];
                }
            }
            return $historyCounts;
        }
}
?>