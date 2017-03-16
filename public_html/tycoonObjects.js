class object {
	constructor(options) {
		this.type = options.objType || 'unknown',
		this.unitName = options.objName || 'unnamed',
		this.status = options.status || 0,
		this.objID = options.objID;
	}

	update(object) {
		this.status = object.status || this.status,
		this.str = object.strength || this.str,
		this.subType = object.subType || this.subType,
		this.unitName = object.unitName || this.unitName,
		this.tNum = object.tNum || this.tNum;
		console.log("update unit " + this);
	}
}

class factory extends object {
	constructor(options) {
		super(options);
		this.factoryType = options.subType || 0,
		this.prod = options.prod || 0,
		this.quality = options.quality || 0,
		this.pollution = options.pol || 0,
		this.rights = options.rights || 0,
		this.rate = options.rate || 0,
		this.items = options.items || [],
		this.prices = options.prices || [];
	}

	renderSummary(target) {
		//console.log('draw ' + this.type)
		var thisDiv = addDiv(null, 'udHolder', target);
		thisDiv.setAttribute("data-unitid", this.unitID);

		thisDiv.nameDiv = addDiv("asdf", "sumName", thisDiv);
		thisDiv.nameDiv.setAttribute("data-boxName", "unitName");

		thisDiv.actDiv = addDiv("asdf", "sumAct", thisDiv);
		thisDiv.actDiv.setAttribute("data-boxName", "apBar");
		thisDiv.actDiv.setAttribute("data-boxunitid", this.unitID);

		thisDiv.expDiv = addDiv("asdf", "sumStr", thisDiv);
		thisDiv.expDiv.setAttribute("data-boxName", "strBar");
		thisDiv.expDiv.setAttribute("data-boxunitid", this.unitID);

		thisDiv.nameDiv.innerHTML = factoryNames[this.factoryType] + " - " + this.objID;
		return thisDiv;
	}

	renderDetail (target) {
		var thisDiv = addDiv(null, 'factoryDetailContainer', target);
		this.renderSummary(thisDiv);

		thisDiv.productsDiv = addDiv(null, "factoryProducts", thisDiv);
		thisDiv.laborDiv = addDiv(null, "factoryLabor", thisDiv);

		thisDiv.buttonDiv = addDiv(null, "factoryButton", thisDiv);
		//console.log(thisDiv);
		return thisDiv;
	}

}

setBar = function (id, desc, pct) {
	thisList = document.body.querySelectorAll(desc);
	for (n=0; n<thisList.length; n++) {
		if (thisList[n].getAttribute("data-boxunitid") == id) {
			thisList[n].style.width = pct*125/100;
			thisList[n].style.backgroundColor = "rgb("+parseInt((100-pct)*2.55)+", "+parseInt(pct*2.55)+", 0)";
		}
	}
}

class city {
	constructor(objDat, laws, taxes) {
		console.log(objDat);
		console.log(laws);
		console.log(taxes);
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
		this.laws = laws;
		//this.taxes = taxes;
		this.taxes = taxes.map(function(x) {
			console.log(x);
			if (isNaN(x)) {
				if (x.match(/^[0-9]/)) {
					return parseInt(x,10);
				}	else return x;
			} else return x;
	})}

	renderSummary(target) {
		//console.log('draw ' + this.type)
		var thisDiv = addDiv(null, 'udHolder', target);
		thisDiv.setAttribute("data-unitid", this.unitID);

		thisDiv.nameDiv = addDiv("asdf", "sumName", thisDiv);
		thisDiv.nameDiv.setAttribute("data-boxName", "unitName");

		thisDiv.actDiv = addDiv("asdf", "sumAct", thisDiv);
		thisDiv.actDiv.setAttribute("data-boxName", "apBar");
		thisDiv.actDiv.setAttribute("data-boxunitid", this.unitID);

		thisDiv.expDiv = addDiv("asdf", "sumStr", thisDiv);
		thisDiv.expDiv.setAttribute("data-boxName", "strBar");
		thisDiv.expDiv.setAttribute("data-boxunitid", this.unitID);

		thisDiv.nameDiv.innerHTML = this.objName + " - " + this.objID;
		return thisDiv;
	}

