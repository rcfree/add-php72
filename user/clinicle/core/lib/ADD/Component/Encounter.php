<?php


class ADD_Component_Encounter extends ADD_Component {
        protected $_encounterRecord = null;
		
        protected function initEncounterRecord($idValue=null) {
            throw new Exception("No 'initEncounterRecord' function in ".get_class($this));
        }
        public function getEncounterRecord($idValue=null) {
            if ($this->_encounterRecord) {
                if($idValue && $this->_encounterRecord->exists() && $idValue!=$this->_encounterRecord->getId()) {
	            	$this->reset();
				}
            }
			
            if (!$this->_encounterRecord) {
                $this->_encounterRecord = $this->initEncounterRecord($idValue);

            }
            return $this->_encounterRecord;
        }
        public function setEncounterRecord($encounterRecord) {
            $this->_encounterRecord = $encounterRecord;
        }
        public function reset() {
            $this->_encounterRecord = null;
        }
}
