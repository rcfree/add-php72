<?php

class ADD_Page_Api_DictItem extends ADD_Page_Api {
	function apiOutput() {
		$add = ADD::sys();
		
		$db = ADD::db('dictionary');
		$edb = ADD::db('encounter');
		list($action, $dictName) = $add -> assertParams(array('action', 'dict_name'));
		$id = $add -> getParam('id');

		if ($action == 'edit') {
			$di = $db -> getDictionaryItem($id);
			$data = $di;
			if (ADD_Util::val("dictionary_group_id", $di)) {
				$dg = $db -> getDictionaryGroup(ADD_Util::val("dictionary_group_id", $di));
				$data['group'] = $dg['name'];
			} else {
				$data['group'] = "";
			}
			$oqCount = $db -> countDictionaryObservations($dictName, $di);
			if ($oqCount > 0)
				$data['__used'] = 1;
			$data['__id'] = $id;
			if (isset($di['data'])) {
				$diData = ADD_Util::unserialize($di['data']);
				unset($data['data']);
				if ($diData){
					//$data['data']=$diData;
					$data = $data + $diData;
				}
			}
		} elseif ($action == 'delete') {
			$di = $db -> getDictionaryItem($id);
			if ($di) {
				$oqCount = $db -> countDictionaryObservations($dictName, $di);
				if ($oqCount > 0)
					return;

				$db -> deleteDictionaryItem($id);
			}
		} else {
			$data['group'] = "";
			$data['value'] = "";
			$data['title'] = "";
			$data['group_label'] = "";
			$data['new_group'] = 0;
		}

		$data['__action'] = $action;
		$data['new_group'] = 0;
		return $data;
	}

}