	renderDetail(target) {
		var containerDiv = addDiv(null, 'cityDetailContain', target);
		//containerDiv.innerHTML = this.objName;

		containerDiv.stats = addDiv(null, "cdStats", containerDiv);
		containerDiv.population = addDiv(null, "cdPop", containerDiv.stats);
		containerDiv.education = addDiv(null, "cdEd", containerDiv.stats);
		containerDiv.affluence = addDiv(null, "", containerDiv.stats);
		containerDiv.region = addDiv(null, "", containerDiv.stats);

		containerDiv.population.innerHTML = "Pop: " + this.details[13];
		containerDiv.education.innerHTML = "Education: " + this.details[14];
		containerDiv.affluence.innerHTML = "Aff: " + this.details[15];
		containerDiv.region.innerHTML = "Region: " + this.details[20];

		containerDiv.taxes = addDiv(null, "cdTax", containerDiv);
		let taxTable = document.createElement("table");
		taxTable.className = "taxTable";
		taxTable.cells = new Array();
		for (let i=0; i<7; i++) {
			let thisRow = taxTable.insertRow(-1);
			for (let j=0; j<5; j++) {
				let thisCell = thisRow.insertCell(-1);
			}
		}

		containerDiv.taxes.appendChild(taxTable);
		let total;

		taxTable.rows[0].cells[1].innerHTML = "C";
		taxTable.rows[0].cells[2].innerHTML = "R";
		taxTable.rows[0].cells[3].innerHTML = "N";
		taxTable.rows[0].cells[4].innerHTML = "T";

		taxTable.rows[1].cells[0].innerHTML = "IT";
		taxTable.rows[1].cells[1].innerHTML = this.taxes[0]/100;
		taxTable.rows[1].cells[2].innerHTML = this.taxes[1]/100;
		taxTable.rows[1].cells[3].innerHTML = this.taxes[2]/100;
		total = this.taxes[0]/100 + this.taxes[1]/100 + this.taxes[2]/100;
		taxTable.rows[1].cells[4].innerHTML = total.toFixed(2);

		taxTable.rows[2].cells[0].innerHTML = "PT";
		taxTable.rows[2].cells[1].innerHTML = this.taxes[3]/100;
		taxTable.rows[2].cells[2].innerHTML = this.taxes[4]/100;
		taxTable.rows[2].cells[3].innerHTML = this.taxes[5]/100;
		total = this.taxes[3]/100 + this.taxes[4]/100 + this.taxes[5]/100;
		taxTable.rows[2].cells[4].innerHTML = total.toFixed(2);

		taxTable.rows[3].cells[0].innerHTML = "VT";
		taxTable.rows[3].cells[1].innerHTML = this.taxes[6]/100;
		taxTable.rows[3].cells[2].innerHTML = this.taxes[7]/100;
		taxTable.rows[3].cells[3].innerHTML = this.taxes[8]/100;
		total = this.taxes[6]/100 + this.taxes[7]/100 + this.taxes[8]/100;
		taxTable.rows[3].cells[4].innerHTML = total.toFixed(2);

		taxTable.rows[4].cells[0].innerHTML = "PI";
		taxTable.rows[4].cells[1].innerHTML = this.taxes[9]/100;
		taxTable.rows[4].cells[2].innerHTML = this.taxes[10]/100;
		taxTable.rows[4].cells[3].innerHTML = this.taxes[11]/100;
		total = this.taxes[9]/100 + this.taxes[10]/100 + this.taxes[11]/100;
		taxTable.rows[4].cells[4].innerHTML = total.toFixed(2);

		taxTable.rows[5].cells[0].innerHTML = "PO";
		taxTable.rows[5].cells[1].innerHTML = this.taxes[12]/100;
		taxTable.rows[5].cells[2].innerHTML = this.taxes[13]/100;
		taxTable.rows[5].cells[3].innerHTML = this.taxes[14]/100;
		total = this.taxes[12]/100 + this.taxes[13]/100 + this.taxes[14]/100;
		taxTable.rows[5].cells[4].innerHTML = total.toFixed(2);

		taxTable.rows[6].cells[0].innerHTML = "RT";
		taxTable.rows[6].cells[1].innerHTML = this.taxes[15]/100;
		taxTable.rows[6].cells[2].innerHTML = this.taxes[16]/100;
		taxTable.rows[6].cells[3].innerHTML = this.taxes[17]/100;
		total = this.taxes[15]/100 + this.taxes[16]/100 + this.taxes[17]/100;
		taxTable.rows[6].cells[4].innerHTML = total.toFixed(2);

		containerDiv.taxes.taxEx = addDiv("", "taxEx", containerDiv.taxes);
		containerDiv.taxes.taxEx.parentObj = this;
		containerDiv.taxes.taxEx.addEventListener("click", function () {
			event.stopPropagation();
			this.parentObj.taxExes()});
		containerDiv.taxes.taxEx.innerHTML = "EX";

		containerDiv.demos = addDiv(null, "cdDemos", containerDiv);
		this.renderDemos(containerDiv.demos);

		return containerDiv;
	}

