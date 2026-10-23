<?php


class ADD_ModuleInstall_Dictionary_Plugin_ICD10 implements ADD_ModuleInstall_Dictionary_PluginInterface {
    private $topLevels = array();
    private $topCounts=array();
    private $codes = array();
    function beforeProcessRow(&$row) {
        $ddb = ADD::db('dictionary');
        
        if (preg_match("/^[A-Z][0-9A-Z]{1,3}$/",$row['value'])) {
            $topCode = $row['value'];
            $this->topLevels[$topCode]=$row['title'];
            if(!isset($this->topCounts[$topCode])) $this->topCounts[$topCode]=0;
            $row=null;
            return;
        }
        $row['concept']=$row['value'];
        list($topCode,$lowerCode) = explode(".",$row['value']);
        if($topCode) $groupLabel = $this->topLevels[$topCode];
        
        $row['group']=$topCode;
        $row['group_title']=$groupLabel;
        $this->codes[]=$row['value'];
        if(!ADD_Util::isEmpty($row['extra'])) $row['title']=$row['title']." (".$row['extra'].")";
        $this->topCounts[$topCode]++;
        
    }
    function afterProcessRow(&$row) {
    }
    function beforeImport($dataFile) {
            $dataFile->setFieldSep("\t");
            $dataFile->setTrimFields(true);
            //$dataFile->setLimit(10);
    }
    function afterGetFields(&$fields) {
            $fields = array('value','alt_value','usage','usage_uk','title','extra');
    }
    function afterImport($core) {
        $dict = ADD::db('dictionary')->getDictionaryByName("icd10");
       $extraItems = array();
        foreach($this->topCounts as $topCode => $count) {
            if ($count>0) continue;
            $title = $this->topLevels[$topCode];
            $extraItems[]=array(
                'value'=>$topCode,
                'title'=>$this->topLevels[$topCode],
                'concept'=>$topCode,
                'group'=>$topCode,
                'group_title'=>$title,
            );
        }
        $core->createItems($dict,$extraItems,false);
        
        $ddb = ADD::db('dictionary');
        $placeholders = array_fill(0,count($this->codes),"?");
        $params = array_merge(array($dict['id']),$this->codes);
        $oldTerms = $ddb->fetchAll("select * from dictionary_item where dictionary_id=? and is_disabled=0 and value not in(".implode(",",$placeholders).")",$params);
        ADD::log()->info("Old terms found:".count($oldTerms));
        foreach($oldTerms as $oldTerm) {
            $oldTerm['is_disabled']=1;
            $oldTerm['title'].=" (non-NHS term)";
            $ddb->update("dictionary_item",$oldTerm);
        }
    }
    
   
    public function processFields(&$fields) {
        
    }
}