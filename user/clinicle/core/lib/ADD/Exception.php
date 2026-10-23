<?php
class ADD_DatabaseException extends Exception { }
class ADD_FormException extends Exception { }
class ADD_PatientException extends Exception { }
class ADD_EncounterException extends Exception { }
class ADD_AccessException extends Exception { }
class ADD_FieldException extends Exception { }
class ADD_MapException extends Exception { }
class ADD_UtilException extends Exception { }
class ADD_PageException extends Exception { }
class ADD_PageExistsException extends Exception { }
class ADD_ReportException extends Exception { }
class ADD_ApiException extends Exception { }
class ADD_ModuleException extends Exception { }
class ADD_SystemException extends Exception { }
class ADD_ComponentException extends Exception { }
class ADD_ToolException extends Exception { }
class ADD_ContentException extends Exception { }
class ADD_NoPermissionToAccessException extends Exception { }
class ADD_NoPermissionToSubmitException extends Exception { }
class ADD_DirectoryDoesNotExistException extends Exception { }
class ADD_ModuleDoesNotExistException extends Exception { }
class ADD_ComponentNotInCollectionException extends Exception { }
class ADD_NullValueProvidedException extends Exception { }
class ADD_FormDoesNotExistException extends Exception { }
class ADD_EncounterNotFoundException extends Exception { }
class ADD_TooManyEncountersException extends Exception { }
class ADD_IdenticalEncounterException extends Exception { }
class ADD_FieldDoesNotExistException extends Exception { }
class ADD_FieldExistsException extends Exception { }
class ADD_SettingNotDefinedException extends Exception { }
class ADD_PatientExistsException extends Exception { }
class ADD_DictionaryNotFoundException extends Exception { }
class ADD_DictionaryItemNotFoundException extends Exception { }
class ADD_DictionaryGroupNotFoundException extends Exception { }
class ADD_PageNotFoundException extends Exception { }
class ADD_ModuleDependencyException extends Exception { }
class ADD_DataTruncatedException extends Exception { 
    function __construct($message,$patient) {
        parent::__construct($message);
        $this->_patient=$patient;
    }
    function getPatient() {
        return $this->_patient;
    }
}
class ADD_AjaxFieldException extends Exception { 
    function __construct($message,$fieldTitle,$fieldName) {
        parent::__construct($message);
        $this->_fieldTitle=$fieldTitle;
		$this->_fieldName=$fieldName;
    }
    function getFieldTitle() {
        return $this->_fieldTitle;
    }
	function getFieldName() {
        return $this->_fieldName;
    }
}
?>