	taxExes() {
		infoPane = useDeskTop.newPane("infoPane");
		infoPane.innerHTML = "TAX DETAILS";
		infoPane.type_1 = addDiv("", "stdFloatDiv", infoPane);
		infoPane.type_2 = addDiv("", "stdFloatDiv", infoPane);
		infoPane.type_3 = addDiv("", "stdFloatDiv", infoPane);
		infoPane.type_4 = addDiv("", "stdFloatDiv", infoPane);
		infoPane.type_5 = addDiv("", "stdFloatDiv", infoPane);
		infoPane.type_6 = addDiv("", "stdFloatDiv", infoPane);

		for (var i=18; i<this.taxes.length; i+=5) {
			//console.log("switch " + this.taxes[i]);
			switch(this.taxes[i]) {
				case 1:
					//console.log("Company " + this.taxes[i+4] + " has a " + taxTypes[this.taxes[i+1]] + " rate of " + this.taxes[i+3]);
					textBlob("", infoPane.type_1, "Company " + this.taxes[i+4] + " has a " + taxTypes[this.taxes[i+1]] + " rate of " + this.taxes[i+3]);
					break;
				case 2:
					textBlob("", infoPane.type_2, "Factory type " + this.taxes[i+4] + " has a " + taxTypes[this.taxes[i+1]] + " rate of " + this.taxes[i+3]);
					break;
				case 3:
					textBlob("", infoPane.type_3, "Industry type " + this.taxes[i+4] + " has a " + taxTypes[this.taxes[i+1]] + " rate of " + this.taxes[i+3]);
					break;
				case 4:
					textBlob("", infoPane.type_4, "Factory " + this.taxes[i+4] + " has a " + taxTypes[this.taxes[i+1]] + " rate of " + this.taxes[i+3]);
					break;
				case 5:
					textBlob("", infoPane.type_5, "Conglomerate " + this.taxes[i+4] + " has a " + taxTypes[this.taxes[i+1]] + " rate of " + this.taxes[i+3]);
					break;
				case 6:
					textBlob("", infoPane.type_6, "Product " + this.taxes[i+4] + " has a " + taxTypes[this.taxes[i+1]] + " rate of " + this.taxes[i+3]);
					break;
			}
		}
	}

	loadDemands(demandRates, demandLevels) {
		this.demandRates = demandRates;
		this.demandLevels = demandLevels;
	}

	demandMenu(target) {
		target.innerHTML = "";
		target.sortDiv = addDiv(null, 'stdFloatDiv', target);
		target.sortDiv.innerHTML = "Sort Bar";

		target.showDiv = addDiv(null, 'stdFloatDiv', target);
		//return showDiv;
	}

	renderDemands(target, list) {
		for (var i=0; i<list.length; i++)	{
			let showRate = this.demandLevels[list[i]]/this.details[13]*this.demandRates[list[i]];
			let ratePct = Math.min(100, this.demandLevels[list[i]]/(2.0*this.demandRates[list[i]]*this.details[13]));

			console.log(this.demandLevels[list[i]] + " / 2.0 * " +this.demandRates[list[i]] + " * " + this.details[13]);

			let containDiv = addDiv("", "demandContain", target.showDiv);
			containDiv.product = addDiv("", "demandIcon", containDiv);
			containDiv.bar = addDiv("", "demandBar", containDiv);
			containDiv.rate = addDiv("", "demandRate", containDiv);
			containDiv.current = addDiv("", "demandCurrent", containDiv);
			console.log(this.demandLevels[list[i]] + " / " + this.details[13] + " * " +this.demandRates[list[i]]);
			containDiv.bar.innerHTML = showRate;

			let r = Math.floor(255 - 255*ratePct);
			let g = Math.floor(255*ratePct);
			let barSize = 20 + 180*ratePct/100;

			containDiv.bar.style.width = barSize;
			containDiv.bar.style.backgroundColor = "rgb(" + r + ", " + g + ",0)";
		}
	}

