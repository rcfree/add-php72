<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Service\Remote;
use Guzzle\Http\Client;

class AccessFactory {
	function newAccess($url, $apikey) {
		$guzzle = new Client($url);
		return new Access($guzzle,$apikey);
	}
}		
