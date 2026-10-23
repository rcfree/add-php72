<?php

class ADD_Util {
	static function loadClass($module_name,$args = null,$args2=null, $args3=null) {
            
            ini_set("track_errors","1");
		$module_path=str_replace("_","/",$module_name).".php";
                //if (!class_exists($module_name)) {
                    //if(!include_once($module_path)) throw new ADD_UtilException("Unable to open class '$module_path'");
                    //if(isset($php_errormsg)) 
                    if(!class_exists($module_name)) throw new ADD_UtilException("Class '{$module_name}' could not be loaded");
               // }
		$module = new $module_name($args,$args2,$args3);
		if (!$module) throw new ADD_UtilException("Class '{$module_path}' could not be instantiated");
		return $module;
	}
	
	static function token() {
		//return openssl_random_pseudo_bytes(8);
                return uniqid("",true);
	}
	
	static function determineEntityValue($entity, $entity_key) {
		if (isset($entity[$entity_key])) {
			return $entity[$entity_key];
		}
		else if (isset($entity['obs'])) {
			if (isset($entity['obs'][$entity_key])) {
				return $entity['obs'][$entity_key];
			}
			else {
				throw new Exception("No value for key '".$entity_key."' in entity:".print_r($entity,true));
			}
		}
		else {
			return null;
		}
	}
	static function createPMI() {
			
		$add = ADD::sys();
            $config = $add->config('PatientLookup');
	    return LRB_PatientLookup::factory($config);
	}
    
	
	static function evaluate($expression) {
		$expression=str_replace("|",",",$expression);
		$eval = new ADD_Tool_Evaluate($expression);
		return $eval->evaluate();
	}	
	
	static function makePath($pathParts=array(),$query=array()) {
		$queryList = array();	
		foreach($query as $name => $value) {
			$queryList[]=$name."=".$value;
		}
		$queryString=join("&",$queryList);
		return join("/",$pathParts).(strlen($queryString)>0 ? "?$queryString" : "");
	}
	
	static function getArrayValue($key,$array,$default=null) {
                if (is_object($array)) {
					if (get_class($array)=="SimpleXMLElement") {
						if (isset($array[$key])) return (string) $array[$key];
						return $default;
					}
					else {
						throw new ADD_PageException("Cannot get data from object in getArrayValue function");
					}
                }
		if (is_array($key)) {
			$outputs = array();
			foreach($key as $k) {
				$outputs[]=self::getArrayValue($k,$array,$default);
			}
			return $outputs;
		}
		if (is_object($key)) throw new ADD_PageException("Cannot get data using key as object in getArrayValue function");
		if (isset($array[$key])===false || ADD_Util::isEmpty($array[$key])) return $default;
		if ($array[$key]===0) return '0';
		return $array[$key];
	}
	
	static function getCsvArrayValues($key, $array, $singleAsString=false) {
		$csvValues = ADD_Util::getArrayValue($key,$array);
		return ADD_Util::getCsvValues($csvValues,$singleAsString);
	}
        
        static function getCsvValues($csvValues, $singleAsString=false) {
		if (is_array($csvValues)) return $csvValues;
		if (!isset($csvValues) || empty($csvValues)) return array();
		#$optionsList = preg_replace("/\r|\n/",",",$csvValues);
		$optionItems = preg_split("/\n|,/",$csvValues);
			
		#then split each options on = to get the key and title
		$options = array();
		foreach($optionItems as $item) {
			$temp=explode("=",$item);
			if(count($temp)==2) {
				$matches = array();
				if (preg_match("/(.+)\[(\d+)]/",$temp[0],$matches)) {
					$key = trim($matches[1]);
					$index = $matches[2];
					if (!isset($options[$key])) $options[$key]=array();
					$options[$key][$index]=trim($temp[1]);
				}
				else {
					$key = trim($temp[0]);
					if(isset($options[$key])) throw new ADD_UtilException("Key '$key' is a duplicate value in CSV list:".$csvValues);
					$options[$key]=trim($temp[1]);
				}
			}
			else {
				$options[(string) trim($item)]=trim($item);
			}
		}
		
		if (count($options)==1) {
			$keys = array_keys($options);
			$vals = array_values($options);
			if ($keys == $vals) $options = array_shift($keys);
		}
		return $options;
	}
	
	static function convertToPascal($text) {
		$words = preg_split("[_|-]",$text);
		if (count($words)==1) {
			return ucfirst($text);
		}
		else {
			$newText = "";
			foreach($words as $word) {
				$newText.=ucfirst($word);
			}
			return $newText;
		}
	}
	static function convertToPerlish($text) {
		$words = preg_split("/([A-Z])/", $text, 0, PREG_SPLIT_DELIM_CAPTURE);
		if (count($words)==1) return $words[0];
		array_shift($words);
		$newWords = array();
   		for($w=0; $w<count($words)/2;$w++) {
   			$newWords[] = strtolower($words[($w*2)].$words[($w*2)+1]);
   		}
   		return join("_",$newWords);
	}
	