	renderDemos(trg) {
		console.log(this.townDemo);
		let r,g,b, townP, ldrP, demoP;
		for (var i=0; i<this.townDemo.length; i++) {
			let demoContain = addDiv("", "demogBox", trg);
			let demoBar = addDiv("", "demoBar", demoContain);
			let townStat = addDiv("", "townDemo", demoContain);
			let mayorSpot = addDiv("", "ldrDemo", demoContain);

			townP = this.townDemo[i]/1000;
			ldrP = this.leaderDemo[i]/1000;
			demoP = 0;

			townStat.style.width = parseInt(townP*120/1000+120);
			townStat.style.backgroundColor = "rgb(" + Math.floor(122.5 - 122.5*townP) + ", " + Math.floor(122.5 + 122.5*townP) + ",0)";

			mayorSpot.style.width = parseInt(ldrP*120/1000+120);
			mayorSpot.style.backgroundColor = "rgb(" + Math.floor(122.5 - 122.5*ldrP) + ", " + Math.floor(122.5 + 122.5*ldrP) + ",0)";

			demoBar.style.width = parseInt(demoP*120/1000+120);
			demoBar.style.backgroundColor = "rgb(" + Math.floor(122.5 - 122.5*demoP) + ", " + Math.floor(122.5 + 122.5*demoP) + ",0)";
		}
	}
}

class offer {
	constructor(details) {
		console.log(details);
		this.objID = details[0];
		this.productID = details[11];

		this.qty = details[1]; //1
		this.price = details[2]; //2
		this.sellingFactory = details[3]; //3
		this.quality = details[4]; //4
		this.pollution = details[5]; //5
		this.rights = details[6]; //6
		this.time = details[7]; //7
		this.saleLoc = details[8]; //8
		this.sellerID = details[9]; //9
		this.sellCongID = details[10]; //10
	}

	renderSummary(target) {
		var thisDiv = addDiv(null, 'udHolder', target);
		console.log("render product "+ this.productID);
		productArray[this.productID].renderSummary(thisDiv);

		thisDiv.setAttribute("data-unitid", this.unitID);

		thisDiv.buyBox = addDiv("", "", thisDiv);
		thisDiv.buyBox.innerHTML = "buy";

		thisDiv.nameDiv = addDiv("asdf", "sumName", thisDiv);
		thisDiv.nameDiv.setAttribute("data-boxName", "unitName");

		thisDiv.nameDiv.innerHTML = this.qty + " @ " + (this.price)/100;
		return thisDiv;
	}

	renderSale(target) {
		var thisDiv = addDiv(null, 'udHolder', target);
		console.log("render product "+ this.productID);
		productArray[this.productID].renderSummary(thisDiv);

		thisDiv.setAttribute("data-unitid", this.unitID);

		thisDiv.buyBox = addDiv("", "", thisDiv);
		thisDiv.buyBox.innerHTML = "sell";

		thisDiv.nameDiv = addDiv("asdf", "sumName", thisDiv);
		thisDiv.nameDiv.setAttribute("data-boxName", "unitName");

		thisDiv.nameDiv.innerHTML = this.qty + " @ " + (this.price)/100;
		return thisDiv;
	}

	renderCancel(target) {
		var thisDiv = this.renderSale(target);
		thisDiv.buyBox.innerHTML = "cancel";

		let orderID = this.objID;
		thisDiv.buyBox.addEventListener("click", function() {scrMod("1051,"+orderID)})
	}
}

class product {
	constructor(details) {

		this.objID = details.objID,
		this.objName = details.objName,
		this.qty = details.qty || 0;
	}

	renderSummary(target) {
		//console.log('draw ' + this.type)
		var thisDiv = addDiv(null, 'productHolder', target);
		thisDiv.setAttribute("data-unitid", this.unitID);

		addImg("asdf", "productImg", thisDiv); // labor image

		thisDiv.nameDiv = addDiv("asdf", "laborName", thisDiv);

		thisDiv.nameDiv.innerHTML = objNames[this.objID];
		return thisDiv;
	}

	renderQty(target, qty) {
		var thisDiv = addDiv(null, 'productHolder', target);
		thisDiv.setAttribute("data-unitid", this.unitID);

		thisDiv.nameDiv = addDiv("asdf", "laborName", thisDiv);
		thisDiv.nameDiv.setAttribute("data-boxName", "unitName");

		addImg("asdf", "productImg", thisDiv); // labor image

		thisDiv.qtyDiv = addDiv("asdf", "productQty", thisDiv);
		thisDiv.qtyDiv.innerHTML = qty.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");;

		thisDiv.nameDiv.innerHTML = objNames[this.objID];
		return thisDiv;
	}
}

class service {
	constructor(details) {

		this.objID = details.objID,
		this.objName = details.objName,
		this.qty = details.qty || 0;
	}

