<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SubmitInterface
 *
 * @author rcf8
 */
interface ADD_Field_SubmitInterface {
	function submitValues($values, $page);
	function beforeSubmitValues(&$values,$page);
}