	/**
	 * Check all headings in one array are present in the source array
	 * @param array $headings
	 * @param array $checkFields
	 */
	static function checkFields($headings, $checkFields) {
		#confirm that at least the correct number of headings are present
		if (count($headings)<count($checkFields)) {
			throw new Exception("Not enough columns in tab-delimited file");
		}
	
		#confirm that each of the fields are present in the heading
		foreach($checkFields as $checkField) {
			if (!in_array($checkField,$headings)) {
				throw new Exception("checkFields found no '$checkField' field");
			}
		}
	}
	
	static function cleanFieldValues($values) {
		$cleanValues = array();
		foreach($values as $key => $value) {
			if (preg_match('/^form/',$key) || $key=='op' || $key=='submit' || preg_match('/^\_\_/',$key)) continue;
			$cleanValues[$key]=$value;
		}
		return $cleanValues;
	}
	
	static function timeFromISOToDMYHM($date) {
            $dt = $date ? DateTime::createFromFormat("Y-m-d H:i:s",$date,new DateTimeZone(date_default_timezone_get())) : null;
                if (!$dt) return null;
		return $dt->format("d/m/Y H:i");
	}
	
	static function dateFromISOToDMY($date) {
            $dt = $date ? DateTime::createFromFormat("Y-m-d H:i:s",$date,new DateTimeZone(date_default_timezone_get())) : null;
                if (!$dt) return null;
		return $dt->format("d/m/Y");
	}
        
        static function dateFromDMYToISO($date) {
		$dt = $date ? DateTime::createFromFormat("d/m/Y",$date,new DateTimeZone(date_default_timezone_get())) : null;
		return $dt->format("Y-m-d H:i:s");
	}
	
	static function formatDMYDate($parsableDate) {
		$date = date_parse($parsableDate);
		return $date['day']."/".$date['month']."/".$date['year']; 
	}
	
	static function makeArray($value, $default=array()) {
		if (empty($value)) return $default;
		if (!is_array($value)) return array($value);
		return $value;
	}
	
	static function getConfigPath() {
		$confProfile=getenv('ADD_PROFILE');
		if (empty($confProfile)) $confProfile="local";
		$confPath=getenv('ADD_PATH').'/config/'.$confProfile;
		if (!file_exists($confPath)) throw new Exception("Config profile [$confProfile] not found. Expected location: ".$confPath);
		return $confPath;
	}
	static function dateFromDmy($date) {
		$dt = $date ? DateTime::createFromFormat("d/m/Y",$date,new DateTimeZone(date_default_timezone_get())) : null;
		return $dt;
	}
	static function dateFromDmyHm($date) {
		$dt = $date ? DateTime::createFromFormat("d/m/Y H:i",$date,new DateTimeZone(date_default_timezone_get())) : null;
		return $dt;
	}
	static function pluralize($count, $single, $plural=null) {
		if(!$plural) {
			if (preg_match("/y$/",$single)) {
				$plural = preg_replace("/y$/","ies",$single);
			}
			else{
				$plural = $single."s";
			}
		}
		if(is_array($count)) $count=count($count);
		return $count == 1 ? $single : $plural;
	}
	static function serialize($data) {
		return json_encode($data,JSON_FORCE_OBJECT);
	}
	static function unserialize($data) {
		if(is_array($data)) return $data;
		return json_decode($data,true);
	}
        static function unserializeValues(&$array,$keys) {
		foreach($keys as $key) {
			$array[$key]=ADD_Util::unserialize($array[$key]);
		}
	}
	static function uri($path,$query=array(),$noSitepath=false) {
            $add = ADD::sys();
            if (count($query)>0) {
                $qlist = array();
                foreach($query as $key => $value) {
                    $qlist[]="$key=".urlencode($value);
                }
                return ($noSitepath ? "" : $add->config('site_path'))."/".$path."?".implode("&",$qlist);
            }
            else {
                    return $add->config('site_path')."/".$path;
            }
	}
	static function logFunction($func,$args) {
            $L=ADD::log();
            $output = array();
            foreach($args as $arg) {
                    if (is_object($arg)) {
                            if(method_exists($arg,"getId")) {
                                    $output[]=$arg->getId();
                            }
                            elseif(method_exists($arg,"getName")) {
                                    $output[]=$arg->getName();
                            }
                            else {
                                    $output[]=get_class($arg);
                            }
                    }
                    elseif(is_array($arg)) {
                            $output[]=json_encode($arg);
                    }
                    else {
                            $output[]=$arg;
                    }
            }

            $L->debug("logFunction:".$func."(".implode(",",$output).")");
        
	}
        static function isEmpty($value) {
            if (is_array($value)) {
                if (count($value)==0) return true;
                return false;
            }	
			if (is_object($value)) return false;
			if ($value === 0) return false;
            if ($value === '0') return false;
            if (!$value) return true;
            if (strlen($value)==0) return true;
            return false;
        }
        static function modulePath($modName) {
            $add = ADD::sys();
            $mod = $add->getModule($modName);
            return $mod->getPath();
        }
        static function val($key,$array,$default=null) {
            return self::getArrayValue($key,$array,$default);
        }
        static function vals($keysList,$array,$default=null) {
            $values = array();
            foreach($keysList as $key) {
                $values[]=self::getArrayValue($key,$array,$default);
            }
            return $values;
        }
        