	renderSummary(target) {
		//console.log('draw ' + this.type)
		var thisDiv = addDiv(null, 'productHolder', target);
		thisDiv.setAttribute("data-unitid", this.unitID);

		thisDiv.nameDiv = addDiv("asdf", "productName", thisDiv);
		thisDiv.nameDiv.setAttribute("data-boxName", "unitName");

		thisDiv.nameDiv.innerHTML = objNames[this.objID] + " - " + this.objID;
		return thisDiv;
	}

	renderQty(target, qty) {
		var thisDiv = addDiv(null, 'productHolder', target);
		thisDiv.setAttribute("data-unitid", this.unitID);

		thisDiv.nameDiv = addDiv("asdf", "productName", thisDiv);
		thisDiv.nameDiv.setAttribute("data-boxName", "unitName");

		thisDiv.qtyDiv = addDiv("asdf", "productQty", thisDiv);
		thisDiv.qtyDiv.innerHTML = qty.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");;

		thisDiv.nameDiv.innerHTML = objNames[this.objID] + " - " + this.objID;
		return thisDiv;
	}
}

class labor {
	constructor(details) {

		this.objID = details.objID,
		this.objName = details.objName,
		this.qty = details.qty || 0;
		this.edClass = details.edClass || "0",
		this.laborType = details.laborType,
		this.quality = details.ability || 0;
		//console.log('create product ' + this.objID);
	}

	renderSummary(target) {
		var thisDiv = addDiv(null, 'productHolder', target);

		thisDiv.ownerObject = this.objID;

		thisDiv.nameDiv = addDiv("asdf", "laborName", thisDiv);

		addImg("asdf", "laborImg", thisDiv); // labor image

		thisDiv.qualBar = addDiv("asdf", "laborQualBar", thisDiv); // labor quality bar;
		let qualPct = (this.quality%3600)/3600;
		thisDiv.qualBar.style.width = 10 + 65*qualPct;
		thisDiv.qualBar.style.backgroundColor = "rgb(" + parseInt(255*(1-qualPct)) + ", " + parseInt(255*qualPct) + ", 0)";

		thisDiv.qualNum = addDiv("asdf", "laborQualNum", thisDiv);
		thisDiv.qualNum.innerHTML = this.quality;

		thisDiv.eduDiv = addDiv("asdf", "laborEd", thisDiv);
		thisDiv.eduDiv.innerHTML = this.edClass;

		thisDiv.nameDiv.innerHTML = laborNames[this.laborType];
		return thisDiv;
	}

	renderHire(target, quality, sendStr) {
		let hireContain = this.renderSummary(target);

		hireContain.hireButton = newButton(hireContain);
		hireContain.hireButton.innerHTML = "Hire!";
		hireContain.hireButton.sendStr = sendStr;

		hireContain.addEventListener("click", function () {
			scrMod("1057,"+sendStr);
		});

		return hireContain;
	}

	renderFire(target, sendStr) {
		let hireContain = this.renderSummary(target);
		if (this.laborType > 0) {
			hireContain.hireButton = newButton(hireContain);
			hireContain.hireButton.innerHTML = "Fire!";
			hireContain.hireButton.sendStr = sendStr;

			hireContain.hireButton.addEventListener("click", function () {
				event.stopPropagation();
				scrMod(sendStr);
			});
		}

		return hireContain;
	}

	renderQty(target, qty) {
		console.log("renderdqty");
		var thisDiv = addDiv(null, 'productHolder', target);
		thisDiv.setAttribute("data-unitid", this.unitID);

		thisDiv.nameDiv = addDiv("asdf", "laborName", thisDiv);
		thisDiv.nameDiv.setAttribute("data-boxName", "unitName");

		thisDiv.qtyDiv = addDiv("asdf", "productQty", thisDiv);
		thisDiv.qtyDiv.innerHTML = qty;

		thisDiv.nameDiv.innerHTML = laborNames[this.laborType];
		return thisDiv;
	}

