<?php
 
/*
* Here comes the text of your license
* Each line should be prefixed with  *
 */
 
/**
* Description of Patient
*
* @author rcf8
*/
class ADD_Page_Api_Research extends ADD_Page_Api {
    function createMenuPath() {
       return "api/research/".$this->getName();
    }
}