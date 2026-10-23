<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of History
 *
 * @author rcf8
 */
class ADD_Database_History extends ADD_Database {
    function getAuditCountsForFieldsInEncounter($encounterId) {
            $historyRows = $this->fetchAll("select of.name as name, count(*) as revision_count from observation_audit oa "
                    . "left join observation o on oa.observation_id = o.id "
                    . "left join observation_field of on o.observation_field_id = of.id "
                    . " where o.encounter_id=? group by o.observation_field_id having revision_count>1",$encounterId);
            $historyCounts = array();
            if ($historyRows) {
                foreach($historyRows as $hRow) {
                    $historyCounts[$hRow['name']]=$hRow['revision_count'];
                }
            }
            return $historyCounts;
    }
    
    function getHistoryForFieldInEncounter($fieldName,$encId) {
       return $this->fetchAll("select a.revision_no, oa.change_type, oa.value, a.timestamp, a.user_id
        from observation_audit oa
        left join observation o on oa.observation_id = o.id
        left join audit a on oa.audit_id = a.id
        left join encounter e on o.encounter_id = e.id
        left join observation_field of on o.observation_field_id = of.id
        where o.encounter_id=? and of.name=?",array($encId,$fieldName));
    }
}
