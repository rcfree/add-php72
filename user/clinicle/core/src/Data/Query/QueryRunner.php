<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Data\Query;
use ClinicLE\Common\Connection\AbstractDatabaseService;
use ClinicLE\Data\Query\QueryInterface;
/**
 * Description of QueryRunner
 *
 * @author rob
 */
class QueryRunner extends AbstractDatabaseService {
	function getRows(QueryInterface $query) {
	    if($query->isDisabled()) {
	        return array();
        }
		list($sql,$params) = $query->generateFullSQL();
		return $this->getConnection()->fetchAll($sql,$params);
	}
	function countRows(QueryInterface $query) {
        if($query->isDisabled()) {
            return 0;
        }
		list($sql, $params) = $query->generateCountSQL();
		return $this->getConnection()->fetchSingleValue($sql,$params);
	}
}
