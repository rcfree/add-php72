<?php
interface ADD_Client_Action_GridRowActionInterface {
	function isAvailable();
	function outputMarkup();
	function afterOutputMarkup(ADD_Content_Html $html);
	function getColumnHeading();
}
