<?php

/*
Return information about a city for output in a city detail.

PVS
1 - city ID

this.objID = objDat[0];
		this.objName = objDat[1];
		this.details = objDat;
		this.demandRates = "";
		this.demandLevels = "";
		this.rTax = objDat[14];
		this.nTax = objDat[15];
		this.leader = objDat[16];
		this.townDemo = new Array(10, 20, 30, 40, 50, 60, 70, 80, 90, 100);
		this.leaderDemo = new Array(-10, -20, -30, -40, -50, -60, -70, -80, -90, -100);
*/

require_once('./slotFunctions.php');
require_once('./objectClass.php');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

$cityTaxEx = new itemSlot($cityFile->get('cityTax'), $slotFile, 40);
$regionTaxEx = new itemSlot($cityFile->get('regionTax'), $slotFile, 40);
$nationTaxEx = new itemSlot($cityFile->get('nationTax'), $slotFile, 40);

$cityLaws = new itemSlot($cityFile->get('cityLaw'), $slotFile, 40);
$regionLaws = new itemSlot($cityFile->get('regionLaw'), $slotFile, 40);
$nationLaws = new itemSlot($cityFile->get('nationLaw'), $slotFile, 40);

$thisCity = loadCity($postVals[1], $cityFile);

// basic city info
echo $postVals[1].',"City Name"'.implode(',', array_slice($thisCity->objDat, 11, 25)).','.implode(',', array_slice($thisCity->objDat, 50, 40)).','.implode(',', array_slice($thisCity->objDat, 120, 20)).';';

//city laws
echo implode(',', $cityLaws->slotData).';';

// city tax exemptions
echo implode(',', $cityTaxEx->slotData).';';

fclose($cityFile);

?>