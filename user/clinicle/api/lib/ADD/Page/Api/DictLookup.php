<?php

class ADD_Page_Api_DictLookup extends ADD_Page_Api {
        function apiOutput() {
            $add = ADD::sys();
            
            $ddb = ADD::db('dictionary');
            list($term, $dict,$minLength, $words, $noMessage,$fromStart) = $add->getParams(array('term','dict_name','min_length','words','no_message','from_start'));
            $dictArgs = explode(":",$dict);
            $dictName = $dictArgs[0];
            $dictOption = ADD_Util::val(1,$dictArgs,"DictionaryItems");
            
            if ($term && strlen($term) < $minLength) {
                $data = array( array('value' => null, 'html' => "Please enter $minLength or more characters to search", 'message' => true));
                return $data;
            }
            //$terms = explode(" ",trim($term));//$words ? ) : array(trim($term));
            
            if(!$dictOption) $dictOption="DictionaryItems";
            $option = ADD_Util::loadClass("ADD_Field_Option_$dictOption");
            
            $option->setDictionaryName($dictName);
            $dictItems = $option->findItemsMatchingTerm($term,$fromStart);
            
            if (!$dictItems) $dictItems=array();
            $items = array();
			
			$terms = str_getcsv($term,' ');
            foreach ($dictItems as $ditem) {
                    $html = $ditem['title'];
					
					foreach($terms as $term) {
						$html = str_ireplace($term,"<b>".$term."</b>",$html);
					}					
                    $ditem['html'] = $html;
                    if (isset($ditem['data'])) $data = ADD_Util::unserialize($ditem['data']);
                    $data['title'] = $ditem['title'];
                    $data['value'] = $ditem['value'];
                    //if (isset($ditem['synonyms'])) $data['synonyms']=explode("|",$ditem['synonyms']);
                    $ditem['data'] = $data;
                    $items[] = $ditem;
            }
            if (count($items) == 0) {
                if ($noMessage) {
                        $items = array();
                } else {
                        $items = array( array('value' => null, 'html' => 'No items match terms', 'message' => true));
                }
            }
            return $items;
        }
/*
 * synonym handling bit
 * 
                $diq = DictionaryItemQuery::create();
		$diq -> filterByDictionary($dict);
                $scount = 1;
                $swhere = array();
                foreach($terms as $titem) {
                    $diq -> condition('syn'.$scount, 'DictionaryItem.Synonyms LIKE ?', "%{$titem}%");
                    $swhere[]='syn'.$scount;
                    $scount++;
                }
                $diq -> where($swhere,'and');
		
		foreach ($diq->find() as $di) {
			$ditem = $di -> toArray(BasePeer::TYPE_FIELDNAME);
			$data = ADD_Util::unserialize($ditem['data']);
			$synonyms = explode("|",$ditem['synonyms']);
                        $synCount=0;
                        $synList = array();
                        foreach($synonyms as $synonym) {                          
                            foreach($terms as $titem) {
                                if(stristr($synonym,$titem)) {
                                    $synList[$synCount]=true;
                                }
                            }
                            $synCount++;
                        }
                        
                        foreach($synList as $idx => $ignore) {
                            $synonym = $synonyms[$idx];
                            $html = $synonym;
                            foreach($terms as $titem) {
                                $html = str_ireplace($titem, "<b>$titem</b>", $html);
                            }
                            $html.="*";
                            $ditem['html'] = $html;
                            $data['value'] = $ditem['value'];
                            $data['synonyms']=$synonyms;
                            $data['title'] = $synonym;                             
                            $ditem['data'] = $data;
                            $items[] = $ditem;
                        }
		}
                
		
		return $items;
 * 
 */
        
	
}
