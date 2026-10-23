<?php

class ADD_Database_Token extends ADD_Database {

    private $_st = null;

    public function unregister() {
        return;
            if (!$this->_st) throw new ADD_SystemException("Token was not registered in this page");
            $this->_deleteToken($this->_st);
    }
    	public function register($values=array()) {
            $add = ADD::sys();
            
            $token = $add->getParam('t') ? $add->getParam('t') : ADD_Util::getArrayValue(ADD_Component::TOKEN,$values);

            if (!$token) {
                    ADD::web()->addWarning("No '".ADD_Component::TOKEN."' post/'t' get token was provided to the page. Your submitted data has been saved, but please contact the administrator.");
                    return true;
            }
            $st = $this->_getSubmitToken($token);
            
            if ($st) {
                return false;
            }
            else {
                $st = $this->_createSubmitToken(array("uuid"=>$token,"timestamp"=>time()));
            }
            $this->_st = $st;
            return true;
	}
        public function getSubmitToken() {
            return $this->_st;
        }
        
        public function generate() {
            return ADD_Util::token();
        }
         function _getSubmitToken($id) {
            $this->lockTables("submit_token","write");
            $st =  $this->fetchFirst("select * from submit_token where uuid=?",$id);
            $this->unlockTables("submit_token");
            return $st;
        }
        function _createSubmitToken($token) {
            $st = null;
            try {
                $this->lockTables("submit_token","write");
                $st = $this->insert("submit_token",$token);
            }
            catch(Exception $ex) {
                    if (preg_match("/Unable to execute INSERT statement.+Duplicate entry/",$ex->getMessage())) {
                        return false;
                    }
            }
            $this->unlockTables("submit_token");
            return $st;
        }
        
        function _deleteToken($token) {
            $this->lockTables("submit_token","WRITE");
            $this->execute("delete from submit_token where uuid=?",$token['uuid']);
            $this->unlockTables();
        }

}
