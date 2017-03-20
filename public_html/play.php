<?php

$namesList = explode(',', file_get_contents('../scenarios/1/objNames.dat'));
$laborNameList = file_get_contents('../scenarios/1/laborNames.dat');
$numProducts = 38;
$numFactories = 8;

require_once('./slotFunctions.php');
require_once('./objectClass.php');

session_start();
if (!isset($_SESSION["playerId"])) echo "<script>window.location.replace(./index.php)</script>";
if (!isset($_GET["gameID"])) echo "<script>window.location.replace(./index.php)</script>";

// Read game file to determine player number and status.
//print_r($_SESSION);
//echo 'Look for player '.$_SESSION["playerId"].'in game <p>';
$playerList = unpack("i*", file_get_contents("../games/".$_GET["gameID"]."/players.dat"));
//print_r($playerList);
$playerListLoc = array_search($_SESSION["playerId"], $playerList);
$pGameID = $playerList[$playerListLoc+1]*-1;
$_SESSION["instance"] = $_GET["gameID"];

//echo 'found as player '.$pGameID;

if ($pGameID < 0) {
	echo "<p><p><p><p>Not alrady in game(".$_SESSION["playerId"].")";
	//print_r($playerList);
	include("../gameScripts/1001.php");
	exit;
}

$_SESSION["gameIDs"][$_GET["gameID"]] = $pGameID;

$gamePath = "../games/".$_GET["gameID"];
$gameID = $_GET["gameID"];
// Read game parameters
$paramDat = file_get_contents($gamePath."/params.ini");
//$mapBounds = unpack("S*", substr($paramDat, 100, 8));
$gameTimes = unpack("N*", substr($paramDat, 0, 8));

$paramFile = fopen('../games/'.$gameID.'/params.ini', 'rb');
$params = unpack('i*', fread($paramFile, 100));
$_SESSION['game_'.$gameID]['scenario'] = $params[9];
$_SESSION['game_'.$gameID]['scenario'] = 1;
$_SESSION['game_'.$gameID]['teamID'] = 1; // Set and record player culture
fclose($paramFile);

$gamePath = "../games/".$gameID;
$scnPath = "../scenarios/".$_SESSION['game_'.$gameID]['scenario'];
// Read player info
$defaultBlockSize = 100;
$unitFile = fopen($gamePath."/objects.dat", "rb");
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');

//echo 'Load player '.$pGameID;
$thisPlayer = loadObject($pGameID, $unitFile, 400);
$_SESSION['game_'.$gameID]['business'] = $thisPlayer->objDat;

// Load player factories
$factoryList = [];

if ($thisPlayer->get('ownedObjects') > 0) {
	$ownedObjects = new itemSlot($thisPlayer->get('ownedObjects'), $slotFile, 40);

	for ($i=1; $i<sizeof($ownedObjects->slotData); $i++) {
		if ($ownedObjects->slotData[$i] > 0) {
			//echo 'Object '.$ownedObjects->slotData[$i].'<br>';
			$thisObject = loadObject($ownedObjects->slotData[$i], $unitFile, 400);
			if ($thisObject->get('oType') == 3) {
				array_push($factoryList, $thisObject->get('subType'), $thisObject->get('currentProd'), $thisObject->get('prodRate'), $ownedObjects->slotData[$i]);
			}
			else echo 'Cull object type '.$thisObject->get('oType');
		}
	}
}
//print_r($factoryList);
// Load company labor
echo 'Labor in slot '.$thisPlayer->get('laborSlot');
$companyLabor = [];
$laborSlot = new itemSlot($thisPlayer->get('laborSlot'), $slotFile, 40);
//print_r($laborSlot->slotData);
$laborCount = 0;
for ($i=1; $i<sizeof($laborSlot->slotData); $i+=10) {
	$companyLabor[] = $laborCount;
	$companyLabor = array_merge($companyLabor, array_slice($laborSlot->slotData, $i-1, 10));
	//$companyLabor = $laborSlot->slotData;
	$laborCount++;
}
//print_r($companyLabor);
echo '
<link rel="stylesheet" type="text/css" href="gameStyles.css">
<script type="text/javascript" src="glMatrix-0.9.5.min.js"></script>
<script type="text/javascript" src="webgl-utils.js"></script>
<script type="text/javascript" src="templates.js"></script>
<script type="text/javascript" src="selectList.js"></script>
<script type="text/javascript" src="tycoonObjects.js"></script>