	renderSimple(target) {
		var thisDiv = addDiv(null, 'productHolder', target);

		thisDiv.ownerObject = this.objID;

		thisDiv.nameDiv = addDiv("asdf", "laborName", thisDiv);
		//thisDiv.nameDiv.setAttribute("data-boxName", "unitName");

		addImg("asdf", "laborImg", thisDiv); // labor image

		//thisDiv.qualBar = addDiv("asdf", "laborQualBar", thisDiv); // labor quality bar;
		//let qualPct = (this.quality%3600)/3600;
		//thisDiv.qualBar.style.width = 10 + 65*qualPct;
		//thisDiv.qualBar.style.backgroundColor = "rgb(" + parseInt(255*(1-qualPct)) + ", " + parseInt(255*qualPct) + ", 0)";

		//thisDiv.qualNum = addDiv("asdf", "laborQualNum", thisDiv);
		//thisDiv.qualNum.innerHTML = parseInt(this.quality/3600);

		thisDiv.eduDiv = addDiv("asdf", "laborEd", thisDiv);
		thisDiv.eduDiv.innerHTML = this.edClass;

		//console.log("labor type " + this.laborType + " == " + laborNames[this.laborType]);
		//console.log(laborNames);
		thisDiv.nameDiv.innerHTML = laborNames[this.laborType];
		return thisDiv;
	}
}

class laborItem extends labor {
	constructor(details) {
		super(details);
		this.pay = details.pay;
	}

	renderSummary(target) {
		var thisDiv = addDiv(null, 'productHolder', target);

		thisDiv.ownerObject = this.objID;

		thisDiv.nameDiv = addDiv("asdf", "laborName", thisDiv);
		//thisDiv.nameDiv.setAttribute("data-boxName", "unitName");

		addImg("asdf", "laborImg", thisDiv); // labor image

		thisDiv.qualBar = addDiv("asdf", "laborQualBar", thisDiv); // labor quality bar;
		let qualPct = (this.quality%3600)/3600;
		thisDiv.qualBar.style.width = 10 + 65*qualPct;
		thisDiv.qualBar.style.backgroundColor = "rgb(" + parseInt(255*(1-qualPct)) + ", " + parseInt(255*qualPct) + ", 0)";

		thisDiv.qualNum = addDiv("asdf", "laborQualNum", thisDiv);
		thisDiv.qualNum.innerHTML = this.quality;

		thisDiv.eduDiv = addDiv("asdf", "laborEd", thisDiv);
		thisDiv.eduDiv.innerHTML = this.edClass;
		/*
		thisDiv.fireDiv = addDiv("asdf", "laborFire", thisDiv);
		thisDiv.fireDiv.innerHTML = "F";
		*/
		thisDiv.payDiv = addDiv("", "laborPay", thisDiv);
		thisDiv.payDiv.innerHTML = "$"+(this.pay/100).toFixed(2);

		thisDiv.nameDiv.innerHTML = laborNames[this.laborType] + "(" +this.laborType+ ")";
		return thisDiv;
	}

}

class gamePlayer {
	constructor(data) {
		this.moneyCash = data[0] || 0;
		this.moneyGold = data[1] || 0;
		this.money = this.moneyCash;
		this.gold = this.moneyGold;
		this.boosts = new Array();
	}

	set money (x) {
		console.log("setting player money to " + x);
		this.moneyCash = x;
		document.getElementById("cashBox").innerHTML = "$" + (this.moneyCash/100).toFixed(2);
	}

	set gold (x) {
		console.log("setting playergold to  " + x);
		this.moneyGold = x;
		document.getElementById("goldBox").innerHTML = "G " + this.moneyGold;
	}

	setBoosts(boosts) {
		this.boosts = boosts;
	}
}

class factoryUpgrade {
	constructor(id, endTime) {
		this.factoryID = id;
		this.endTime = endTime;
		this.timeBoost = 0;
		console.log("Set ID to " + this.factoryID);
	}

	boostClock(deltaT) {
		this.timeBoost += deltaT;
	}

	render(target, boost=true) {
		var containerBox = addDiv("", "", target);
		containerBox.innerHTML = "upgrading somethin";
		containerBox.timeBox = addDiv("", "timeFloat", containerBox);

		let date = new Date();
		if (this.endTime > Math.floor(date.getTime()/1000)) {

			var objectPointer = this;
			containerBox.clockObj = setInterval(function () {runClock(objectPointer.endTime, containerBox, "", function () {console.log("factory upgrade completion")}, objectPointer.timeBoost)}, 1000)

			if (boost) {
				containerBox.boostBox = addDiv("", "buildSpeedUp", containerBox);
				containerBox.boostBox.innerHTML = "S";

				let useID = this.factoryID;
				containerBox.boostBox.addEventListener("click", function () {scrMod("1029,1,"+useID)});
			}
		}

		return this.containerBox
	}
}

class factoryOrder {
	constructor(id, endTime, productID, qty, spotNum) {
		this.factoryID = id || 0;
		this.endTime = endTime;
		this.timeBoost = 0;
		this.material = productID;
		this.qty = qty;
		this.orderNum = spotNum;
	}

