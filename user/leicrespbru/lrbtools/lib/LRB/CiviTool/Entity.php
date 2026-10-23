<?php
/*
 * Base class for CiviCRM entities and actions (which can be API-based or more complex e.g. word replacement)
 * used by the CiviTool
 */

require_once("LRB/Exception.php");

class LRB_CiviTool_Entity {
	const NAME="Name";
	const LABEL="Label";
	const TYPE="Type";
	const VALUE="Value";
	const PARENT="Parent";
	const STYLE="Style";
	const ACTION="Action";
	const PRETEXT="PreText";
	const OPTIONS="Options";
	const WEIGHT="Weight";
	const SETTINGS="Settings";
	
	private $options = array();
	
	/*
	 * Create action for entity
	 */
	function create() {
		throw new LRB_CiviToolException("create() not implemented");
	}
	
	/*
	 * Get action for entity
	*/
	function get() {
		throw new LRB_CiviToolException("get() not implemented");
	}
	
	/*
	 * Delete action for entity
	*/
	function delete() {
		throw new LRB_CiviToolException("delete() not implemented");
	}
	
	/*
	 * Count action for entity
	*/
	function count() {
		throw new LRB_CiviToolException("count() not implemented");
	}
	
	/*
	 * Set array map of options in config file for that entity
	 */
	function setOptions($options) {
		$this->options = $options;
	}
	
	/*
	 * Get specific option for entity or null if it doesn't exist
	 */
	function getOption($name,$default=null) {
		if (isset($this->options[$name]) && !empty($this->options[$name])) {
			return $this->options[$name];
		}
		return $default;
	}
	
	/*
	 * Get all options for entity
	*/
	function getOptions() {
		return $this->options;
	}
	
	/*
	 * Factory method which produce required Entity object based on the options provided
	 */
	static function factory($options) {
		
		#check type provided
		if (!isset($options[LRB_CiviTool_Entity::TYPE])) throw new LRB_CiviToolException("No 'Type' provided for entity factory");
		$type = $options[LRB_CiviTool_Entity::TYPE];
		
		$options[LRB_CiviTool_Entity::SETTINGS] = isset($options[LRB_CiviTool_Entity::SETTINGS]) ? 
			LRB_CiviTool_Entity::csvToArray($options[LRB_CiviTool_Entity::SETTINGS])
		:
		array();
		
		#convert underscore-based type to PascalCase
		if (strtolower($type) == $type) {
			$words = split("_",$type);
			$type = "";
			foreach($words as $word) {
				$type.=ucfirst($word);
			}
		}
		
		#load the module and populate the options
		$module_name = "LRB_CiviTool_Entity_$type";
		$module_path=str_replace("_","/",$module_name).".php";
		try {
			
		}
		catch(Exception $ex) {
			if (preg_match("/No such file or directory/",$ex->getMessage())) {
				throw new LRB_CiviToolException("Entity type:$type does not exist. The type should be specified lower-case separated_by_underscore OR CapitalisedWordsJoinedTogether");
			}
			else {
				throw new LRB_CiviToolException("Unknown problem creating Entity type:$type");
			}
		}
		$module = new $module_name();
		$module->setOptions($options);
		return $module;
	}
	
	/*
	 * Return the ID for the entity matching the options
	 */
	function getId() {
		$entity = $this->get();
		return $entity['id'];
	}
	
	function exists() {
		return $this->count()>0 ? true : false;
	}
	
	protected function getOptionValue($name, $optionGroupName, $useOgLabel=false) {
		require_once "api/v3/utils.php";
		require_once "api/v3/OptionValue.php";
		
		$params = array();
		
		$params['name']=$name;
		$params['version']=3;
		$params['sequential']=1;
		$params['option_group_id']=$this->getOptionGroupID($optionGroupName, $useOgLabel);
	
		$ent = civicrm_api3_option_value_get($params);

		if (isset($ent['error'])) {
			throw LRB_CiviToolException("There was an error (".$ent['error']." getting the ". get_class($this)." entity with options (".print_r($this->getOptions().")"));
		}
		if ($ent['count']>=1) {
			return $ent['values'][0];
		}
		else {
			return null;
		}
	}
	
	protected function getOptionGroupID($optionGroupName, $useOgLabel=false) {
		$params = array(
				LRB_CiviTool_Entity::TYPE => 'OptionGroup'
		);
	
		if ($useOgLabel) {
			$params[LRB_CiviTool_Entity::LABEL]=$optionGroupName;
		}
		else {
			$params[LRB_CiviTool_Entity::NAME]=$optionGroupName;
		}
		
		$og = LRB_CiviTool_Entity::factory($params);
		$ogValue = $og->get();
		return $ogValue['id'];
	}
	
	public static function labelToName($label) {
		return strtolower(str_replace(" ","_",$label));
	}
	
	public function nameToLabel($name) {
		return ucfirst(str_replace("_"," ",$name));
	}
	public function getLabelAndName() {
		$label = $this->getOption(LRB_CiviTool_Entity::LABEL);
		$name = $this->getOption(LRB_CiviTool_Entity::NAME);
		if (!$label && !$name) throw new LRB_CiviToolException("No 'Name' or 'Label' provided for entity:".print_r($this->getOptions(),1));
		if (!$label && $name) $label = LRB_CiviTool_Entity::nameToLabel($name);
		if (!$name && $label) $name = LRB_CiviTool_Entity::labelToName($label);
		return array($label,$name);
	}
	
	static function csvToArray($csvValues) {
		if (is_array($csvValues)) return $csvValues;
		if (!isset($csvValues) || empty($csvValues)) return null;
		#$optionsList = preg_replace("/\r|\n/",",",$csvValues);
		$optionItems = preg_split("/\n|,/",$csvValues);
			
		#then split each options on = to get the key and title
		$options = array();
		foreach($optionItems as $item) {
			$temp=explode("=",$item);
			if(count($temp)==2) {
				$matches = array();
				if (preg_match("/(.+)\[(\d+)]/",$temp[0],$matches)) {
					$key = $matches[1];
					$index = $matches[2];
					if (!isset($options[$key])) $options[$key]=array();
					$options[$key][$index]=$temp[1];
				}
				else {
					$options[$temp[0]]=$temp[1];
				}
			}
			else {
				$options[$item]=$item;
			}
		}
		
		if (count($options)==1) {
			$keys = array_keys($options);
			$vals = array_values($options);
			if ($keys == $vals) $options = array_shift($keys);
		}
		return $options;
	}
}