<?php
/**
 * Description of Load
 *
 * @author rcf8
 */


namespace ClinicLE\Manage\Command;

use ClinicLE\Command\AbstractCommand;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Common\Config\AbstractConfig;
use ClinicLE\Manage\Event\ManageEvents;
use CLE;
use ClinicLE\Common\Util;

class DataViewCommand extends AbstractCommand
{
	function getOptsList() {
        return "p::d";
    }
    function getDescription() {
        return "Data view";
    }
    
    function runCommand(AbstractConfig $options) {
		$cle = CLE::sys();
        $cle->initialise();
		$this->patientId = $options->get("p");
		$record = $options->get("r");
		$this->type = $options->get("t");
		$this->limit = $options->get("l");
		$this->sectionType = $options->get("s");
		$this->id = $options->get("i");
		$this->sectionId = $options->get("w");
		$events = CLE::sys()->getEventDispatcher();
		
		foreach(array("Patient","Domains","Encounters","Sections","Dictionaries","DictionaryItems","Fields","Forms",
                    "SectionTypes") as
        $outputType) {
			$events->addListener(ManageEvents::DATA_VIEW,array($this,"output$outputType"));
		}
		$event = new \ClinicLE\Manage\Event\DataViewCommandEvent($options);
		$events->dispatch(ManageEvents::DATA_VIEW,$event);
		$this->outputRecords($event);
		
    }
	function outputPatient($event) {
		if($event->getOptions()->get("r")!="patients") {
			return;
		}
		$mainConnection = CLE::svc(CoreServices::MAIN_CONNECTION);
		
		$sql = "select p.* from patient p left join address a on p.address_id = a.id left join gp g on p.gp_id = g.id";
		$params=array();
		if($event->getOptions()->get("p")) {
			$sql.=" where p.id = ?";
			$params[]=$event->getOptions()->get("p");
		}
if($event->getOptions()->get("i")) {
 
                         $event->setTitle("patient ".$event->getOptions()->get("i"));
                         print("where identifier = ".$event->getOptions()->get("i"));
                         $sql.=" where p.identifier=?";
                         $params[]=$event->getOptions()->get("i");
                 }

		$rows = $mainConnection->fetchAll($sql,$params);
		$event->setTitle("patient ".$event->getOptions()->get("p"));
		$event->setSubTitle("patient");
		$event->setData($rows);	
		$event->setFields(array(
			"id"=>5,
"identifier"=>8,
"title"=>6,
"first_name"=>20,
"middle_name"=>20,
"last_name"=>20,
"date_of_birth"=>10,
"hospital_no"=>10,
"nhs_no"=>12,
"born_in_uk"=>5,
"country_of_birth"=>7,
"gender"=>6,
"marital_status"=>5,
"ethnic_group"=>5,
"deceased"=>3,
"date_of_death"=>10,
"is_deleted"=>5,
"special_status"=>5));
	}
    function outputFields($event) {
        if($event->getOptions()->get("r")!="fields") {
            return;
        }
        $mainConnection = CLE::svc(CoreServices::MAIN_CONNECTION);

        $sql = "select of.id, of.name,of.level,of.weight,of.type,of.title,of.options,of.settings,of.is_deleted
         from 
observation_field
 of left join encounter_form ef on of.encounter_form_id = ef.id";
        $params=array();
        if(!$event->getOptions()->get("d")) {
            $where[]="of.is_deleted != '1'";
        }
        if($event->getOptions()->get("t")) {
            $where[]="ef.type like ?";
            $params[]=$event->getOptions()->get("t");
        }
        $rows = $mainConnection->fetchAll($sql.($where ? " where ".implode(" and ",$where) : ""). " order by of
        .weight",$params);
        $event->setTitle("form ".$event->getOptions()->get("t"));
        $event->setSubTitle("field");
        $event->setData($rows);
        $event->setFields(array("id"=>5,"level"=>3, "name"=>20,"weight"=>6,"type"=>20,"title"=>30,
            "options"=>20,"settings"=>20,"is_deleted"=>3));
    }
    function outputForms($event) {
        if($event->getOptions()->get("r")!="forms") {
            return;
        }
        $mainConnection = CLE::svc(CoreServices::MAIN_CONNECTION);

        $sql = "select * from encounter_form ef";
        $params=array();
        $where = array();
        if($event->getOptions()->get("t")) {
            $where[]="ef.type like ?";
            $params[]=$event->getOptions()->get("t");
        }
        $rows = $mainConnection->fetchAll($sql.($where ? " where ".implode(" and ",$where) : ""),$params);
        $event->setTitle("forms ".$event->getOptions()->get("t"));
        $event->setSubTitle("form");
        $event->setData($rows);
        $event->setFields(array("id"=>5,"type"=>30));
    }
	function outputDomains($event) {
		if($event->getOptions()->get("r")!="domains") {
			return;
		}
		$mainConnection = CLE::svc(CoreServices::MAIN_CONNECTION);
		
		$sql = "select d.*, dt.name from domain d left join domain_type dt on d.domain_type_id = dt.id";
		if($event->getOptions()->get("t")) {
			$where[]=" dt.name=?";
			$params[]=$event->getOptions()->get("t");
		}
		if($event->getOptions()->get("p")) {
			$where[]=" d.patient_id = ?";
			$params[]=$event->getOptions()->get("p");
		}
		if($event->getOptions()->get("i")) {
			$where[]=" d.id = ?";
			$params[]=$event->getOptions()->get("i");
		}
		$rows = $mainConnection->fetchAll($sql.($where ? " where ".implode(" and ",$where) : ""),$params);
		$event->setTitle("domains of type ".$event->getOptions()->get("t") . " (for patient ". $event->getOptions()->get("p") .")");
		$event->setSubTitle("domain");
		$event->setData($rows);	
	}
	function outputDictionaries($event) {
		if($event->getOptions()->get("r")!="dicts") {
			return;
		}
		$mainConnection = CLE::svc(CoreServices::MAIN_CONNECTION);
		
		$sql = "select d.* from dictionary d";
		$where = array();
		$params = array();
		if($event->getOptions()->get("t")) {
			$where[]=" d.name like ?";
			$params[]=$event->getOptions()->get("t");
		}
		
		$rows = $mainConnection->fetchAll($sql.($where ? " where ".implode(" and ",$where) : ""),$params);
		$event->setTitle("dictionaries of type ".$event->getOptions()->get("t"));
		$event->setSubTitle("dictionary");
		$event->setData($rows);	
		$event->setFields(array("id"=>4,"name"=>20,"title"=>30,"is_deleted"=>11));
	}
	function outputDictionaryItems($event) {
		if($event->getOptions()->get("r")!="dictitems") {
			return;
		}
		$mainConnection = CLE::svc(CoreServices::MAIN_CONNECTION);
		
		$sql = "select di.*, d.name as `dictionary` from dictionary_item di left join dictionary d on di.dictionary_id = d.id";
		$where = array();
		$params = array();
		if($event->getOptions()->get("t")) {
			$where[]=" d.name like ?";
			$params[]=$event->getOptions()->get("t");
		}
		if($event->getOptions()->get("w")) {
			$where[]=" di.title like ?";
			$params[]=$event->getOptions()->get("w");
		}
		$rows = $mainConnection->fetchAll($sql.($where ? " where ".implode(" and ",$where) : ""),$params);
		$event->setTitle("dictionary items of type ".$event->getOptions()->get("t"));
		$event->setSubTitle("dictionary item");
		$event->setData($rows);
		$event->setFields(array("value"=>10,"title"=>30,"weight"=>6,"is_disabled"=>11,"is_deleted"=>11));
	}
	function outputSections($event) {
		if($event->getOptions()->get("r")!="sections") {
			return;
		}
		$mainConnection = CLE::svc(CoreServices::MAIN_CONNECTION);
		
		$sql = "select s.*, st.name from section s left join section_type st on s.section_type_id = st.id left join patient p on s
.patient_id = p.id";
		if($event->getOptions()->get("t")) {
			$where[]=" st.name=?";
			$params[]=$event->getOptions()->get("t");
		}
		if($event->getOptions()->get("p")) {
			$where[]=" p.identifier = ?";
			$params[]=$event->getOptions()->get("p");
		}
		if($event->getOptions()->get("i")) {
			$where[]=" s.id = ?";
			$params[]=$event->getOptions()->get("i");
		}
		$rows = $mainConnection->fetchAll($sql.($where ? " where ".implode(" and ",$where) : ""),$params);
		$event->setTitle("sections of type ".$event->getOptions()->get("t") . " (for patient ". $event->getOptions()->get("p") .")");
		$event->setSubTitle("section");
		$event->setData($rows);	
	}
    function outputSectionTypes($event) {
        if($event->getOptions()->get("r")!="sectiontypes") {
            return;
        }
        $mainConnection = CLE::svc(CoreServices::MAIN_CONNECTION);

        $sql = "select st.* from section_type st";
        if($event->getOptions()->get("t")) {
            $where[] = "st.name like ?";
            $params[] = $event->getOptions()->get("t");
        }
        $rows = $mainConnection->fetchAll($sql.($where ? " where ".implode(" and ",$where) : ""),$params);
        $event->setTitle("sections types ".$event->getOptions()->get("t"));
        $event->setSubTitle("section type");
        $event->setData($rows);
    }
	function outputEncounters($event) {
		if($event->getOptions()->get("r")!="encs") {
			return;
		}
		$mainConnection = CLE::svc(CoreServices::MAIN_CONNECTION);
		
		$sql = "select p.identifier as snumber, e.*, ef.type, st.name as section_type from encounter e left join 
		encounter_form
		 ef on e
		.encounter_form_id = ef
.id left join section s on e.parent_encounter_id = s.id left join section_type st on st.id = s.section_type_id left 
join patient p on p.id = e.patient_id";
		if($event->getOptions()->get("t")) {
			$where[]=" ef.type like ?";
			$params[]=$event->getOptions()->get("t");
		}
		if($event->getOptions()->get("p")) {
			$where[]=" e.patient_id = ?";
			$params[]=$event->getOptions()->get("p");
		}
		if($event->getOptions()->get("s")) {
			$where[]=" st.name like ?";
			$params[]=$event->getOptions()->get("s");
		}
		if($event->getOptions()->get("i")) {
			$where[]=" e.id = ?";
			$params[]=$event->getOptions()->get("i");
		}
		if($event->getOptions()->get("w")) {
			$where[]=" s.id = ?";
			$params[]=$event->getOptions()->get("w");
		}
		$sql=$sql.($where ? " where ".implode(" and ",$where) : "");
		if($event->getOptions()->get("l")) {
			$sql.=" limit ".$event->getOptions()->get("l");
		}
		$rows = $mainConnection->fetchAll($sql,$params);
				
		$rcount=0;
		$obsFields = array();
		foreach($rows as $row) {
			$encounterRecord = new \ClinicLE\Data\Record\EncounterRecord();
			$encounterRecord->setType($event->getOptions()->get("t"));
			$encounterRecord->setId($row['id']);
			$encounterRecord->retrieve();
            $obsValues = $encounterRecord->getObsValues();
            foreach($obsValues as $name => $obsValue) {
                if(is_array($obsValue)) {
                    foreach($obsValue as $multiName => $multiValue) {
                        $rows[$rcount][$name."_".$multiName] = 1;
                        if(!isset($obsFields[$name."_".$multiName])){
                            $obsFields[$name . "_" . $multiName] = 1;
                        }
                    }
                }
                else {
                    $rows[$rcount][$name] = $obsValue;
                    if(!isset($obsFields[$name]) || $obsFields[$name]) {
                        $obsFields[$name] =  1;
                    }
                }
            }

			$rcount++;
		}
		$event->setTitle("encounters of type ".$event->getOptions()->get("t") . " (for patient ". $event->getOptions()->get("s") .") in section ".$event->getOptions()->get("s"). "(section id:".$event->getOptions()->get("w").")");
		$event->setSubTitle("encounter");
		$event->setData($rows);
		$fields = array("snumber"=>8, "id"=>5, "patient_id"=>5,"parent_encounter_id"=>5,"is_deleted"=>6);
		$fields = $fields + $obsFields;
		$event->setFields($fields);
	}
	
	function outputRecords($event) {
		if(!$event->getOptions()->get("r")) {
			CLE::log($this)->info("No record type provided");
			return;
		}
		$format = $event->getOptions()->get("f");
		echo "\n";
		if($format == "list") {
			$this->outputList($event);
		}
		elseif($format == "csv") {
			$this->outputCsv($event);
		}
		else {
			$this->outputOutline($event);
		}
		echo "\n";
	}
	function outputOutline($event) {
		echo "- ".count($event->getData())." ".$event->getTitle().":\n";
		foreach($event->getData() as $row) {
			if($event->getSubTitle()) {
				echo " - ".$event->getSubTitle()." ".Util::val($event->getIdFieldName(),$row).":\n";
			}
			foreach($row as $fieldName => $value) {
				$this->printLine(2,$fieldName,$value);
			}
			echo "\n";
		}
	}
	function outputList($event) {
		$heads = array();
		echo count($event->getData())." ".$event->getTitle().":\n\n";
		
		foreach($event->getFields() as $field => $len) {
			$heads[]=sprintf("%-$len.$len"."s",$field);
		}
		echo implode("|",$heads)."\n";
		foreach($event->getData() as $row) {
			$line = array();
			foreach($event->getFields() as $field=>$len) {
				$line[]=sprintf("%-$len.$len"."s",Util::val($field,$row));
			}
			echo implode("|",$line)."\n";
		}
	}
	function outputCsv($event) {
		$outputFile = $event->getOptions()->get("o");
		if(!$outputFile) {
			CLE::log($this)->info("No output file provided - printing to STDOUT");
            $fh = fopen("php://stdout","w");
		}
		else {
            $fh = fopen($outputFile, "w");
        }
		$heads = array();
		foreach($event->getFields() as $field => $len) {
			$heads[]=$field;
		}
		fputcsv($fh,$heads);
		foreach($event->getData() as $row) {
			$line = array();
			foreach($event->getFields() as $field=>$len) {
				$line[]=Util::val($field,$row);
			}
			fputcsv($fh,$line);
		}
		fclose($fh);
	}
	function printLine($level, $name,$value) {
		if(is_array($value)) {
			echo str_repeat(" ",$level)."- ".$name .":\n";
			foreach($value as $subName => $subValue) {
				$this->printLine($level+1,$subName,$subValue);
			}
			return;
		}
		echo str_repeat(" ",$level)."- ".$name ." = \"" . $value ."\"\n";
	}
	
}