	boostClock(deltaT) {
		this.timeBoost += deltaT;
	}

	render(target, boost=true) {
		var containerBox = addDiv("", "orderContain", target);

		this.displayBox = containerBox;
		this.showItem(this.displayBox, boost);
		return containerBox;
	}

	updateOrder (id, endTime, productID, qty) {
		console.log("update order to " + this.displayBox);
		this.factoryID = id || 0;
		this.endTime = endTime;
		this.timeBoost = 0;
		this.material = productID;
		this.qty = qty;
		//this.displayBox.innerHTML = "";
		this.showItem(this.displayBox, true);
		//this.displayBox.innerHTML = "";
	}

	showItem (containerBox, boost=true) {
		containerBox.innerHTML = "";
		materialBox(this.material, this.qty, containerBox);
		containerBox.timeBox = addDiv("", "timeFloat", containerBox);
		var thisObject = this;
		if (this.material == 0) containerBox.addEventListener("click", function () {

			useDeskTop.newPane("xyzPane");
			orderPane = useDeskTop.getPane("xyzPane");
			orderPane.innerHTML = "";

			event.stopPropagation();

			textBlob("", orderPane, "Select which item you want to order");
			invList.reset();

			orderPane.orderBox1 = invList.SLsingleButton(orderPane);
			var orderSelectButton = newButton(orderPane, function () {scrMod("1009, " + thisObject.factoryID + ", "+ SLreadSelection(orderPane.orderBox1))});
			orderPane.offerContainer = addDiv("", "stdContain", orderPane);

			orderSelectButton.innerHTML = "Find Offers";

			});

		let date = new Date();
		if (this.endTime > Math.floor(date.getTime()/1000)) {
			var objectPointer = this;
			console.log("start a clock for object " + this.orderNum);
			containerBox.clockObj = setInterval(function () {runClock(objectPointer.endTime, containerBox, objectPointer, function (trgObject) {
				console.log(objectPointer);

				//businessDiv.orderItems.innerHTML = "";


				for (var i=0; i<materialInv.length; i+=2) {
					if (materialInv[i] == trgObject.material) {
						materialInv[i+1] += trgObject.qty;
						console.log("add " + trgObject.qty);
						showInventory(trgObject.factoryID, materialInv);
						break;
					}
				}
				trgObject.material = 0;
				trgObject.qty = 0;
				//factoryOrders[trgObject.orderNum] = new factoryOrder(0, 0, 0, 0, 0);
				factoryOrders[trgObject.orderNum].updateOrder(trgObject.factoryID, 0, 0, 0);
			}, objectPointer.timeBoost)}, 1000);

			if (boost) {
				containerBox.boostBox = addDiv("", "buildSpeedUp", containerBox);
				containerBox.boostBox.innerHTML = "S";

				let useID = this.factoryID;
				containerBox.boostBox.addEventListener("click", function () {scrMod("1036,"+this.factoryID + "," + this.orderNum)});
			}
		}
		//this.displayBox = containerBox;
	}
}

class factoryProduction {
	constructor(id, endTime, productID, qty) {
		this.factoryID = id;
		this.endTime = endTime;
		this.timeBoost = 0;
		this.material = productID;
		this.qty = qty;
	}

	boostClock(deltaT) {
		this.boost += deltaT;
	}

	render(target, boost=true) {
		var containerBox = addDiv("", "orderContain", target);
		//materialBox(rscID, qty, containerBox);
		containerBox.timeBox = addDiv("", "timeFloat", containerBox);

		let date = new Date();
		if (this.endTime > Math.floor(date.getTime()/1000)) {
			var objectPointer = this;
			containerBox.clockObj = setInterval(function () {runClock(objectPointer.endTime, containerBox, objectPointer, function (trgObject) {
				console.log("prod complete " + trgObject.qty);
				for (var i=0; i<5; i++) {
					if (productStores[i] == trgObject.material) productStores[i+5] += trgObject.qty;
				}
				showOutputs(productInvSection, productStores);
			}, objectPointer.timeBoost)}, 1000);


			if (boost) {
				containerBox.boostBox = addDiv("", "buildSpeedUp", containerBox);
				containerBox.boostBox.innerHTML = "S";

				let useID = this.factoryID;
				containerBox.boostBox.addEventListener("click", function () {scrMod("1035,"+this.factoryID)});
			}
		}
		return containerBox
	}
}

