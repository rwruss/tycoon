<?php

//transportClass.php

class routeObj {
	public $id, $stops, $stopsDist, $objDat, $attrList;

	function __construct($id, $dat) {
		$this->id = $id;
		$this->objDat = unpack('s*', substr($dat, 4, 20);
		$this->stops = unpack('s*', substr($dat, 56, 20));
		$this->stopsDist = unpack('s*', substr($dat, 76, 20);
		
		$this->attrList['owner'] = 1;
		$this->attrList['mode'] = 2;
		$this->attrList['speed'] = 3;
		$this->attrList['spaceCost'] = 4;
		$this->attrList['weightCost'] = 5;
		
		$this->attrList['spaceCap'] = 6;
		$this->attrList['weightCap'] = 7;
		$this->attrList['status'] = 8;
		$this->attrList['runFreq'] = 9;
		$this->attrList['vehicle'] = 9;
	}
	
	function get($desc) {
		if (array_key_exists($desc, $this->attrList)) {
			return $this->objDat[$this->attrList[$desc]];
		} else {
			echo 'DESC: "'.$desc.'" Not found in type '.$this->objDat[4].' ('.$desc.')';
			return false;
		}
	}
}

?>