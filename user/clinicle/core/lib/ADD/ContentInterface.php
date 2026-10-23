<?php
interface ADD_ContentInterface {
	function init();
	function output();
    function mimeType();
	function headers();
}