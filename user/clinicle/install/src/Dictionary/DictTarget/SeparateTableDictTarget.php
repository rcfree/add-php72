<?php
namespace ClinicLE\Install\Dictionary\DictTarget;
use CLE;
use ClinicLE\Common\Util;
use ClinicLE\Data\Database\QueryFileExecute;
use ClinicLE\Migrate\DatabaseSchemaPopulator;
use ClinicLE\Migrate\Schema;
use ClinicLE\Migrate\SqlParser;
use ClinicLE\Migrate\TableCompare;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Install\Dictionary\Supervisor;

use PDO;

class SeparateTableDictTarget extends AbstractDictTarget {
    protected $includeDataField=false;
    function __construct($config, Supervisor $supervisor=null) {
        parent::__construct($config, $supervisor);
        $this->_tableName = $config->assertSetting('table');
        $this->_otherFields = $config->getSettingList('other_fields',';');
    }
    public function finish() {
        CLE::svc(CoreServices::MAIN_CONNECTION)->enableKeys($this->_tableName);
    }
    public function createNewItem($dict,$dictItem, $dg) {
        $db = CLE::svc(CoreServices::MAIN_CONNECTION);
        $adb = CLE::db('audit');

        $didata = array(
            'value'=>$dictItem['value'],
            'title'=>$dictItem['title'],
        );

        foreach($this->_otherFields as $otherField) {
            $didata[$otherField]=Util::val($otherField,$dictItem);
        }
        if (array_key_exists('weight',$dictItem)) $didata['weight']=$dictItem['weight'];
        $didata['is_disabled']=Util::val('disabled',$dictItem,0);
        $didata['is_deleted']=Util::val('deleted',$dictItem,0);
        $di = $db->insert($this->_tableName,$didata);
        $di['dictionary_id']=$this->_tableName;
        $adb->auditDictItem("create",$di);
        return $di;
    }

    public function updateCurrentItem($currentItem,$itemData, $latestGroup) {
        $ddb = CLE::sys()->db('dictionary');

        $log = CLE::log($this);

        list($changed,$newData) = $this->isItemSame($currentItem, $itemData, $latestGroup, $log);
        if(!$changed) {
            return;
        }

        $oldDi = $currentItem;
        $adb = CLE::sys()->db('audit');
        $currentItem = $ddb->update($this->_tableName,$currentItem,$newData);
        $currentItem['dictionary_id']=$this->_tableName;
        $adb->auditDictItem("update",$currentItem,$oldDi);

        return $currentItem;
    }


    public function findGroupUsingInput($currentItem,$dict) {
        return null;
    }
    private $getItem;
    function getItemWithValue($dict,$value) {
        if(!$this->getItem) {
            $this->getItem = CLE::db('BASE')->prepare("select * from ".$this->_tableName." where value=?");
        }
        $this->getItem->execute(array($value));
        return $this->getItem->fetch(PDO::FETCH_ASSOC);
    }
    function beforeImport() {
        CLE::info("create separate table:".$this->_tableName);
        $db = CLE::db('dictionary');
        $db->getConn();
        $this->createSeparateTable($this->_tableName,$this->_config->getSetting('alter_table'));
        $db->disableKeys($this->_tableName);
    }

    function createSeparateTable($tableName, $alterTable=null,$createSql=null) {
        $db = CLE::svc(CoreServices::MAIN_CONNECTION);
        CLE::info("table $tableName exists:".$db->doesTableExist($tableName));
        if(!$db->doesTableExist($tableName)) {

            if (!$createSql) {
                $execute = new QueryFileExecute($db);
                $execute->executeFile(CLE::sys()->getPath() . "/vendor/clinicle/core/sql/install/dictTable.sql", array
                ($tableName, $tableName));
            } else {
                $db->execute($createSql);
            }
        }
        if(!$alterTable) return;
        if(!is_array($alterTable)) {
            $alterTable=array($alterTable);
        }
        $dbSchemaPopulator = new DatabaseSchemaPopulator(CLE::svc(CoreServices::MAIN_CONNECTION));
        $schema = new Schema();
        $sqlParser = new SqlParser();
        $dbSchemaPopulator->populate($schema, $sqlParser);
        $tableCompare = new TableCompare();
        $sql = $tableCompare->createModifyStatement($schema->getTable($tableName), $alterTable);
        if(!$sql) {
            return;
        }
        $db->execute($sql);
    }
}
	