<script id="shader-fs" type="x-shader/x-fragment">
</script>

<script id="shader-vs" type="x-shader/x-vertex">
</script>

<script type="text/javascript">
	var companyLabor = new Array();
	var factoryLabor  = new Array();
	var factoryList, playerFactories, factoryDiv, tmpLabor, infoPane;
	var playerUnits;
	var moveString = new Array();

	var taxTypes = ["None", "city income tax", "city property tax", "city VAT", "city personal income tax", "city polltion tax", "city rights tax",
		"regional income tax", "regional property tax", "regional VAT", "regional personal income tax", "regional polltion tax", "regional rights tax",
		"national income tax", "national property tax", "national VAT", "national personal income tax", "national polltion tax", "national rights tax"];

	var selectedFactory;
	var buildTimeBox;
	var boostTarget;
	var factoryProductionBox;
	var factoryUpgradeBox;
	var factoryOrders = new Array();
	var fProductionBox;
	var orderPane;
	var businessDiv;
	var nationList;

	var unitBox;
	var rY = 0.0;
	var rotShift = [0,0];
	var testXShift = [-30, -20, -10, 0, 10, 20,
						-30, -20, -10, 0, 10, 20,
						-30, -20, -10, 0, 10, 20,
						-30, -20, -10, 0, 10, 20,
						-30, -20, -10, 0, 10, 20,
						-30, -20, -10, 0, 10, 20,
						-30, -20, -10, 0, 10, 20,
						-30, -20, -10, 0, 10, 20,
					  ];
	var testZShift = [-30, -30, -30, -30, -30, -30,
					-20, -20, -20, -20, -20, -20,
					-10, -10, -10, -10, -10, -10,
					0, 0, 0, 0, 0, 0,
					10, 10, 10, 10, 10, 10,
					20, 20, 20, 20, 20, 20];
	var drawOrder = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35];
	var zoomRot = [0, 3, 2, 0, 1, 0, 0, 0, 0];

	var lastTime = 0;
	var wY = 0;
	var xSpeed = 0;
	var zSpeed = 0;
	var viewAngle;
	var currentlyPressedKeys = {};

	var tileCanvas;
	var ctx;

	//* Div vars	*//
	var orderItems;
	var reqBox;

	var drawList = [];
	drawList[0] = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35];
	drawList[1] = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35];
	drawList[2] = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35];
	drawList[3] = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35];
	drawList[4] = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35];
	drawList[5] = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35];
	drawList[6] = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35];
	drawList[7] = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35];
	drawList[8] = drawList[0];
	var clickParams = [];
	var clickTarg = "";

	var genCharList = [];
	var thisPlayer;
	var numProducts = '.$numProducts.';
	var numFactories = '.$numFactories.';


	function ncode_div(el_id) {
				if (typeof(el_id) == "string") trg = document.getElementById(el_id);
				else trg = el_id;
         var x = trg.getElementsByTagName("script");
         for(var i=0;i<x.length;i++) {
                 eval(x[i].text);
							   }
         }

	function ncode_general(data) {
         var x = data.getElementsByTagName("script");
         for(var i=0;i<x.length;i++) {
                 eval(x[i].text);

                 }
         }


	function groupSelect(selNum) {
		dupe = false;
		for (i=0; i<groupList.length; i++) {
			if (groupList[i] == selNum) {
				dupe = true;
				groupList.splice(i, 1);
				document.getElementById("selOpt_"+selNum).className="unselected";
				break;
			}
		}
		if (!dupe) {
			groupList.push(selNum);
			document.getElementById("selOpt_"+selNum).className="selected";
		}
	}

	function passClick(val, trg) {
		params = "val1="+val;
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.open("POST", "gameScr.php?gid='.$_GET['gameID'].'", true);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

		xmlhttp.onreadystatechange = function() {
			if (typeof(trg) == "string") target = document.getElementById(trg);
			else target = trg;

			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

				target.innerHTML = xmlhttp.response;

				ncode_div(target);

				}
			}

		xmlhttp.send(params);
		}

	function returnInfo(val) {
		params = "val1="+val;
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.open("POST", "gameScr.php?gid='.$_GET['gameID'].'", true);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				return xmlhttp.response;
				}
			}

		xmlhttp.send(params);
	}

	function scrMod(val) {
		params = "val1="+val;
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.open("POST", "gameScr.php?gid='.$_GET['gameID'].'", true);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				document.getElementById("scrBox").innerHTML = xmlhttp.response;
				ncode_div("scrBox");
				}
			}

		xmlhttp.send(params);
		}

	function loadData(val, callback) {
		console.log("loadting data");
		params = "val1="+val;
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.open("POST", "gameScr.php?gid='.$_GET['gameID'].'", true);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				console.log(xmlhttp.response);
				callback(xmlhttp.response);
				}
			}

		xmlhttp.send(params);
	}


	function makeBox(bName, val, h, w, x, y) {
		console.log(arguments);
		e = window.event || arguments[0];
		useDeskTop.newPane(bName);
		passClick(val, useDeskTop.getPane(bName));
		}

	function closeBox() {
		this.parentNode.remove();
	}

	function killBox(trg) {
		console.log(trg + " name is " + trg.nodeName)
		if (trg.nodeName == "DIV") {
			testNode = trg;

		} else {

			testNode = this;
		}
		while (testNode.parentNode.nodeName != "BODY") {
			testNode = testNode.parentNode;
			if (testNode.parentNode.parentObj) {
				console.log(testNode.parentNode.parentObj + " found");
				break;
			}
		}
		//testNode.remove();
		console.log("destroying " + testNode.parentNode.parentObj.nodeType + "  via " + testNode);
		testNode.parentNode.parentObj.destroyWindow();
	}

	function getData(rTrg, prm, tTrg) {
		var tot_length = 0;
		params = "val1="+prm.join();
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.open("POST", rTrg, true);

		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

		xmlhttp.responseType = "arraybuffer";

		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

				loadRivers(prm, xmlhttp.response, tTrg);
				}
			}
		xmlhttp.send(params);
		}

	function handleMapTextures(texture, x, y, tileNum) {
		}

	var  baseMap;

	function tileSwitch() {
		if (switchOption == 0) {
			locTr[0] += 10;
			tmp = [drawOrder[5], drawOrder[0], drawOrder[1], drawOrder[2], drawOrder[3], drawOrder[4],
						drawOrder[11], drawOrder[6], drawOrder[7], drawOrder[8], drawOrder[9], drawOrder[10],
						drawOrder[17], drawOrder[12], drawOrder[13], drawOrder[14], drawOrder[15], drawOrder[16],
						drawOrder[23], drawOrder[18], drawOrder[19], drawOrder[20], drawOrder[21], drawOrder[22],
						drawOrder[29], drawOrder[24], drawOrder[25], drawOrder[26], drawOrder[27], drawOrder[28],
						drawOrder[35], drawOrder[30], drawOrder[31], drawOrder[32], drawOrder[33], drawOrder[34]]
					drawOrder = tmp;
			loadTiles();
			}
		else if (switchOption == 1) {
			locTr[0] -= 10;
			tmp = [drawOrder[1], drawOrder[2], drawOrder[3], drawOrder[4], drawOrder[5], drawOrder[0],
						drawOrder[7], drawOrder[8], drawOrder[9], drawOrder[10], drawOrder[11], drawOrder[6],
						drawOrder[13], drawOrder[14], drawOrder[15], drawOrder[16], drawOrder[17], drawOrder[12],
						drawOrder[19], drawOrder[20], drawOrder[21], drawOrder[22], drawOrder[23], drawOrder[18],
						drawOrder[25], drawOrder[26], drawOrder[27], drawOrder[28], drawOrder[29], drawOrder[24],
						drawOrder[31], drawOrder[32], drawOrder[33], drawOrder[34], drawOrder[35], drawOrder[30]]
				drawOrder = tmp;
			loadTiles();
			}
		else if (switchOption == 2) {
			locTr[1] +=10;
			tmp = [drawOrder[30], drawOrder[31], drawOrder[32], drawOrder[33], drawOrder[34], drawOrder[35],
						drawOrder[0], drawOrder[1], drawOrder[2], drawOrder[3], drawOrder[4], drawOrder[5],
						drawOrder[6], drawOrder[7], drawOrder[8], drawOrder[9], drawOrder[10], drawOrder[11],
						drawOrder[12], drawOrder[13], drawOrder[14], drawOrder[15], drawOrder[16], drawOrder[17],
						drawOrder[18], drawOrder[19], drawOrder[20], drawOrder[21], drawOrder[22], drawOrder[23],
						drawOrder[24], drawOrder[25], drawOrder[26], drawOrder[27], drawOrder[28], drawOrder[29]]
				drawOrder = tmp;
			loadTiles();
			}
		else if (switchOption == 3) {
			locTr[1] -=10;
			tmp = [drawOrder[6], drawOrder[7], drawOrder[8], drawOrder[9], drawOrder[10], drawOrder[11],
						drawOrder[12], drawOrder[13], drawOrder[14], drawOrder[15], drawOrder[16], drawOrder[17],
						drawOrder[18], drawOrder[19], drawOrder[20], drawOrder[21], drawOrder[22], drawOrder[23],
						drawOrder[24], drawOrder[25], drawOrder[26], drawOrder[27], drawOrder[28], drawOrder[29],
						drawOrder[30], drawOrder[31], drawOrder[32], drawOrder[33], drawOrder[34], drawOrder[35],
						drawOrder[0], drawOrder[1], drawOrder[2], drawOrder[3], drawOrder[4], drawOrder[5]]
				drawOrder = tmp;
			loadTiles();
			}
		else if (switchOption == 4) {
			loadTiles();
			baseOffset[0] = (baseMap[0]-baseTile[0]*120*zoomLvl/2)/(12*zoomLvl/2)
			baseOffset[1] = -(baseTile[1]*120*zoomLvl/2-baseMap[1])/(12*zoomLvl/2);
			mapScale /= 2.0;
			zoomLvl /= 2;
			locTr[0] = 0;
			locTr[1] = 0;
			document.getElementById("baseOff").value = baseOffset[0]+", "+baseOffset[1];
			}
		else if (switchOption == 5) {
			loadTiles();
			baseOffset[0] = (baseMap[0]-baseTile[0]*120*zoomLvl*2)/(12*zoomLvl*2)
			baseOffset[1] = -(baseTile[1]*120*zoomLvl*2-baseMap[1])/(12*zoomLvl*2);
			mapScale *= 2.0;
			zoomLvl *= 2;
			locTr[0] = 0;
			locTr[1] = 0;
			document.getElementById("baseOff").value = baseOffset[0]+", "+baseOffset[1];
			}
		else if (switchOption == 6) loadTiles();
		}

	function checkLoad() {
		loaded++;
		//alert(num + ": " + src);
		document.getElementById("loadedQty").value = "fok u"
		if (loaded >= loadTarg) loadTiles(x, y, z);
		}

	function initTiles(x, y, z, initList, trgList) {
		}

	function loadTiles() {
		}

	function setClick(params, style, trg) {
	}

	function findPos(obj) {
		var curleft = curtop = 0;
		if (obj.offsetParent) {
			do {
			curleft += obj.offsetLeft;
			curtop += obj.offsetTop;
			} while (obj = obj.offsetParent);
			return [curleft,curtop];
			}
		}



	function showDiagnostics() {
		if (document.getElementById("diagCB").checked) document.getElementById("diagBox").style.width = "300";
		else  document.getElementById("diagBox").style.width = 0;
	}

	function sendValue(src, dst) {
		//alert("source has a value of " + document.getElementById(src).value);
		dst = dst + ","+document.getElementById(src).value;
		alert(dst);
		makeBox("someBox", dst, 500, 500, 200, 50);
	}

	function getDescription(trg, info, src) {
		info = info  + ","+document.getElementById(src).value;
		passClick(info, trg);
	}

	function initGame() {
		thisPlayer = new gamePlayer(['.$thisPlayer->get('money').', '.$_SESSION['gold'].']);
		thisPlayer.setBoosts('.implode(',', $_SESSION['boosts']).');
		useDeskTop = new deskTop;

		objNames = ['.implode(',', array_slice($namesList, 0, $numProducts)).'];
		factoryNames = ['.implode(',', array_slice($namesList, $numProducts)).'];
		laborNames = ['.$laborNameList.'];
		schoolList = new Array();
		schoolList[0] = new school([1, "Primary Schools"], [1,2,3]);
		schoolList[1] = new school([2, "Trade School"], [1,2,3]);
		schoolList[2] = new school([3, "Community College"], [1,2,3]);
		schoolList[3] = new school([4, "University"], [1,2,3]);
		schoolList[4] = new school([5, "Technical University"], [1,2,3]);
		schoolList[5] = new school([6, "Business School"], [1,2,3]);
		console.log(laborNames);
		console.log(objNames);
		console.log(factoryNames);

		playerFactories = new Array(';
		if (sizeof($factoryList) > 0) echo 'new factory({subType:'.($factoryList[0]-$numProducts).', prod:'.$factoryList[1].', rate:'.$factoryList[2].', objID:'.$factoryList[3].'})';
		for ($i=4; $i<sizeof($factoryList); $i+=4) {
			echo ', new factory({subType:'.($factoryList[$i]-$numProducts).', prod:'.$factoryList[$i+1].', rate:'.$factoryList[$i+2].', objID:'.$factoryList[$i+3].'})';
		}
		echo ');
		console.log(playerFactories);

		serviceArray = new Array();


		productArray = new Array();
		for (var i=0; i<numProducts; i++) {
			productArray.push(new product({objType:product, objID:(i), objName:objNames[i]}));
		}

		serviceArray = new Array();
		for (var i=0; i<20; i++) {
			serviceArray.push(new product({objType:service, objID:(i), objName:"Service"+i}));
		}

		laborArray = new Array();
		descSwith = ["dumbasses", "not dumbasses"];
		for (var i=0; i<100; i++) {
			laborArray.push(new labor({objType:product, objID:(i), objName:"labor " + i, edClass:(descSwith[i%2]), laborType:(i)}));
		}

		factoryList = new Array();
		for (var i=0; i<numFactories; i++) {
			console.log("make factory " + objNames[numProducts+i]);
			factoryList.push(new factory({objType:factory, objID:(i+numProducts), objName:objNames[numProducts+i], subType:(i), items:[1,2,3,4,5], prices:[6, 7, 8, 9, 10]}));
		}
		console.log(factoryList);
		defaultBuildings = new uList(factoryList);

		nationList = new Array("Canada", "Mexico", "United States");

		// initialize windows
		useDeskTop.newPane("dialogPane");
		companyLabor = loadCompanyLabor(['.implode(',', $companyLabor).']);
		//loadCompanyLabor(['.implode(',', $companyLabor).']);
		webGLStart();
	}

window.addEventListener("load", initGame);

</script>

	<html>
	<body id="pageBody">
	<div id="ltPnl" style="position:absolute; top:15; left:10; height:675; width:100; border:1px solid #000000">
		ID: '.$pGameID.'<br>
		<a href="javascript:void(0);" onclick="scrMod(1004)">Financial</a>
		<a href="javascript:void(0);" onclick="scrMod(1002)">Busineses</a><br>
		<a href="javascript:void(0);" onclick="scrMod(1026)">Markets</a><br>
		<a href="javascript:void(0);" onclick="scrMod(1040)">Services</a><br>
		<a href="javascript:void(0);" onclick="scrMod(1041)">Conglomerate</a><br>
		<a href="javascript:void(0);" onclick="scrMod(1042)">Politics</a><br>
		<a href="javascript:void(0);" onclick="scrMod(1044)">Messages</a><br>
		<a href="javascript:void(0);" onclick="scrMod(1033)" style="position:absolute; bottom:60">Boost Store</a><br>
		<a href="javascript:void(0);" onclick="scrMod(1032)" style="position:absolute; bottom:40">Buy Gold</a><br>
		<a href="javascript:void(0);" onclick="scrMod(2001)" style="position:absolute; bottom:20">Gimme Coin</a><br>
		<a href="./index.php" style="position:absolute; bottom:0">Back to Main</a>
	</div>
	<div id="infoBar" style="position:absolute; top:640; left:110; height:50; width:1200; border:1px solid #000000">infoBar</div>
	<div id="rtPnl" style="position:absolute; top:15; left:1310; height:675; width:200; border:1px solid #000000; display:inline;"></div>
	<div id="botPnl" style="position:absolute; top:690; left:10; height:40; width:1400; border:1px solid #000000">
		<a href="javascript:void(0);" id="readMsg">Read Messages</a>
	</div>
	<div id="gmPnl" style="position:absolute; top:40; left:110; height:650; width:1200; border:1px solid #000000; overflow:hidden">
		<canvas style="position:absolute" id="gameCanvas" style="border: none;"></canvas>
	</div>
	<div id="topBar" style="position:absolute; top:15; left:110; height:25; width:1200; border:1px solid #000000; overflow:hidden">
		<div id="cashBox" style="position:absolute; top:0; left:0; height:25; border:1px solid #000000; padding-right:5; overflow:hidden"></div>
		<div id="goldBox" style="position:absolute; top:0; left:100; height:25; border:1px solid #000000; padding-right:5; overflow:hidden"></div>
	</div>

	<div id="scrBox" style="width:0; height:0; overflow:hidden;">
	</div>
	<div style="width:0; height:0; overflow:hidden;">
		<div style="position:absolute;  overflow:hidden; width:0; height:0; left:1210; top:150;"><canvas id="tCanvas" style="border: 1px solid black;" width=720 height=720></canvas></div>
		<div id="pointDat" style="position:absolute; right:0; bottom:0;"></div>
		<div id="diagBox" style="position:absolute; right:50; top:0; width:0; overflow:hidden;">
			<table>
				<tr><td>Lat:</td><td><input id="clickLat" value="0"></td></tr>
				<tr><td>Long:</td><td><input id="clickLong" value="0"></td></tr>
				<tr><td>Mask:</td><td><input type="checkbox" id="showMask"></td></tr>
				<tr><td>UseColor:</td><td><input type="checkbox" id="showUseColor"></td></tr>
				<tr><td>zVal:</td><td><input id="zVal"></td></tr>
				<tr><td>locX:</td><td><input id="locX"></td></tr>
				<tr><td>locY:</td><td><input id="locY"></td></tr>
				<tr><td>zLvl:</td><td><input id="zLvl"></td></tr>
				<tr><td>baseMap:</td><td><input id="baseMap"></td></tr>
				<tr><td>tileRef:</td><td><input id="tileRef"></td></tr>
				<tr><td>rivTargs:</td><td><input id="rivTargs"></td></tr>
				<tr><td>rivTiles:</td><td><input id="rivTiles"></td></tr>
				<tr><td>baseOff:</td><td><input id="baseOff"></td></tr>
				<tr><td>lookAt:</td><td><input id="lookAt"></td></tr>
				<tr><td>rotate:</td><td><input id="rotate"></td></tr>
				<tr><td>drawNum:</td><td><input id="drawNum"></td></tr>
				<tr><td>loadedQty:</td><td><input id="loadedQty"></td></tr>
				<tr><td>locLock:</td><td><input id="locLock"></td></tr>
				<tr><td>baseTile:</td><td><input id="baseTile"></td></tr>
				<tr><td>landRot:</td><td><input id="landRot"></td></tr>
				<tr><td>unitLength:</td><td><input id="unitLength"></td></tr>
				<tr><td>mapScale:</td><td><input id="mapScale"></td></tr>
				<tr><td>moveLength:</td><td><input id="moveLength"></td></tr>
			</table>
		</div>
	</div>
	<div style="position:absolute; right:0; bottom:5;">Show diagnostics? <input id="diagCB" type="checkbox" onchange="showDiagnostics()"></div>
	</body>
	</html>';

?>
