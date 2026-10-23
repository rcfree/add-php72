<?php
namespace EMBER\Calculate;
use ClinicLE\Common\Util;

class DECAFScore {
	private $atrialFibValue=2;
	private $consolValue="Y";
	const EOS_THRESHOLD=0.05;
	const ACID_THRESHOLD=7.3;
	
	public function setAtrialFibValue($atrialFibValue) {
		$this->atrialFibValue = $atrialFibValue;
	}
	
	public function determineScore($emrc, $eosCount, $consolidation, $pH, $rhythm) {
		$score = 0;
		if(Util::isEmpty($emrc)) {
			return null;
		}
		if(Util::isEmpty($eosCount)) {
			return null;
		}
		if(Util::isEmpty($consolidation)) {
			return null;
		}
		if(Util::isEmpty($rhythm)) {
			return null;
		}
		
		if($emrc=="5A") {
            $score += 1;
        }

		if($emrc=="5B") {
            $score+=2;
		}
		
		if(floatval($eosCount)<self::EOS_THRESHOLD) {
			$score++;
		}
		if($consolidation==$this->consolValue) {
			$score++;
		}
		if($pH && floatval($pH)<self::ACID_THRESHOLD) {
			$score++;
		}
		if($rhythm==$this->atrialFibValue) {
			$score++;
		}
		return $score;
	}
}
