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
	constructor(objDat) {

		this.objID = objDat[0];
		this.objName = objDat[1];
		this.details = objDat;
		this.demandRates = "";
		this.demandLevels = "";
		//console.log('create product ' + this.objID);
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

		thisDiv.nameDiv.innerHTML = this.objName + " - " + this.objID;
		return thisDiv;
	}

	renderDetail(target) {
		var containerDiv = addDiv(null, 'udHolder', target);
		//containerDiv.innerHTML = this.objName;

		containerDiv.population = addDiv(null, "", containerDiv);
		containerDiv.education = addDiv(null, "", containerDiv);
		containerDiv.affluence = addDiv(null, "", containerDiv);
		containerDiv.region = addDiv(null, "", containerDiv);

		containerDiv.population.innerHTML = "Pop: " + this.details[13];
		containerDiv.education.innerHTML = "Education: " + this.details[14];
		containerDiv.affluence.innerHTML = "Aff: " + this.details[15];
		containerDiv.region.innerHTML = "Region: " + this.details[20];

		return containerDiv;
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
}

class offer {
	constructor(details) {
		this.objID = details[0];
		this.qty = details[1];
		this.price = details[2];
		this.seller = details[3];
		this.quality = details[4];
		this.pollution = details[5];
		this.rights = details[6];
		this.productID = details[7]
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
		this.edClass = details.edClass || "None",
		this.laborType = details.laborType;
		//console.log('create product ' + this.objID);
	}

	renderSummary(target) {
		var thisDiv = addDiv(null, 'productHolder', target);

		thisDiv.ownerObject = this.objID;

		thisDiv.nameDiv = addDiv("asdf", "productName", thisDiv);
		thisDiv.nameDiv.setAttribute("data-boxName", "unitName");

		thisDiv.nameDiv.innerHTML = "Labor - " + laborNames[this.laborType];
		return thisDiv;
	}

	renderQty(target, qty) {
		console.log("renderdqty");
		var thisDiv = addDiv(null, 'productHolder', target);
		thisDiv.setAttribute("data-unitid", this.unitID);

		thisDiv.nameDiv = addDiv("asdf", "productName", thisDiv);
		thisDiv.nameDiv.setAttribute("data-boxName", "unitName");

		thisDiv.qtyDiv = addDiv("asdf", "productQty", thisDiv);
		thisDiv.qtyDiv.innerHTML = qty;

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
		thisDiv.setAttribute('ownerObject', this.objID);
		//thisDiv.ownerObject = this.objID;
		//console.log(thisDiv.ownerObject);

		thisDiv.nameDiv = addDiv("asdf", "productName", thisDiv);
		thisDiv.nameDiv.setAttribute("data-boxName", "unitName");

		thisDiv.nameDiv.innerHTML = laborNames[this.laborType] + "(" + this.laborType + ")" + "<br>Pay: " + this.pay;
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
			orderPane.offerContainer = addDiv("", "stdContain", orderPane);
			var orderSelectButton = newButton(orderPane, function () {scrMod("1009, " + thisObject.factoryID + ", "+ SLreadSelection(orderPane.orderBox1))});
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
