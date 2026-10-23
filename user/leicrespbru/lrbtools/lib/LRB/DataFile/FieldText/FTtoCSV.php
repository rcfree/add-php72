<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CSVToFT
 *
 * @author rcf8
 */
class LRB_DataFile_FieldText_FTtoCSV {
    public function convert($fieldText) {
        $output = array();
		$output['Level'] = $this->determineLevel($fieldText);
       
		$title = $this->extractTitle($fieldText);
		$output['Title']=$title;
        
        $output['Settings']=$this->determineSettings($fieldText);
        $output['Options']=$this->determineOptions($fieldText);
        
		if(isset($output['Settings'])) {
			$fieldText=str_replace("{".$output['Settings']."}","",$fieldText);
		}
		if (isset($output['Options'])) {
			$fieldText=str_replace("[".$output['Options']."]","",$fieldText);
		}
		 
        $output['Field']=$this->determineField($fieldText,$title);
		$rename = $this->determineRename($fieldText);
		if($rename) $output['Rename']=$rename;
        $output['Required']=$this->determineRequired($fieldText);
        $output['Type']=$this->determineType($fieldText);
        return $output;   
    }
	private function determineRename($fieldText) {
		if (preg_match("/\@([A-Za-z0-9|_]+)\s{0,1}/",$fieldText,$matches)) return $matches[1];
		return null;
	}
    private function determineLevel(&$fieldText) {
		$prevFieldText = $fieldText;
		$fieldText = preg_replace("/^[\t]*(-)/","",$fieldText);
		if($prevFieldText!=$fieldText) {
			return "DEL";
		}
		preg_match("/^([\t\s]*)(.+)/",$fieldText,$matches);
		
        return strlen($matches[1])+1;
    }
    private function determineType($fieldText) {
        $matches = array();
        if (!preg_match("/\:([a-z|_]+)\s{0,1}/",$fieldText,$matches)) return "markup";
        return $matches[1];
    }
    private function extractTitle(&$fieldText) {
        if (preg_match('/^[\t\s\>]*\"(.+)\"(.+)/',$fieldText,$matches)) {
            $fieldText = trim($matches[2]);
            return trim($matches[1]);
        }
        
        preg_match("/^[\t\s\>]*([^:\$\*]*)/",$fieldText,$matches);
        return trim($matches[1]);
    }
    private function determineField($fieldText,$title) {
        $matches=array();
        $field = preg_match('/\$([A-Za-z0-9\-\_]+)/',$fieldText,$matches) ? $matches[1] : self::titleToField($title);
        if (strlen($field)>40) throw new LRB_FieldTooLongException("Field '$field' determined from FieldText '$fieldText' and title '$title' is too long");
        return $field;
    }
    private function determineRequired($fieldText) {
		$matches = array();
        if (!preg_match("/(.*)\*(.*)/",$fieldText,$matches)) {
			return "";
		}
		return 1;
    }
    
    static function titleToField($title) {
		$title=trim($title);
		$title=strip_tags($title);
		
		$title=preg_replace("/[^0-9a-zA-Z\s-]/","",$title);
		
		if(strlen($title)>30) $title=substr($title,0,30);
		$field = preg_replace('/[\s\-]/', "_", $title);
        return strtolower($field);
    }
    private function determineSettings($fieldText) {
        $matches=array();
        if (!preg_match('/\{(.+)?\}/',$fieldText,$matches)) {
            return "";
        }
        return $matches[1];
    }
    private function determineOptions($fieldText) {
        $matches=array();
        if (!preg_match('/\[(.+?)\]/',$fieldText,$matches)) {
            return "";
        }
        return $matches[1];
    }
}
class LRB_FieldTooLongException extends Exception {};
