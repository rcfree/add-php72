<?php
//Make a truely unique brisskit id
//It is possible that someone else will be running this at the same time, and may
//come up with the same random number, so need to LOCK the table for READS by anyone.
//This will have the effect of serializing all calls to this and will slow the
//process down a lot!

//24/5/12
//Olly Butters

#connect to the database using the options in the civicrm mysql config file

class LRB_IdGenerator {
function __construct($dsn, $isCiviDsn=false, $user=null, $pass=null) {
	if ($isCiviDsn) {
		$dsn=preg_match("/\/\/(.+)\:(.*)@(.+)\/(.+){0,1}\?/",$dsn,$matches);
		$dsn="mysql:host={$matches[3]};dbname={$matches[4]}";	
		$user = $matches[1];
		$pass = $matches[2];
	}
	
	//Connect to admin DB
	$dbh = new PDO($dsn, $user, $pass);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
	
	$this->_dbh=$dbh;
	$this->createIdTable();
}

#get the brisskit institutional prefix from the brisskit config file
private function getPrefix() {
	return "ILH";
}

#create tables in admin database to store used brisskit IDs
function createIdTable() {
	
	$con = $this->_dbh;
	$con->beginTransaction();
	$idLength = $this->getLength() + strlen($this->getPrefix());
	$query = "CREATE TABLE IF NOT EXISTS `ilh_existing_id` (
	  `id` varchar({$idLength}) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1";
	$con->exec($query);
	echo "Create ID table\n";
	$con->commit();
}

function getLength() {
	return 8;
}

#create brisskit ID
function generateId()
{
	$prefix = $this->getPrefix();
	$dbh = $this->_dbh;
	
	//Check only letters and numbers
	if(!ctype_alnum($prefix))
	{
    	throw new Exception("Prefix contains non-alphanumeric characters: $inst_prefix");
	}

	$maxNum = pow(10,$this->getLength())-1;
	$range = $maxNum-1;
	
	//LOCK the table so other people cant even READ it.
	$dbh->query("LOCK TABLES ilh_existing_id WRITE");
	
	//In a while loop as we may pick an existing number.
	$counter=0;
	$ok=FALSE;
	
	$rows = $dbh->query("SELECT COUNT(*) FROM ilh_existing_id")->fetchAll();

	$rowCount = $rows[0][0];
	if ($rowCount>=$maxNum) throw new Exception("No unique numbers left in range 1 - ".$maxNum);
	
	$stmt = $dbh->prepare("SELECT COUNT(*) FROM ilh_existing_id WHERE id = :id");
	$stmt->bindParam(':id',$id, PDO::PARAM_STR);
	
	while(!$ok)
	{
		$proposedId=rand(1,$maxNum);
		$proposedId = sprintf($prefix."%0".$this->getLength()."s",$proposedId);
		$id = $proposedId;
		$stmt->execute();
		$row = $stmt->fetch();
		
		if ($row[0]==0) $ok=true;
		
		$counter++;
	}
	
	$stmt = $dbh->prepare("INSERT INTO ilh_existing_id VALUES (:id)");
	$stmt->bindParam(':id',$id,PDO::PARAM_STR);
	
	$stmt->execute();
	
	//UNLOCK the table.
	$dbh->query("UNLOCK TABLES");

	return $id;
}
}
?>
