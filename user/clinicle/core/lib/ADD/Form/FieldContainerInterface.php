<?php
interface ADD_Form_FieldContainerInterface {
	function getTopLevelFields();
	function getNextField($searchField);

	function setChildFieldsByName($childFieldsByName);
    
	function addChildField($parentFieldName,$childField);
        
	function setParentFieldsByName($parentFieldsByName);

	function getFields();

	function getChildFieldsByName();

	function getParentFieldsByName();

	function getParentField($name);

	function getChildFields($fieldName);

	function getFieldsByName();

	function getField($fieldName);
	
	function addField($field);
	function unshiftField($field);
	
	function areFieldsInit();
	function setFieldsInit($fieldsInit);
	
	function getChildFieldAtPosition($position);
}
?>