class message {
	constructor(dat) {
		this.loaded = false;
		this.content = "";
		this.subject = dat.subject || "";
		this.clicked = dat.read || 0;
		this.id = dat.id;
		this.fromID = dat.fromID;
		this.fromName = dat.fromName;
		this.sentTime = dat.time;
	}

	showContent(trg) {
		this.clicked = (this.clicked+1)%2;
		this.renderObject.className = "msgSum";
		if (!this.clicked) {
			this.renderObject.contentBox.className = "msgContentHide";
		} else {
			if (this.loaded) {
				this.renderContent(trg) ;
			} else {
				this.content = returnInfo("1046,"+this.id);
				this.renderContent(trg);
			}
		}
	}

	collapse() {
		this.renderObject.contentBox.className = "msgContentHide";
	}

	renderSummary(trg) {
		let summaryBar;
		if (this.read) summaryBar = addDiv("", "msgSum", trg);
		else summaryBar = addDiv("", "msgSumNew", trg);

		let msgItem = this;
		summaryBar.addEventListener("click", function () {
			msgItem.showContent(msgItem.renderObject.contentBox);
		});

		summaryBar.subjSpan = document.createElement("span");
		summaryBar.subjSpan.innerHTML = this.subject;
		summaryBar.appendChild(summaryBar.subjSpan);

		summaryBar.fromSpan = document.createElement("span");
		summaryBar.fromSpan.innerHTML = "From: " + this.fromName;
		let msgSender = this.fromID;
		summaryBar.fromSpan.addEventListener("click", function () {
			scrMod("1045,"+msgSender);
		});
		summaryBar.appendChild(summaryBar.fromSpan);

		summaryBar.timeSpan = document.createElement("span");
		summaryBar.timeSpan.innerHTML = "Sent: " + this.sentTime;
		summaryBar.timeSpan.className = "spanStyle";
		summaryBar.appendChild(summaryBar.timeSpan);

		summaryBar.contentBox = addDiv("", "msgContentHide", summaryBar);


		this.renderObject = summaryBar;
	}

	renderContent() {
		this.renderObject.contentBox.className = "msgContentShow";
		this.renderObject.contentBox.innerHTML = this.content;
	}
}

class school {
	constructor(details, laborTypes) {
		this.schoolID = details[0];
		this.schName = details[1];
		this.laborItems = laborTypes;
	}

	renderSummary(trg) {
		let contain = addDiv("", "schoolContain", trg);
		contain.schName = addDiv("", "schoolName", contain);
		contain.schName.innerHTML = this.schName;
		//textBlob("", contain, "this school can train the following labor items");
		contain.schoolLvl = addDiv("", "schoolLevel", contain);

		contain.laborSect = addDiv("", "schoolLabor", contain);
		laborArray[this.laborItems[0]].renderSimple(contain.laborSect);
		laborArray[this.laborItems[1]].renderSimple(contain.laborSect);
		laborArray[this.laborItems[2]].renderSimple(contain.laborSect);
		return contain;
	}

	buildOpt(trg, id, cityNum) {
		let contain = this.renderSummary(trg);
		let buyButton = newButton(contain);
		let schID = this.schoolID;
		buyButton.innerHTML = "Build this School";
		buyButton.addEventListener("click", function() {scrMod("1054,"+schID+","+cityNum)});
	}

	renderCitySchools(trg, cityID, factoryID, lvl, schStatus, price) {
		let contain = this.renderSummary(trg);

		contain.hireButton = addDiv("", "schoolHire", contain);
		contain.hireButton.innerHTML = "hire from here";
		contain.hireButton.sendStr = cityID+","+factoryID+","+this.schoolID;
		contain.hireButton.addEventListener("click", function () {scrMod("1056,"+this.sendStr)})

		if (schStatus == 100) contain.schoolLvl.innerHTML = "Level " + lvl;
		else {
			contain.schoolLvl.innerHTML = "Level " + lvl + ", " + schStatus + "%";
			// create pricing slide area
			contain.schPrice = addDiv("", "schoolPrice", contain);

			slideValBar(contain.schPrice, "sp-"+cityID+","+this.schoolID, 0, 1000); //(trg, slideID, low, hi)
			let savePrice = newButton(contain.schPrice);
			savePrice.innerHTML = "Set Price";
			savePrice.schID = cityID+","+this.schoolID;

			savePrice.addEventListener("click", function () {
				console.log("look for sp-"+this.schID);
				console.log(document.getElementById("sp-"+this.schID));
				scrMod("1055,"+this.schID + ","+document.getElementById("sp-"+this.schID).value)});
		}
	}
}
