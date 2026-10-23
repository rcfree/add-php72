<?php
namespace Codebreaker\Conditions\DictTarget;
use ClinicLE\Install\Dictionary\DictTarget\SeparateTableDictTarget;
use ClinicLE\Common\Util;
use CLE;
class ComorbGroupDictTarget extends SeparateTableDictTarget {
	public function __construct($config) {
		$config->setSetting("table","icd10_group");
		parent::__construct($config);
	}
	public function createNewItem($dict,$dictItem, $dg) {
		$item = parent::createNewItem($dict,$dictItem,$dg);
		$this->replaceGroupCodes($item['id'],Util::val('code_ids',$dictItem,array()));
	}
	public function updateCurrentItem($currentItem,$itemData, $latestGroup) {
		$item = parent::updateCurrentItem($currentItem,$itemData,$latestGroup);	
		$this->replaceGroupCodes($item['id'],Util::val('code_ids',$itemData,array()));
	}
	function getAreaWithValue($areaValue) {
		return CLE::db('BASE')->fetchFirst("select * from icd10_area where value=?",array($areaValue));
	}
	function insertArea($areaValue,$areaTitle) {
		return CLE::db('BASE')->insert("icd10_area",array("value"=>$areaValue,"title"=>$areaTitle));
	}
	function replaceGroupCodes($groupId,$codeIds) {
		$db = CLE::db('BASE');
		$db->deleteByField("icd10_group_code","icd10_group_id",$groupId);
		foreach($codeIds as $codeId) {
			$db->insert("icd10_group_code",array("icd10_group_id"=>$groupId,"icd10_code_id"=>$codeId));
		}
	}
	function initDictionary($dictName) {
		$dict = parent::initDictionary($dictName);
		$this->createSeparateTable("icd10_group_code", array(
			"`icd10_group_id` INT(11)",
			"`icd10_code_id` INT(11)"
		),"CREATE TABLE IF NOT EXISTS `icd10_group_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `icd10_group_id` INT(11),
  `icd10_code_id` INT(11),
  PRIMARY KEY (`id`),
  CONSTRAINT `group_gc` FOREIGN KEY (`icd10_group_id`) REFERENCES `icd10_group` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `code_gc` FOREIGN KEY (`icd10_code_id`) REFERENCES `icd10_code` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8");
	}
}
	