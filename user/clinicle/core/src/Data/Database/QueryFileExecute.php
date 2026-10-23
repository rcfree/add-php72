<?php
namespace ClinicLE\Data\Database;
use ClinicLE\Common\Connection\AbstractDatabaseService;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 09/06/17
 * Time: 02:35
 */
class QueryFileExecute extends AbstractDatabaseService
{
    function executeFile($fileName, $params = array()) {
        \CLE::info("execute file:$fileName");
        $conn = $this->getConnection();
        \CLE::info("conn",$conn);
        $sqlFile = vsprintf(file_get_contents($fileName),$params);

        $sqlStatements = explode(";",$sqlFile);
        \CLE::info("sqlStatements:",$sqlStatements);
        foreach($sqlStatements as $sql) {
            $sql=trim($sql);
            if (strlen($sql)==0) continue;
            \CLE::info("SQL:$sql");
            $conn->execute($sql);
        }
    }
}