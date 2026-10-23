<?php

class ADD_Client_Action_AdminDeleteRecord extends ADD_Client_Action_DeleteRecord {
	function isAvailable() {
		return CLE::sys()->getAccessManager()->isAdmin();
	}

}


		