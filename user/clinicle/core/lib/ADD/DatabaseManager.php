<?php

class ADD_DatabaseManager {
    
   function get($type="BASE") {
       if (!isset($this->_db[$type])) {
                $className = ADD_Util::convertToPascal($type);
                $db = $type=="BASE" ? ADD_Util::loadClass("ADD_Database") : ADD_Util::loadClass("ADD_Database_{$className}");
                $this->_db[$type]=$db;
        }

        $db = $this->_db[$type];
        return $db;
   }
   
}
