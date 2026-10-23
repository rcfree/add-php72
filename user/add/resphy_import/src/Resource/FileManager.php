<?php
namespace ADD\ResphyImport\Resource;
use ADD\ResphyImport\Exception\SystemCommandException;

class FileManager {
	function move($srcPath, $tgtPath) {
		$returnVal = 0;
		$srcPath = str_replace(' ','\ ',$srcPath);
		$cmd = "mv ".addslashes($srcPath)." $tgtPath 2>/dev/null";
		
		exec($cmd,$output, $returnVal);
		if($returnVal!=0) {
			throw new SystemCommandException("Unable to move $srcPath to $tgtPath");
		}
	}
	function isReadable($file) {
	    return is_readable($file);
    }
}