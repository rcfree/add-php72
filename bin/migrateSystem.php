<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$home = getenv('ADD_PATH');
include_once($home."/vendor/autoload.php");
 
use ClinicLE\System\Environment\StandardEnvironment;
use ClinicLE\System\Initialiser;


$env = new StandardEnvironment($home,getenv('ADD_PROFILE'));
$init = new Initialiser($env);

$system = ADD::init($init);
$system->initServiceContainer();
$system->initModules();

migrateRoles($system);
createContainerTables($system);
createTypeData($system);
copyEncounterData($system);
updateNoteData($system);

function migrateRoles($system) {
	$udbConfig = $system->getConfig()->getSection("UserDatabase");
	
	$pdo = new PDO($udbConfig->getSetting('dsn'),$udbConfig->getSetting('user'),$udbConfig->getSetting('pass'));
	$pdo->query("CREATE TABLE `users_roles` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `user_id` int(11) unsigned NOT NULL,
	  `role_id` int(11) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `user_id` (`user_id`),
	  KEY `role_id` (`role_id`),
	  CONSTRAINT `users_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
	  CONSTRAINT `users_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=latin1;
	
	CREATE TABLE `roles_groups` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `role_id` int(11) NOT NULL,
	  `group_id` mediumint(11) unsigned NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `role_id` (`role_id`),
	  KEY `group_id` (`group_id`),
	  CONSTRAINT `roles_groups_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
	  CONSTRAINT `roles_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=214 DEFAULT CHARSET=latin1;");
	
	$grpStmt = $pdo->prepare("select id,name from groups");
	$grpStmt->execute();
	$tempGrps = $grpStmt->fetchAll(PDO::FETCH_ASSOC);
	$groups=array();
	foreach($tempGrps as $tempGrp) {
		$groups[$tempGrp['name']]=$tempGrp;
	}	
	$roleStmt = $pdo->prepare("select name,id,groups from roles");
	$roleStmt->execute();
	$tempRoles = $roleStmt->fetchAll(PDO::FETCH_ASSOC);
	$roles=array();
	foreach($tempRoles as $tempRole) {
		$roles[$tempRole['name']]=$tempRole;
	}	
	$stmt = $pdo->prepare("select id,roles from users");
	$stmt->execute();
	$users = $stmt->fetchAll();
	
	$userRoleStmt = $pdo->prepare("insert into users_roles (user_id,role_id) values(?,?)");
	$pdo->exec("delete from users_roles");
		
	foreach($users as $user) {
		$userId = $user['id'];
		if($user['roles']=="") continue;
		
		$userRoles = explode(",",$user['roles']);
		foreach($userRoles as $userRole) {
			$userRoleStmt->execute(array($userId,$roles[$userRole]['id']));
		}
	}
	$roleGroupStmt = $pdo->prepare("insert into roles_groups (role_id,group_id) values(?,?)");
	$pdo->exec("delete from roles_groups");
	
	foreach($roles as $roleName => $role) {
		$roleId = $role['id'];
		if($role['groups']=="") continue;
		
		$roleGroups = explode(",",$role['groups']);
		foreach($roleGroups as $roleGroup) {
			$roleGroupStmt->execute(array($roleId,$groups[$roleGroup]['id']));
		}
	}
}
function createContainerTables($system) {
	$sql = "-- Create syntax for TABLE 'domain'
CREATE TABLE IF NOT EXISTS `domain` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `domain_type_id` int(11) DEFAULT NULL,
  `is_deleted` int(11) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `domain_info` (`patient_id`,`domain_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'domain_type'
CREATE TABLE IF NOT EXISTS `domain_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
-- Create syntax for TABLE 'section'
CREATE TABLE IF NOT EXISTS `section` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `section_type_id` int(11) DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `label` varchar(100) DEFAULT NULL,
  `number` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'section_type'
CREATE TABLE IF NOT EXISTS `section_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
alter table `encounter` 
  drop foreign key `encounter_FK_2`;
  CREATE TABLE `recent_patient` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`,`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
insert into recent_patient (patient_id,username,timestamp) 
select distinct record_id, user_id,timestamp 
from audit 
where record_id is not null and (record_type='patient' and action='create' or action='edit') or record_type='home';";

	ADD::db('BASE')->getConn()->query($sql);
}
function createTypeData($system) {
	$db = ADD::db('BASE');
	$log = ADD::log("migrateSystem.php");
	foreach($system->getModules() as $module) {
		foreach($module->getPages() as $page) {
			if(!$page->hasComponent('Encounter')) continue;
			$enc = $page->getComponent('Encounter');
			$encType = $page->getSetting('encounter');
			if(is_a($enc,"ADD_Component_Encounter_Domain")) {
				$dtcount = $db->fetchSingleValue("select count(*) from domain_type where name=?",array($encType));	
				if($dtcount==0) {
					$db->insert("domain_type",array("name"=>$encType));
					$log->info("insert domain type $encType");
				}
				continue;
			}
			if(is_a($enc,"ADD_Component_Encounter_Section") || is_a($enc,"ADD_Component_Encounter_RepeatSection")) {
				$dtcount = $db->fetchSingleValue("select count(*) from section_type where name=?",array($encType));	
				if($dtcount==0) {
					$db->insert("section_type",array("name"=>$encType));
					$log->info("insert section type $encType");
				}
			}
			continue;
		}
	}
	
}
function copyEncounterData($system) {
	$db = ADD::db('BASE');
	$log = ADD::log("migrateSystem.php");
	$db->disableKeys("section");
	$db->disableKeys("domain");
	$db->disableKeys("domain_type");
	$db->disableKeys("section_type");
	foreach($system->getModules() as $module) {
		foreach($module->getPages() as $page) {
			if(!$page->hasComponent('Encounter')) continue;
			$enc = $page->getComponent('Encounter');
			$encType = $page->getSetting('encounter');
			
			if(is_a($enc,"ADD_Component_Encounter_Domain")) {
				$dtId = $db->fetchSingleValue("select id from domain_type where name=?",array($encType));	
				$db->execute("delete from domain where domain_type_id=?",array($dtId));
				$dCount = $db->fetchSingleValue("select count(*) from
				encounter e left join encounter_form ef on e.encounter_form_id = ef.id
				where ef.type=?",array($encType));
				$log->info("copy domain $encType ($dCount)");
				$db->execute("insert into `domain` (id,patient_id,domain_type_id,is_deleted,date_created,date_updated)
				select e.id, e.patient_id, '$dtId', e.is_deleted, e.date_created, e.date_updated from
				encounter e left join encounter_form ef on e.encounter_form_id = ef.id
				where ef.type=?",$encType);
				$db->execute("delete from encounter where id in (select d.id from domain d left join domain_type dt on d.domain_type_id = dt.id where dt.name=?)",$encType);
				$db->execute("delete from `encounter_form` where type=?",$encType);
			}
			if(is_a($enc,"ADD_Component_Encounter_Section") || is_a($enc,"ADD_Component_Encounter_RepeatSection")) {
				$stId = $db->fetchSingleValue("select id from section_type where name=?",array($encType));	
				$db->execute("delete from section where section_type_id=?",array($stId));
				$sCount = $db->fetchSingleValue("select count(*) from
				encounter e left join encounter_form ef on e.encounter_form_id = ef.id
				where ef.type=?",array($encType));
				$log->info("copy section $encType ($sCount)");
				$db->execute("insert into `section` (id,patient_id,section_type_id,is_deleted,date_created,date_updated)
				select e.id, e.patient_id, '$stId', e.is_deleted, e.date_created, e.date_updated from
				encounter e left join encounter_form ef on e.encounter_form_id = ef.id
				where ef.type=?",$encType);
				$db->execute("delete from encounter where id in (select s.id from section s left join section_type st on s.section_type_id = st.id where st.name=?)",$encType);
				$db->execute("delete from `encounter_form` where type=?",$encType);
			}
		}
	}
	$db->enableKeys("section");
	$db->enableKeys("domain");
	$db->enableKeys("domain_type");
	$db->enableKeys("section_type");

}
function updateNoteData($system) {
	$db = ADD::db('BASE');
	$conn = $db->getConn();
	$log = ADD::log("migrateSystem.php");
	$log->info("update notes with observation ids");
	$conn->query("update observation set observation_note_id=549 where id=399049");
	$conn->query("update `observation_note` note
set observation_id = (
	select `id` from `observation` obs
	where note.id = obs.observation_note_id 
)
where (
	select obs.observation_note_id from `observation` obs
	where note.id = obs.observation_note_id 
) is not null");

$log->info("update observations with latest note id");
$conn->query("update `observation` obs 
set observation_note_id=(
	select `id` from `observation_note` note 
	where obs.id = note.observation_id 
	order by obs.date_updated desc limit 0,1)
");

$log->info("create note field in observation");
$conn->query("alter table `observation`
add column note text default null;");

$log->info("update observation note field using note ids set previously");
$conn->query("update `observation` obs 
set note=(
	select `text` from `observation_note` note 
	where obs.observation_note_id = note.id
);");
$conn->query("update observation o set o.value='' where length(o.value)>0 and o.note is not null and length(o.note)>length(o.value);");
$conn->query("update observation o set o.note='' where length(o.value)>0 and o.note is not null and length(o.note)<length(o.value);");
$conn->query("alter table observation 
drop foreign key observation_FK_4,
drop column observation_note_id;");

$conn->query("alter table observation_note
drop foreign key observation_note_FK_1;
drop table observation_note;");
}