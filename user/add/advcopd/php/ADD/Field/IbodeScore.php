<?php

class ADD_Field_IbodeScore extends ADD_Field_Text_Number {
	const VERSION="1.00";
	const DATE="12/08/2013";
	function finaliseField($page) {
		parent::finaliseField($page);
		$this->setSetting('viewonly',1);
	}
	function afterPopulateFields($page) {
		$exercise = ADD_Field_Util::getSiblingObs($page,'exercise',array('pc_pred_fev1','iswt','mrc_grade'),"calculate the iBODE score");
		
		$expulmdis =ADD_Field_Util::getSiblingObs($page,'expulmdis',array('bmi'),"calculate the iBODE score");
		
		if (!$exercise) return;
		if (!$expulmdis) return;
		
		$currValue = $this->getValue();
		
		list($fev1,$iswt,$mrc_grade)  = $exercise;
		
		list($bmi) = $expulmdis;
		$newValue = null;
		if ($fev1>=65 && $iswt>=250 && ($mrc_grade==1 || $mrc_grade==2) && $bmi>21) {
                    $newValue = 0;
		}
		if (($fev1>=50 && $fev1<=64) && ($iswt>=150 && $iswt<=249) && $mrc_grade==3 & $bmi<=21) {
                    $newValue = 1;
		}
		if (($fev1>=36 && $fev1<=49) && ($iswt>=80 && $iswt<=149) && $mrc_grade==4) {
                    $newValue = 2;
		}
		if ($fev1<=35 && $iswt<80 && $mrc_grade==5) {
                    $newValue = 3;
		}
		if ($newValue!=$currValue) {
			$page->addMessage("iBODE score has changed from '$currValue' to '$newValue'");
      		}
                if ($newValue===null) {
                    $page->addWarning("Patient cannot be assigned to an iBODE index. Their measurements are not within the required ranges.");
                    $newValue="N/A";
                }

        $this->setValue($newValue);
	}
	
	
}
?>