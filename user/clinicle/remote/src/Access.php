<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Service\Remote;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use ClinicLE\Common\Util;
use ClinicLE\Service\Remote\Exception\RemoteDataException;

class Access {

	private $client;
	private $apikey;

	/**
	 * 
	 * @param Client $client
	 * @param type $apikey
	 */
	function __construct(Client $client, $apikey) {
		$client->setConfig(array(
			"request.options"=>
			array(
				"query"=>array("apikey"=>$apikey)
			)
		));
		$this->client = $client;
		$this->apikey = $apikey;
	}

	function getClient() {
		return $this->client;
	}

	function getPatientWithIdentifier($identifier) {
		$request = $this->client->get("/api/patient", array(),array(
			"query"=>array("identifier" => $identifier)
			)
		);
		
		$request->send();
		
		$response = $request->getResponse();
		$rawData = $response->getBody(true);
		$jsonData = json_decode($rawData,true);
        $patientData = Util::val("data",$jsonData);
		if(empty($patientData)) {
			throw new RemoteDataException("Patient with identifier '$identifier' was not found");
		}
		return $jsonData;
	}
	
	function getEncountersUsingPim($patientId, $pim, $query = array()) {
		$query['pid']=$patientId;
		$query['pim']=$pim;
		$request = $this->client->get("/api/encounters", array(),array(
			"query"=>$query
			)
		);
		
		$request->send();
		
		$response = $request->getResponse();
		
		$rawData = $response->getBody(true);
		$jsonData = json_decode($rawData,true);
		
		if(Util::val('error',$jsonData)) {
			throw new RemoteDataException(Util::val('error_text',$jsonData));
		}
		
		return Util::val('data',$jsonData);
	}

}
