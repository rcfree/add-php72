<?php
namespace ClinicLE\Common;
use ClinicLE\Common\Exception\ClassLoadException;
use DateTime;
use DateTimeZone;

class Util {
	static function loadClass($module_name,$args = null,$args2=null, $args3=null) {
            
            ini_set("track_errors","1");
		$module_path=str_replace("_","/",$module_name).".php";
                if (!class_exists($module_name)) {
                    if(!include_once($module_path)) throw new ClassLoadException("Unable to open class '$module_path'");
                    //if(isset($php_errormsg)) 
                    if(!class_exists($module_name)) throw new ClassLoadException("Class '{$module_name}' could not be loaded");
                }
		$module = new $module_name($args,$args2,$args3);
		if (!$module) throw new ClassLoadException("Class '{$module_path}' could not be instantiated");
		return $module;
	}
	
	
	static function makePath($pathParts=array(),$query=array()) {
		$queryList = array();	
		foreach($query as $name => $value) {
			$queryList[]=$name."=".$value;
		}
		$queryString=join("&",$queryList);
		return join("/",$pathParts).(strlen($queryString)>0 ? "?$queryString" : "");
	}
	
	static function val($key,$array,$default=null) {
            if (is_object($array)) {
                                    if (get_class($array)=="SimpleXMLElement") {
                                            if (isset($array[$key])) return (string) $array[$key];
                                            return $default;
                                    }
                                    else {
                                            throw new \InvalidArgumentException("Cannot get data from object in 'val' function");
                                    }
            }
            if (is_array($key)) {
                    $outputs = array();
                    foreach($key as $k) {
                            $outputs[]=self::val($k,$array,$default);
                    }
                    return $outputs;
            }
            if (is_object($key)) throw new \InvalidArgumentException("Cannot get data using key as object in 'val' function");
            if (isset($array[$key])===false || Util::isEmpty($array[$key])) return $default;
            if ($array[$key]===0) return '0';
            return $array[$key];
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
	
	static function makeArray($value, $default=array()) {
		if (empty($value)) return $default;
		if (!is_array($value)) return array($value);
		return $value;
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
			$array[$key]=Util::unserialize($array[$key]);
		}
	}
	static function uri($path,$query=array()) {
            if (count($query)>0) {
                return "/".$path."?".http_build_query($query);
            }
            else {
                return "/".$path;
            }
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
        
        static function vals($keysList,$array,$default=null) {
            $values = array();
            foreach($keysList as $key) {
                $values[]=self::val($key,$array,$default);
            }
            return $values;
        }
        
        static function pascalKeys($array) {
            $data = array();
            foreach($array as $key => $value) {
                $data[Util::convertToPascal($key)]=$value;
            }
            return $data;
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
			if(Util::isEmpty($value)) return null;
			return number_format($value,$decimalPlaces, ".","");
		}
		static function hash($value) {
			return sha1($value);
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
	static function dateFromDmy($date) {
		$dt = $date ? DateTime::createFromFormat("d/m/Y",$date,new DateTimeZone(date_default_timezone_get())) : null;
		return $dt;
	}
	static function dateFromDmyHm($date) {
		$dt = $date ? DateTime::createFromFormat("d/m/Y H:i",$date,new DateTimeZone(date_default_timezone_get())) : null;
		return $dt;
	}
	   
        static function convertDateToDb(&$array,$keys) {
            if (!is_array($keys)) $keys= array($keys);
            $sourceDf = "d/m/Y";
            
            foreach($keys as $key) {
                if(!isset($array[$key]) || self::isEmpty($array[$key])) {
                    $array[$key]=null; 
                    continue;
                }
                
                $date = $array[$key];
                
                $dt = $date ? DateTime::createFromFormat("d/m/Y",$date,new DateTimeZone(date_default_timezone_get())) : null;
                if (!$dt) {
                    $array[$key]=null; 
                    continue;
                }
                if ($dt->format($sourceDf)!==$date) throw new \Exception("Date '$date' does not appear to be valid");
                
                $array[$key] = $dt->format("Y-m-d");
            }
        }
        
        static function convertDateFromDb(&$array,$keys=null) {
            if (!is_array($keys)) $keys = array($keys);
            
            $targetDf = "d/m/Y";
            
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
	
	static function getCsvArrayValues($key, $array, $singleAsString=false) {
		$csvValues = Util::val($key,$array);
		return Util::getCsvValues($csvValues,$singleAsString);
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
					if(isset($options[$key])) throw new \Exception("Key '$key' is a duplicate value in CSV list:".$csvValues);
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
       
	static function timestamp($ts=null) {
		return date("dmYHis",$ts);
	}
	
	static function requestMethod() {
		return Util::val('REQUEST_METHOD',$_SERVER);
	}

	static function makePlaceholders($params) {
		$count = is_array($params) ? count($params) : $params;
		$pholderList = array_fill(0, $count, "?");
		return implode(",", $pholderList);
	}
}
?>
