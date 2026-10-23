<?php

class ADD_Field_DictionaryTable extends ADD_Field_Container_MultiEncBase {
	
	function generateTable($page,$items, $groupName) {
		$add = ADD::sys();
		$form = $page->getComponent('Form');
		$db = ADD::db('dictionary');
		
		$results = array();
		$header = array();
		$colNames = explode(";",$this->getSetting('cols'));
		$fields=array();
		foreach($colNames as $col) {
			$field=$form->getField($col);
			$header[]=$field->getSetting('col_title',$field->getTitle());
			$fields[]=$field;
		}
		$ff = new ADD_FieldFactory_RowData();
                if (!$items) return array($header,$results);
                
		foreach($items as $item) {
			$result=array();
			$values = array('value'=>$item['value'],'title'=>$item['title'],'group'=>$groupName);	
			
			if(isset($item['data'])) {
				$data = ADD_Util::unserialize($item['data']);
				$values = $values + $data;
			}
			foreach($fields as $field) {
				$field->clearValue();
				$field->populateValue($values,$page);
				
				$data = $field->getDisplayText($page,$item['id']);
				if ($field->getSetting('modal_action')) {
					$settings = $field->getSettings();
					$settings['no_encounter']=1;
					$settings['before_js']='beforeDictionary';
					$action = $ff->newInstance(array(
						'Title'=>$data,
						'Field'=>$field->getName(),
						'Type'=>'modal_dict_action',
						'Settings'=>$settings,
					));	
					
					$data = $action->getDisplayText($page,$item['id']);
				}
				
				if ($data) {
					$result[]=$data;
				}
				else {
					$result[]="";
				}
			}
			
			$results[]=array("data"=>$result);
		}
		
		return array($header,$results);
	}
	function addGroupTable($title,$type,$items, $showRemoveGroup=null,$page) {
            $groupName = ADD_Util::labelToName($title);
            list($header,$results) = $this->generateTable($page,$items,$groupName);
            $count = count($results);
            
            $ff = new ADD_FieldFactory_RowData();
            
            $settings = array("compress"=>1);
            if ($showRemoveGroup) {
                $legend = new ADD_Content_Html();
                $legend->addHtml("&nbsp;&nbsp;");
                $legend->addTextElement("a","<i class='icon-remove'></i>&nbsp;Remove this group..",array("class"=>"btn btn-mini","href"=>'javascript:;','onclick'=>"removeDictGroup({$showRemoveGroup['id']})"));
                $settings['legend_suffix']=$legend->output();
                
            }
           
            $ng_fset = $ff->newInstance(array('Title'=>$title." (".count($results)." ".ADD_Util::pluralize($results,$type).")", 'Field'=>$groupName."_group",'Type'=>'fieldset','Settings'=>$settings));
            $ng_fset->finaliseField($page);
            $ng_fset->startFieldHtml($page);	
            
            $this->addTableMarkup($header,$results,$page);
            $ng_fset->endFieldHtml($page);	
        }
	function addMarkup($page) {
		$db = ADD::db('dictionary');
                $dictName = $page->assertSetting('DictionaryEntry.dictionary');
                
		$dict = $db->getDictionaryByName($dictName);
                if (!$dict) {
                    throw new ADD_DictionaryNotFoundException("Dictionary pointed to by DictionaryEntry.dictionary has not been defined");
                }
                
                $items = $db->getDictionaryItemsWithNoGroup($dictName);
                $this->addGroupTable("No group","test",$items,null, $page);
		
		$dictGroups = $db->getDictionaryGroups($dictName);
                if (!$dictGroups) return;
                foreach($dictGroups as $group) {
                    $gname = $group['name'];
                    $gitems = $db->getDictionaryItemsInGroup($dictName, $gname);
                    $this->addGroupTable($group['title'],"test",$gitems,$group,$page);
                }

	}
}
?>