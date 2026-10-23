<?php
//Make a truely unique brisskit id
//It is possible that someone else will be running this at the same time, and may
//come up with the same random number, so need to LOCK the table for READS by anyone.
//This will have the effect of serializing all calls to this and will slow the
//process down a lot!

//24/5/12
//Olly Butters

#connect to the database using the options in the civicrm mysql config file

class ADD_Tool_IdGenerator {
	private $selStmt=null;
function __construct() {
        $add = ADD::sys();
	$this->_dbh=ADD::db()->getConn();
	$this->createIdTable();
}

#get the brisskit institutional prefix from the brisskit config file
private function getPrefix() {
	return "ILH";
}

#create tables in admin database to store used brisskit IDs
function createIdTable() {
	
	$con = $this->_dbh;
	$idLength = $this->getLength() + strlen($this->getPrefix());
	$query = "CREATE TABLE IF NOT EXISTS `ilh_existing_id` (
	  `id` varchar({$idLength}) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1";
	$con->exec($query);
	
	$con->commit();
}

function getLength() {
	return 8;
}
function idExists($id) {
	if (!$this->selStmt) {
		$con = $this->_dbh;
		$this->selStmt = $con->prepare("SELECT COUNT(*) FROM ilh_existing_id WHERE id = ?");
	}
	$this->selStmt->execute(array($id));
	$row = $this->selStmt->fetch();	
	return $row[0]==0 ? false : true;
}

#create brisskit ID
private function generateId($insStmt)
{
	$prefix = $this->getPrefix();
	$con = $this->_dbh;


	$maxNum = pow(10,$this->getLength())-1;

	//In a while loop as we may pick an existing number.
	$counter=0;
	$ok=FALSE;
	
	$rows = $con->query("SELECT COUNT(*) FROM ilh_existing_id")->fetchAll();

	$rowCount = $rows[0][0];
	if ($rowCount>=$maxNum) throw new Exception("No unique numbers left in range 1 - ".$maxNum);
	
	
	while(!$ok)
	{
		$proposedId=mt_rand(1,$maxNum);
		$proposedId = sprintf($prefix."%0".$this->getLength()."s",$proposedId);
		
		if (!$this->idExists($proposedId)) $ok=true;
		
		$counter++;
	}
		
	$insStmt->execute(array($proposedId));
	

	return $proposedId;
}
    function generateIds($number)
    {
        $prefix = $this->getPrefix();
        $con = $this->_dbh;
        $con->beginTransaction();

        //Check only letters and numbers
        if(!ctype_alnum($prefix))
        {
            throw new Exception("Prefix contains non-alphanumeric characters: $prefix");
        }

        //LOCK the table so other people cant even READ it.
        $con->query("LOCK TABLES ilh_existing_id WRITE");
        $insStmt = $con->prepare("INSERT INTO ilh_existing_id VALUES (?)");
		$ids = array();
        for($i=0;$i<$number;$i++) {
            $ids[] = $this->generateId($insStmt);
		}

        $con->query("UNLOCK TABLES");
        $con->commit();

        return $ids;
    }
}
?>
