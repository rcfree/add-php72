<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_GridOption {
	public function generateOption(ADD_Field $field, $id,$data) {
		$html = new ADD_Content_Html();
		$html->addTextElement("a", "<i class='icon-pencil icon-white'></i>", array("class"=>"btn btn-success btn-mini","href" => "javascript:;", "onclick" => "ADD.call('" . $field->getName() . "','edit',[$id])"));
		return $html->output();	
	}
}