<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 08:49
 */

namespace ClinicLE\Search\Resource;


use ClinicLE\Common\Connection\AbstractDatabaseService;
use ClinicLE\Common\Util;
use ClinicLE\Search\Group\EncounterPageGroup;
use ClinicLE\Search\Util\CreateTableSelectStatement;
use ClinicLE\Security\AccessManager;
use ClinicLE\Common\Connection\AbstractConnection;
use ClinicLE\System\Service\CoreServices;

class SearchRepository extends AbstractDatabaseService
{
    private $accessManager;
    private $sourceDbName;
    function __construct(AbstractConnection $conn, AccessManager $accessManager, $sourceDbName)
    {
        $this->accessManager = $accessManager;
        $this->sourceDbName = $sourceDbName;
        parent::__construct($conn);
    }

    function storeSearchDefinition($id,$title, $searchData) {
        $this->getConnection()->beginTxn();
        if(Util::isEmpty($id)) {
            $sd = $this->insertSearchDefinition($title, $searchData);
        }
        else {
            $sd = $this->updateSearchDefinition($id, $title, $searchData);
        }
        $this->getConnection()->commitTxn();
        return $sd;
    }
    function getSearchDefinitionById($id) {
        $search = $this->getConnection()->selectById("search_definition",$id);
        if(!$search) {
            return;
        }
        if(isset($search['data'])) {
            $search['data']=Util::unserialize($search['data']);
        }
        return $search;
    }
    function deleteSearchDefinitionById($id) {
        $this->getConnection()->deleteByField("search_definition","id",$id);
    }
    function insertSearchDefinition($title, $searchData) {
        $searchRecord = $this->getConnection()->insert("search_definition",array("title"=>$title,"data"=>Util::serialize($searchData),"owner"=>$this->getUserName(),"date_created"=>\CLE::svc(CoreServices::LOCALE)->nowAsISO()));
        return $searchRecord;
    }
    function updateSearchDefinition($id, $title, $searchData) {
        $searchRecord = $this->getConnection()->update("search_definition",array("id"=>$id), array("title"=>$title,"data"=>Util::serialize($searchData),"owner"=>$this->getUserName(),"date_updated"=>\CLE::svc(CoreServices::LOCALE)->nowAsISO()));
        return $searchRecord;
    }
    function getSearchDefinitionsByUser($owner) {
        return $this->getConnection()->fetchAll("select id,title,owner,date_created, date_updated from search_definition where owner=?",
            array($owner->username));
    }
    function getUserName() {
        return $this->accessManager->getUser()->username;
    }
    function dropTable($tableName) {
        $this->getConnection()->execute("drop table if exists ".$tableName);
    }
    function addEncounterIndexes($tableName) {
        $this->getConnection()->execute("alter table ".$tableName." add index patient_id (patient_id)");
        $this->getConnection()->execute("alter table ".$tableName." add index section_id (section_id)");
    }
    function createObservationTable(EncounterPageGroup $group) {
        \CLE::info("Creating obs table ".$group->getTableExpression(). " for page:".$group->getPagePim());

        $createSelect = new CreateTableSelectStatement();
        $createSelect->setTableName($group->getTableExpression());
        $createSelect->addToSelect("`".$group->getName()."`.*, `".$group->getName()."`.parent_encounter_id as section_id");
        $createSelect->addToSelect("st.name","{$this->sourceDbName}.section_type");
        //$createSelect->addToSelect("s.episode_id","{$this->sourceDbName}.episode_id");
        $createSelect->setFrom("{$this->sourceDbName}.encounter as `".$group->getName()."`");
        foreach($group->getSearchTypes() as $SearchType) {

            $createSelect->addToSelect($SearchType->createObsFieldSubQuery($group->getName(),$this->sourceDbName). "as `"
                .$SearchType->getFieldName()."`");
        }
        $createSelect->addJoin("left join {$this->sourceDbName}.encounter_form as ef on `".$group->getName()."`.encounter_form_id=ef.id");
        $createSelect->addJoin("left join {$this->sourceDbName}.section as s on `".$group->getName()."`.parent_encounter_id=s.id");
        $createSelect->addJoin("left join {$this->sourceDbName}.section_type as st on s.section_type_id = st.id");
        $createSelect->addWhere("ef.type=?");
        $sql = $createSelect->generateSql();

        $this->getConnection()->execute($sql,array($group->getEncounterType()));
        $this->updateSearchTable($group->getTableExpression());
    }
    function updateSearchTable($tableName) {
        $now = \CLE::svc(CoreServices::LOCALE)->nowAsISO();
        \CLE::info("Update search table ".$tableName." with last_updated:".$now);
        $searchTableId = $this->getConnection()->fetchSingleValue("select id from search_table where table_name=?",
            array($tableName));
        if($searchTableId) {
            $this->getConnection()->update("search_table", array("id"=>$searchTableId, "last_updated" => $now));
        }
        else {
            $this->getConnection()->insert("search_table",array("table_name"=>$tableName,"last_updated"=>$now));
        }
    }
    function shouldUpdateSearchTable($tableName, $now, $sinceTime = 23)
    {
        $conn = $this->getConnection();
        $stable = $conn->selectFirstByField("search_table","table_name",$tableName);
        if(!$stable) {
            \CLE::info("Table $tableName is not in the search_table");
            return true;
        }
        if(!$conn->doesTableExist($tableName)) {
            \CLE::info("Table $tableName does not exist");
            return true;
        }
        $lastUpdated = $stable['last_updated'];
        $oneDay = ($sinceTime*60*60);

        if($lastUpdated + $oneDay < $now) {
            \CLE::info("Table older than 1 day (last updated:".$lastUpdated ." + oneDay:".$oneDay." < now:".$now);
            return true;
        }
        return false;
    }
    function createPatients() {
        $conn = $this->getConnection();
        $conn -> execute("drop table if exists `patient`");
        $conn->execute("create table `patient` select * from {$this->sourceDbName}.patient p");
        $conn->execute("create index `patient_id` on patient(id)");
        $this->updateSearchTable("patient");
    }
    function createAudit() {
        $conn = $this->getConnection();
        $conn -> execute("drop table if exists `audit`");
        $conn->execute("create table `audit` select * from {$this->sourceDbName}.audit");
        $this->updateSearchTable("patient");
    }
    function createConsentRecord($includeNonConsents=false) {
        $conn = $this->getConnection();
        \CLE::info("Dropping table consent_record");
        $conn -> execute("drop table if exists `consent_record`");
        \CLE::info("Dropping table all_consent_record");
        $conn -> execute("drop table if exists `all_consent_record`");

        $dataDbName = $this->sourceDbName;
        $sql = "
create table `all_consent_record`
SELECT 
distinct
p.id as patient_id,
(select str_to_date(o.value,'%d/%m/%Y') from $dataDbName.observation o 
    left join $dataDbName.observation_field of on o.observation_field_id=of.id
    where o.encounter_id=res_general_consent.id and of.name='consent_form_date'
) as 'consent_form_date',
(select o.value from $dataDbName.observation o 
    left join $dataDbName.observation_field of on o.observation_field_id=of.id
    where o.encounter_id=res_general_consent.id and of.name='add_to_recruit_db'
) as 'add_to_recruit_db',
(select o.value from $dataDbName.observation o 
    left join $dataDbName.observation_field of on o.observation_field_id=of.id
    where o.encounter_id=res_general_consent.id and of.name='add_to_research_db'
) as 'add_to_research_db',
(select o.value from $dataDbName.observation o 
    left join $dataDbName.observation_field of on o.observation_field_id=of.id
    where o.encounter_id=res_contact_detail.id and of.name='current_status'
) as 'current_status',
(select o.value from $dataDbName.observation o 
    left join $dataDbName.observation_field of on o.observation_field_id=of.id
    where o.encounter_id=res_contact_detail.id and of.name='ilh_identifier'
) as 'ilh_identifier',
(select o.value from $dataDbName.observation o 
    left join $dataDbName.observation_field of on o.observation_field_id=of.id
    where o.encounter_id=res_dbrequest.id and of.name='pat_sent_db_consent'
) as 'pat_sent_db_consent'
from {$dataDbName}.patient p
".($includeNonConsents ? "left" : "inner")." join {$dataDbName}.encounter as res_general_consent on p.id = res_general_consent.patient_id and res_general_consent.encounter_form_id=119 and res_general_consent.is_deleted != 1
inner join {$dataDbName}.encounter as res_contact_detail on p.id = res_contact_detail.patient_id and res_contact_detail.encounter_form_id=139 and res_contact_detail.is_deleted != 1
inner join {$dataDbName}.encounter as res_dbrequest on p.id = res_dbrequest.patient_id and res_dbrequest.encounter_form_id=131 and res_dbrequest.is_deleted != 1;
";
        \CLE::info("Building all consent record table");
        $conn -> execute($sql);
        $conn -> execute("alter table all_consent_record add index patient_id(patient_id)");
        $sql2 = "
create table `consent_record`
select distinct
patient_id, 
(select consent_form_date from all_consent_record cr2 where cr1.patient_id = cr2.patient_id order by consent_form_date desc limit 0,1) as consent_form_date,
(select add_to_recruit_db from all_consent_record cr3 where cr1.patient_id = cr3.patient_id order by consent_form_date desc limit 0,1) as add_to_recruit_db, 
(select add_to_research_db from all_consent_record cr4 where cr1.patient_id = cr4.patient_id order by consent_form_date desc limit 0,1) as add_to_research_db,
current_status, 
ilh_identifier, 
pat_sent_db_consent 
from all_consent_record cr1
group by patient_id, consent_form_date";
        \CLE::info("Build latest consent record table");
        $conn -> execute($sql2);
        $conn -> execute("alter table consent_record add index patient_id(patient_id)");
        $conn -> execute("drop table if exists all_consent_record");
        $this->updateSearchTable("consent_record");
    }
}