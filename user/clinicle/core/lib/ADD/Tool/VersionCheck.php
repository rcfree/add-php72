<?php

class ADD_Tool_VersionCheck {
	const RANGE="range";
	const NEWER="newer";
	const OLDER="older";
	const EQUAL="equal";
	const NEWER_OR_EQUAL="newer_or_equal";
	const OLDER_OR_EQUAL="older_or_equal";
	
	private $_requiredVersion;
	function __construct($requiredVersion) {
		$this->_requiredVersion=$requiredVersion;
	}
	function _versionSegments($version) {
		$temp=explode(".",$version);
		if(count($temp)<2) throw new Exception("Version '$version' must have at least 2 segments");
		if (count($temp)==2) {
			$temp[]=0;
		}
		return $temp;
	}
	function _isWithinRange($rangeStart,$rangeStop,$currentVersion) {
		list($currMajor,$currMinor,$currPatch)=$this->_versionSegments($currentVersion);
		list($reqMajorStart,$reqMinorStart,$reqPatchStart)=$this->_versionSegments($rangeStart);
		list($reqMajorStop,$reqMinorStop,$reqPatchStop)=$this->_versionSegments($rangeStop);
		
		if($currMajor<$reqMajorStart || $currMajor>$reqMajorStop) return false;
		if($currMinor<$reqMinorStart || $currMinor>$reqMinorStop) return false;
		if($currPatch<$reqPatchStart || $currPatch>$reqPatchStop) return false;
		return true;
	}
	function _isEqual($requiredVersion,$currentVersion) {
		list($currMajor,$currMinor,$currPatch)=$this->_versionSegments($currentVersion);
		list($reqMajor,$reqMinor,$reqPatch)=$this->_versionSegments($requiredVersion);
		if($reqMajor!=$currMajor && $reqMajor!="x") return false;
		if($reqMinor!=$currMinor && $reqMinor!="x") return false;
		if($reqPatch!=$currPatch && $reqPatch!="x") return false;
		return true;
	}
	function _isNewer($requiredVersion,$currentVersion) {
		list($currMajor,$currMinor,$currPatch)=$this->_versionSegments($currentVersion);
		list($reqMajor,$reqMinor,$reqPatch)=$this->_versionSegments($requiredVersion);
		
		if($currMajor<$reqMajor) return false;
		if($currMajor==$reqMajor && $currMinor<$reqMinor) return false;
		if($currMinor<$reqMinor && $currMajor<=$reqMajor) return false;
		if($currPatch<=$reqPatch  && $currMinor<=$reqMinor  && $currMajor<=$reqMajor) return false;
		return true;
	}
	function _isOlder($requiredVersion,$currentVersion) {
		list($currMajor,$currMinor,$currPatch)=$this->_versionSegments($currentVersion);
		list($reqMajor,$reqMinor,$reqPatch)=$this->_versionSegments($requiredVersion);
		
		if($currMajor>$reqMajor) return false;
		if($currMajor==$reqMajor && $currMinor>$reqMinor) return false;
		if($currMinor>$reqMinor && $currMajor>=$reqMajor) return false;
		if($currPatch>=$reqPatch  && $currMinor>=$reqMinor  && $currMajor>=$reqMajor) return false;
		return true;
	}
	function _isNewerOrEqual($requiredVersion,$currentVersion) {
		if ($this->_isEqual($requiredVersion,$currentVersion)) return true;
		return ($this->_isNewer($requiredVersion,$currentVersion));
	}
	function _isOlderOrEqual($requiredVersion,$currentVersion) {
		if ($this->_isEqual($requiredVersion,$currentVersion)) return true;
		return ($this->_isOlder($requiredVersion,$currentVersion));
	}
	function isCompatibleWith($currentVersion) {
		list($type,$requiredVersions) = $this->_getVersionRange();
		switch($type) {
			case self::RANGE:
				return $this->_isWithinRange($requiredVersions[0],$requiredVersions[1],$currentVersion);
				
			case self::EQUAL:
				return $this->_isEqual($requiredVersions[0],$currentVersion);
				
			case self::NEWER:
				return $this->_isNewer($requiredVersions[0],$currentVersion);
				
			case self::NEWER_OR_EQUAL:
				return $this->_isNewerOrEqual($requiredVersions[0],$currentVersion);
				
			case self::OLDER_OR_EQUAL:
				return $this->_isOlderOrEqual($requiredVersions[0],$currentVersion);
				
		}
	}
	function _getVersionRange() {
		$range = explode("-",$this->_requiredVersion);
		if(count($range)==2) return array(self::RANGE,$range);
		if(substr($this->_requiredVersion,0,2)==">=") {
			return array(self::NEWER_OR_EQUAL,array(substr($this->_requiredVersion,2)));
		}
		if(substr($this->_requiredVersion,0,2)=="<=") {
			return array(self::OLDER_OR_EQUAL,array(substr($this->_requiredVersion,2)));
		}
		if(substr($this->_requiredVersion,0,1)==">") {
			return array(self::NEWER,array(substr($this->_requiredVersion,1)));
		}
		if(substr($this->_requiredVersion,0,1)=="<") {
			return array(self::OLDER,array(substr($this->_requiredVersion,1)));
		}
		return array(self::EQUAL,array($this->_requiredVersion));
	}
}
