<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Component_StudyBreadcrumb extends ADD_Component {
	public function breadcrumb(&$breadcrumb) {
		$breadcrumb[1]=preg_replace("/\<p.+/","",$breadcrumb[1]);
	}
	public function title(&$title) {
		$title="Study summary";
	}
}