        static function convertDateToDb(&$array,$keys) {
            if (!is_array($keys)) $keys= array($keys);
            $sourceDf = ADD::sys()->assertConfig('date_format');
            
            foreach($keys as $key) {
                if(!isset($array[$key]) || ADD_Util::isEmpty($array[$key])) {
                    $array[$key]=null; 
                    continue;
                }
                
                $date = $array[$key];
                
                $dt = $date ? DateTime::createFromFormat("d/m/Y",$date,new DateTimeZone(date_default_timezone_get())) : null;
                if (!$dt) {
                    $array[$key]=null; 
                    continue;
                }
                if ($dt->format($sourceDf)!==$date) throw new ADD_DatabaseException("Date '$date' does not appear to be valid");
                
                $array[$key] = $dt->format("Y-m-d");
            }
        }
        
        static function convertDateFromDb(&$array,$keys=null) {
            if (!is_array($keys)) $keys = array($keys);
            
            $targetDf = ADD::sys()->assertConfig('date_format');
            
            foreach($keys as $key) {
                if(!isset($array[$key])) continue;
                
                $date = $array[$key];
                if ($date=="0000-00-00") {
                    $array[$key]=null;
                    return;
                }
                $dt = $date ? DateTime::createFromFormat("Y-m-d",$date,new DateTimeZone(date_default_timezone_get())) : null;
                if (!$dt) continue;
                $array[$key] = $dt->format($targetDf);
            }
        }
        
        static function updateTimestamp(&$array,$key) {
            $array[$key]=date(DATE_ATOM);    
        }
        
        static function pascalKeys($array) {
            $data = array();
            foreach($array as $key => $value) {
                $data[ADD_Util::convertToPascal($key)]=$value;
            }
            return $data;
        }
        
        static function getVersionInfo($path) {
            $file = $path."/VERSION";
            if (!file_exists($file)) throw new Exception("No VERSION file found in '$path'");
            $file = file_get_contents($path."/VERSION");
			return array_map(function($value) { return rtrim($value,"\n"); },explode(" ",$file));
        }
       static function nameToLabel($name) {
          return ucfirst(str_replace("_"," ",$name));
       }
       
       static function labelToName($label) {
           $name = preg_replace('/[^0-9a-zA-Z]/', "_", $label);
           return strtolower($name);
       }
	   
	   static function inputLine($length=null) {
		   if(function_exists("readline")) {
			   return readline();
		   }
		   $in = fopen("php://stdin","r");
           $value = fgets($in,$length+1);
		   fclose($in);
		   return $value;
	   }
	   static function roundNumber($value,$decimalPlaces) {
			if(ADD_Util::isEmpty($value)) return null;
			return number_format($value,$decimalPlaces, ".","");
		}
	   
	   static function currentPageURL() {
		$pageURL = 'http';
		 if (ADD_Util::val("HTTPS",$_SERVER) == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		 $port = ADD_Util::val("SERVER_PORT",$_SERVER);
		 if(!$port) return ADD_Util::val("SCRIPT_FILENAME",$_SERVER);
		 if ($port != "80") {
		  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		 } else {
		  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		 }
		 return $pageURL;
		}
		static function hash($value) {
			return sha1($value);
		}
		static function hashData($structure) {
			//ADD::log()->info("serialize:".serialize($structure));
			$hash = self::hash(serialize($structure));
			//ADD::log()->info("hash:".$hash);
			
			return $hash;
		}
		static function scanfilesInDir($scanDir, $regExMatch) {
			$fileNames = scandir($scanDir);
			$inputFiles = array();
			foreach($fileNames as $fileName) {
				$fullPath = $scanDir."/$fileName"; 
				if (is_dir($fullPath)) continue;
				if(!preg_match($regExMatch,$fullPath)) continue;
				$inputFiles[]=$fullPath;
			}
			return $inputFiles;
		}

}
?>
