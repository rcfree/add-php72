<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MockEncounterManager
 *
 * @author rcf8
 */
class ADD_Test_MockEncounterManager extends PHPUnit_Framework_TestCase {
    private $_test = null;
    private $_internal = array();
	public function testIsTrue() {
		$this->assertTrue(true);
	}
    private function _expectedOption($function, $encType, $default=null,$parent=null) {
        $callers=debug_backtrace();
        $class = isset($callers[1]) ? $class = $callers[count($callers)-12]['function']." in ".$callers[2]['class']."::".$callers[2]['function']." (".ADD_Util::val("line",$callers[2]).")" : "";
        $value = null;
        if ($parent) $parent = spl_object_hash($parent);
		if ($parent) {

            if (isset($this->_internal[$function][$encType][$parent])) {
                $value = $this->_internal[$function][$encType][$parent];
                ADD::log()->debug("Get expected func:$function, parent:$parent, encType:$encType, $class");
            }
        }
        if (!$value) {
            if (isset($this->_internal[$function][$encType]['NONE'])) {
                $value = $this->_internal[$function][$encType]['NONE'];
                ADD::log()->debug("Get expected func:$function, no parent, encType:$encType, $class");
            }
            else{
				ADD::log()->debug("Get default func:$function, parent: $parent, encType:$encType, $class");
                $value = $default;
            }
        }
        return $value;
    }
    public function record($encType,$patient) {
        return $this->_expectedOption('record',$encType,$this->getMock('ADD_EncounterRecord'));
    }
	public function recordById($id) {
		return $this->_expectedOption('recordById',$id,$this->getMock('ADD_EncounterRecord'));
	}
    public function newChild(ADD_EncounterRecord $per,$encType) {
        return $this->_expectedOption('newChild',$encType,$this->getMock('ADD_EncounterRecord'),$per);
    }

    public function child(ADD_EncounterRecord $per, $encType, $idValue=null) {
        //if ($type=="qol-hads") throw new Exception();
        return $this->_expectedOption('child',$encType, $this->getMock('ADD_EncounterRecord'),$per);
    }

    public function children(ADD_EncounterRecord $per, $encType, $id=NULL) {
        return $this->_expectedOption('children', $encType, array(),$per);
    }

    public function setExpected($encType,$function, $value, $parent=null) {
        if (is_object($encType)) $encType = $encType->getSetting('encounter');
        if (!isset($this->_internal[$function])) $this->_internal[$function]=array();
        ADD::log()->debug("Set expected $encType,$function,". ($parent ? "parent:".spl_object_hash($parent) : "no parent"));
		if ($parent) {
            $parent = spl_object_hash($parent);
            ADD::log()->debug("setExpected func:$function, parent:$parent. enc:$encType");
            $this->_internal[$function][$encType][$parent]=$value;
        }
        else {
            ADD::log()->debug("setExpected func:$function, no parent, enc:$encType");

            $this->_internal[$function][$encType]['NONE']=$value;
        }
    }
}
