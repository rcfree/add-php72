<?php

class ADD_Field_Option_SearchReplaceDictionaryItems extends ADD_Field_Option_DictionaryItems {
	private $_dictTable;
    function initOptions($field,$page) {
        $dictTable = $field->assertSetting('dictionary_table');
        $this->_dictTable = $dictTable;
		$this->_init=true;
    }	
    function findItemsMatchingTerm($term,$startsWith=false) {
    	$searchFor = "cancer";
		$replaceWith = "neoplasm";
    	$term=str_ireplace($searchFor, $replaceWith,$term);
		$params = array();
		$wordList = str_getcsv($term,' ');
        $wordClauses = array();
        foreach($wordList as $word) {
        	$params[] = "%$word%";
			$wordClauses[] = "title like ?";
		}
		$where = "where is_deleted=0 ";
		$where.=" and (".implode(" and ",$wordClauses).")";
		$sql = "select `value`, `title` from `icd10_code` $where";

		$rows = ADD::db('dictionary')->fetchAll($sql,$params);
		$items = array();
    	/*
    	foreach($rows as $row) {
    		$item=array();
    		$item[$row['value']]=$row['title'];
			$items[]=$item;
		}
		 * 
		 */
    	return $rows;
    }
    
}