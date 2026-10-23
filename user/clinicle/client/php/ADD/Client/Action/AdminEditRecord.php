<?php

class ADD_Client_Action_AdminEditRecord extends ADD_Client_Action_EditRecord {
	function isAvailable() {
		return CLE::sys()->getAccessManager()->isAdmin();
	}

}
?>

		