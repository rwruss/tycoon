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
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');


$thisCity = loadCity($postVals[1], $cityFile);
/* off for testing
$cityTaxEx = new itemSlot($thisCity->get('cTax'), $slotFile, 40);
$regionTaxEx = new itemSlot($thisCity->get('rTax'), $slotFile, 40);
$nationTaxEx = new itemSlot($thisCity->get('nTax'), $slotFile, 40);

$cityLaws = new itemSlot($thisCity->get('cLaw'), $slotFile, 40);
$regionLaws = new itemSlot($thisCity->get('rLaw'), $slotFile, 40);
$nationLaws = new itemSlot($thisCity->get('nLaw'), $slotFile, 40);

// basic city info
echo $postVals[1].',"City Name"'.implode(',', array_slice($thisCity->objDat, 11, 25)).','.implode(',', array_slice($thisCity->objDat, 50, 40)).','.implode(',', array_slice($thisCity->objDat, 120, 20)).';';

//city laws
echo implode(',', $cityLaws->slotData).';';

// city tax exemptions
echo implode(',', $cityTaxEx->slotData).';';
*/

echo $postVals[1].',"City Name"'.implode(',', array_slice($thisCity->objDat, 11, 25)).','.implode(',', array_slice($thisCity->objDat, 50, 40)).','.implode(',', array_slice($thisCity->objDat, 120, 20)).';';
echo ';';
echo '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,1,2,102,500,"Some COmpany"';
fclose($cityFile);

?>
