<?php
namespace EMBER\Calculate;
use ClinicLE\Common\Util;

class CURB65Score {

	public function determineScore($confusion, $bloodUrea,$respiratoryRate,$sbp,$dbp,$dateOfVisit,$dateOfBirth) {
		$score = 0;
		if(Util::isEmpty($confusion)) {
			return null;
		}
		if(Util::isEmpty($bloodUrea)) {
			return null;
		}
		if(Util::isEmpty($respiratoryRate)) {
			return null;
		}
		if(Util::isEmpty($sbp) && Util::isEmpty($dbp)) {
			return null;
		}

		if($confusion=="Y") {
		    $score++;
        }
        if($bloodUrea>7) {
		    $score++;
        }
        if($sbp < 90 || $dbp <= 60) {
            $score++;
        }
        if($respiratoryRate>=30) {
		    $score++;
        }
        $age = \ADD_Field_Util::age($dateOfBirth,$dateOfVisit);

        if($age>=65) {
		    $score++;
        }
        return $score;
	}
}