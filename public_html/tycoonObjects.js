class object {
	/*
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
	}*/
	renderUpdate() {
		console.log("updating... " + this.instances.length + " items");
		//console.log(this.instances);
		for (var i=this.instances.length-1; i>-1; i--) {
			console.log("(" + i + ") updating... " + this.instances[i]);

			let checkDiv = this.instances[i];
			while (checkDiv) {
				let tmp = checkDiv.parentNode;
				//console.log(checkDiv);
				if (checkDiv.tagName == "BODY") {
					this.instances[i].updateFunction(this.instances[i]);
					console.log(this.instances[i]);
					console.log(this.instances[i].updateFunction);
					break;
				}
				checkDiv = tmp;
			}
			if (!checkDiv) {
				//console.log("delete this instance " + i);
				this.instances.splice(i, 1);
				//this.instances = [];
			}
		}
	}
}

class factory extends object {
	constructor(dat) {
		//console.log(dat);
		super(dat);
		this.init(dat);
		this.instances = [];
	}

	init(dat) {
		this.objID = dat[3];
		this.factoryID = dat[3];
		this.factoryType = dat[15]-numProducts;
		this.prod = [dat[4], dat[5], dat[6], dat[7], dat[8]];
		this.prodInv = [dat[9], dat[10], dat[11], dat[12], dat[13]];
		this.nextUpdate = dat[14];
		this.currentPrd = dat[1];
		this.currentRate = dat[2];
		this.prodDtls = dat.slice(16, 41);
		this.taxes = dat.slice(41,72) || this.taxes;
		this.taxes.push(6,1,2,25);
		console.log(this.prodDtls);
	}

	update(dat) {
		this.init(dat);
		console.log(this);
		this.renderUpdate();
	}

	renderSummary(target) {
		var thisDiv;
		if (target.divType != "factorySummary") {
			thisDiv = addDiv(null, 'udHolder', target);
			this.instances.push(thisDiv);
			thisDiv.updateFunction = function (x) {
				console.log("update summary");
				me.renderSummary(x)};
			let me = this;
		}
		else {
			thisDiv = target;
		}

		thisDiv.innerHTML = "";
		thisDiv.nameDiv = addDiv("asdf", "sumName", thisDiv);
		thisDiv.nameDiv.innerHTML = factoryNames[this.factoryType] + " - " + this.objID;
		thisDiv.divType = "factorySummary";

		return thisDiv;
	}

	renderDetail (target) {
		var thisDiv;
		if (target.divType != "factorySummary") {
			thisDiv = addDiv(null, 'factoryDetailContainer', target);
			this.instances.push(thisDiv);
		}
		else {
			thisDiv = target;
		}

		thisDiv.divType = "factorySummary";
		this.renderSummary(thisDiv);

		let me = this;
		thisDiv.updateFunction = function (x) {
			console.log("update detail");
			me.renderDetail(x)};

		thisDiv.productsDiv = addDiv(null, "factoryProducts", thisDiv);
		thisDiv.laborDiv = addDiv(null, "factoryLabor", thisDiv);

		thisDiv.buttonDiv = addDiv(null, "factoryButton", thisDiv);
		//console.log(thisDiv);
		return thisDiv;
	}

	itemBar(target, prodIndex, sendStr) {
		console.log("render item Bar");
		var container;
		if (target.instanceType == "itemBar") {
			container = target;
		} else {
			container = addDiv(null, "", target);
			container.innerHTML = "";
			container.instanceType = "itemBar";
			container.factoryDiv = this.renderSummary(container);
			container.productDiv = productArray[this.prod[prodIndex]].renderDtls(container, this.prodInv[prodIndex], this.prodDtls[prodIndex*5+4], this.prodDtls[prodIndex*5+3], 0, 0);
			container.prodSection = addDiv("", "", container);

			container.slide = slideValBar(container, "", 0, this.prodInv[prodIndex]);

			container.mCost = addDiv("", "", container);
			container.lCost = addDiv("", "", container);
			container.taxBar = addDiv("", "", container);
			container.priceBar = addDiv("", "", container);
			container.taxCost = addDiv("", "", container);
			container.profit = addDiv("", "", container);

			let button = newButton(container, function () {
				scrMod(this.parentNode.sendStr + "," + this.parentNode.slide.slide.value);
			});
			button.innerHTML = "send this amount";
		}

		if (target.instanced) {
			container.instanced = true;
		} else {
			this.instances.push(container);
			container.instanced = true;

			let me = this;
			container.prodIndex = prodIndex;
			container.updateFunction = function (x) {
				console.log("update itemBar");
				me.itemBar(x, this.prodIndex, this.sendStr)};
		}

		container.prodSection.innerHTML = "Qty: "+ this.prodInv[prodIndex];
		container.slide.slide.max = this.prodInv[prodIndex];
		container.slide.slide.value = 0;
		container.slide.setVal.innerHTML = 0;
		container.slide.maxVal.innerHTML = this.prodInv[prodIndex];

		// estimate taxes
		console.log(this.taxes);
		let taxRates = this.taxes;
		calcTaxRates([0,0,0,0,0,0,this.prod[prodIndex],0,0,0,0,0,0,0,0,0,0], taxRates);

		let totalTax = taxRates.slice(0,30).reduce(function (a, b) {return a+b}, 0)/10000;
		container.taxBar.innerHTML = "Tax rate of " + totalTax + "%";
		container.sendStr = sendStr;

		container.totalTax = totalTax;
		//container.slide.slide.addEventListener("change", function () {console.log("Tax Rate of " + totalTax)});

		return container;
	}

	prodDetail(target, prodIndex) {
		var container = addDiv("", "", target);

		container.factoryDiv = this.renderSummary(container);
		container.prodSection = addDiv("", "", container);
		container.prodSection.innerHTML = "Qty: "+ this.prodInv[prodIndex];
	}

	showOrders(trg) {
		let factoryOrders = new Array();
		for (var i=0; i<this.materialOrder.length; i+=18) {
			factoryOrders.push(new factoryOrder(this.materialOrder.slice(i, i+18)));
			//factoryOrders.push(new factoryOrder('.$postVals[1].', materialOrder[i], materialOrder[i+1], materialOrder[i+2], i/3));
		}

		showOrders(trg, factoryOrders);
	}

	showLabor(trg) {
		let factoryLabor = new Array();
		factoryLabor.push(new laborItem({objID:0, pay:0, ability:0, laborType:0}));
		for (var i=0; i<this.labor.length; i+=10) {
			factoryLabor.push(new laborItem({objID:(this.labor[i]/10+1), pay:(this.labor[i+5]), ability:(this.labor[i+8]), laborType:this.labor[i]}));
		}

		trg.innerHTML = "";

		for (var i=1; i<factoryLabor.length; i++) {
			let laborItem = factoryLabor[i].renderFire(factoryDiv.laborSection.aassigned, "1059,"+i+","+this.ID);
			if (factoryLabor[i].laborType > 0) {
			} else {
			}
			let itemNum = i;
			laborItem.addEventListener("click", function () {scrMod("1023,"+this.ID+","+itemNum)});
		}
	}

	showOutputs(trg) {
		trg.innerHTML = "";
		for (var i=0; i<5; i++) {
			if (this.productStores[i]>0) {
				//productArray[this.productStores[i]].renderQty(trg, this.productStores[i+5]);
				productArray[this.prod[i]].renderDtls(trg, this.prodInv[i], this.prodDtls[i*5+4], this.prodDtls[i*5+3], 0, 0);
			}
		}
	}

	showProdRequirements(trg) {
		trg.innerHTML = "";
		for (var i=0; i<this.productMaterial.length; i+=2) {
			materialBox(this.productMaterial[i], this.productMaterial[i+1], trg);
		}
	}

	showReqLabor(trg) {
		trg.innerHTML = "";
		for (var i=0; i<this.productLabor.length; i++) {
			if (this.productLabor[i]>0) laborArray[this.productLabor[i]].renderSimple(trg);
		}
	}

	showInventory(trg) {
		trg.innerHTML = "";
		textBlob("", trg, "Current resource stores:");
		for (var i=0; i<this.materialInv.length; i+=2) {
			//productArray[this.prod[prodIndex]].renderDtls(container, this.prodInv[prodIndex], this.prodDtls[prodIndex*5+4], this.prodDtls[prodIndex*5+3], 0, 0);
			materialBox(this.materialInv[i], this.materialInv[i+1], trg);
		}
	}

	showSales(trg) {
		//console.log("show sales");
		let oList = [];
		for (var i=0; i<this.factorySales.length; i+=12) {
			oList.push(new offer(this.factorySales.slice(i, i+12)));
		}
		for (var i=0; i<oList.length; i++) {
			console.log("show offer " + i);
			oList[i].renderCancel(trg);
		}
	}

	showContracts(trg) {
		console.log("fac contracts size is  " + this.contracts.length);
		let startPos = this.contracts[0]+1;
		let invCount = 0;
		for (var i=0; i<this.contracts[0]; i++) {
			let contractHolder = addDiv("", "facContract", trg);
			console.log("make con");
			let thisContract = new contract(new Int32Array(this.contracts.slice(startPos, startPos+27)));
			console.log(thisContract);
			thisContract.render(contractHolder);

			let invStart = this.contracts[0]*27+this.contracts[0]+1+invCount*50;

			console.log("invoices from " + invStart + " to " + (invStart + this.contracts[1+i]*50))
			contractInvoice(this.contracts.slice(invStart, invStart + this.contracts[1+i]*50), contractHolder);
			invCount += this.contracts[i+1];
		}
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

class shipment() {
	constructor(dat) {
		this.status = dat[0];
		this.prodID = dat[1];
		this.qty = dat[2];
		this.qual = dat[4];
		this.poll = dat[5];
		this.rights = dat[6];
		this.sentTime = dat[7];
		this.delTime = dat[11];
		this.matCost = dat[14];
		this.labCost = dat[15];
	}
	
	renderSummary(trg) {
		container = renderDtls(trg, this.qty, this.matCost, this.labCost, this.qual, this.poll);
		container.arriveTime = addDiv("", "", container);
		container.arriveTime.innerHTML = this.delTime
}

class city {
	constructor(objDat, laws, taxes) {

		this.objID = objDat[0];
		this.objName = objDat[1];
		this.details = objDat;
		this.demandRates = "";
		this.demandLevels = "";
		this.rTax = objDat[14];
		this.nTax = objDat[15];
		this.leader = objDat[16];
		//this.population = objDat[13];
		this.townDemo = new Array(10, 20, 30, 40, 50, 60, 70, 80, 90, 100);
		this.leaderDemo = new Array(-10, -20, -30, -40, -50, -60, -70, -80, -90, -100);
		this.laws = laws;
		this.incomeLvls = [0, 25, 25, 23, 10, 6, 3, 3, 2, 2, 1, 0];
		this.population = 1000000;
		this.loadedProduct = 0;

		//this.taxes = taxes;
		this.taxes = taxes.map(function(x) {
			//console.log(x);
			if (isNaN(x)) {
				if (x.match(/^[0-9]/)) {
					return parseInt(x,10);
				}	else return x;
			} else return x;
	})
	//console.log(this.taxes);
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
		taxTable.rows[1].cells[2].innerHTML = this.taxes[10]/100;
		taxTable.rows[1].cells[3].innerHTML = this.taxes[20]/100;
		total = this.taxes[0]/100 + this.taxes[10]/100 + this.taxes[20]/100;
		taxTable.rows[1].cells[4].innerHTML = total.toFixed(2);

		taxTable.rows[2].cells[0].innerHTML = "PT";
		taxTable.rows[2].cells[1].innerHTML = this.taxes[1]/100;
		taxTable.rows[2].cells[2].innerHTML = this.taxes[11]/100;
		taxTable.rows[2].cells[3].innerHTML = this.taxes[21]/100;
		total = this.taxes[3]/100 + this.taxes[11]/100 + this.taxes[21]/100;
		taxTable.rows[2].cells[4].innerHTML = total.toFixed(2);

		taxTable.rows[3].cells[0].innerHTML = "VT";
		taxTable.rows[3].cells[1].innerHTML = this.taxes[2]/100;
		taxTable.rows[3].cells[2].innerHTML = this.taxes[12]/100;
		taxTable.rows[3].cells[3].innerHTML = this.taxes[22]/100;
		total = this.taxes[2]/100 + this.taxes[12]/100 + this.taxes[22]/100;
		taxTable.rows[3].cells[4].innerHTML = total.toFixed(2);

		taxTable.rows[4].cells[0].innerHTML = "PI";
		taxTable.rows[4].cells[1].innerHTML = this.taxes[3]/100;
		taxTable.rows[4].cells[2].innerHTML = this.taxes[13]/100;
		taxTable.rows[4].cells[3].innerHTML = this.taxes[23]/100;
		total = this.taxes[3]/100 + this.taxes[13]/100 + this.taxes[23]/100;
		taxTable.rows[4].cells[4].innerHTML = total.toFixed(2);

		taxTable.rows[5].cells[0].innerHTML = "PO";
		taxTable.rows[5].cells[1].innerHTML = this.taxes[4]/100;
		taxTable.rows[5].cells[2].innerHTML = this.taxes[14]/100;
		taxTable.rows[5].cells[3].innerHTML = this.taxes[24]/100;
		total = this.taxes[4]/100 + this.taxes[14]/100 + this.taxes[24]/100;
		taxTable.rows[5].cells[4].innerHTML = total.toFixed(2);

		taxTable.rows[6].cells[0].innerHTML = "RT";
		taxTable.rows[6].cells[1].innerHTML = this.taxes[5]/100;
		taxTable.rows[6].cells[2].innerHTML = this.taxes[15]/100;
		taxTable.rows[6].cells[3].innerHTML = this.taxes[25]/100;
		total = this.taxes[5]/100 + this.taxes[15]/100 + this.taxes[25]/100;
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

		for (var i=27; i<this.taxes.length; i+=5) {
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
	/*
	loadDemands(demandRates, demandLevels) {
		this.demandRates = demandRates;
		this.demandLevels = demandLevels;
	}
	*/
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
		//console.log(this.townDemo);
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

	nationalPay(payInfo) {
		console.log("set national pay levels")
		this.nationalPayDemos = payInfo;
		console.log(this.nationalPayDemos);
	}

	demandPrice(qty, productID) {
		//console.log("add qty ogf " + qty)
		//var nationalPayDemos = [0, 1, 1.25, 1.75, 3, 8, 12, 27, 80, 523, 1024, 2768];
		var currentSupply;
		if (this.loadedProduct != productID) {
			console.log("load the demands");
			console.log("1079," + this.objID + "," + productID);
			this.productDemandLevels = [0, 1, 2, 3, 4, 0, 0, 0, 0, 0, 0, 0];
			let me = this;
			loadData("1079," + this.objID + "," + productID, function(x) {
				let rtnVals = x.split(",");
				for (var i=1; i<11; i++) {
					console.log("set " + me.productDemandLevels[i] + " to " + rtnVals[i+3]/100)
					me.productDemandLevels[i] = rtnVals[i+3]/100;
				}
				currentSupply = x[1];
				});
			this.loadedProduct = productID;
			//this.productDemandLevels = [0, 0.9, 0.8, 0.7, 0.6, 0.5, 0.4, 0.3, 0.2, 0.1, 0, 0];
		} else {
			this.productDemandLevels = [0, 0.9, 0.8, 0.7, 0.6, 0.5, 0.4, 0.3, 0.2, 0.1, 0, 0];
		}
		console.log(this.productDemandLevels);
		console.log(this.nationalPayDemos);
		var totalSupply = [];
		var totalDemand = [];
		currentSupply = 375000 + parseInt(qty);

		// calculate demand levels based on population, city income levels, and demand levels
		let popLvls = [].fill(0,this.incomeLvls.length);
		for (let i=0; i<this.incomeLvls.length; i++) {
			//console.log(this.incomeLvls[i] + " * " + this.population + " * " + productDemandLevels[i]);
			this.incomeLvls[i]*this.population;

			totalDemand[i] = this.incomeLvls[i]*this.population*this.productDemandLevels[i]/100;
		}

		//console.log(totalDemand);
		let remSupply = currentSupply;
		let lastSupply = 0;
		let lastInterval = 0;
		let remDemand = 1;
		// Assign the supply the the different brackets from the top down and see what is left
		for (i=this.incomeLvls.length-1; i>0; i--) {
			lastSupply = Math.min(remSupply, totalDemand[i]);
			remSupply -= lastSupply;
			remDemand = totalDemand[i] - lastSupply;
			//console.log(i + ": Rem Dem = " + remDemand + " ->> " + totalDemand[i] + " - " + lastSupply);
			if (remDemand > 0) {
				lastInterval = i;
				break;
			}
		}

		console.log(i);
		console.log(totalDemand[i]);
		// interpolate last interval with remaining supply
		return Math.round((this.nationalPayDemos[i+1]-(this.nationalPayDemos[i+1]-this.nationalPayDemos[i])*(totalDemand[i]-remDemand)/totalDemand[i])*100)/100;
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
		this.origin = details[11];
		this.salesTax = details[16];
	}

	renderSummary(target) {
		// product box is 100w x 120h
		var thisDiv = addDiv(null, 'offerHolder', target);
		productArray[this.productID].renderSummary(thisDiv);

		//thisDiv.setAttribute("data-unitid", this.unitID);

		thisDiv.buyBox = addDiv("", "offerTranBox", thisDiv);

		thisDiv.statBox1 = addDiv("", "offerStatBox", thisDiv);
		thisDiv.statBox2 = addDiv("", "offerStatBox", thisDiv);
		thisDiv.statBox3 = addDiv("", "offerStatBox", thisDiv);
		thisDiv.statBox4 = addDiv("", "offerStatBox", thisDiv);
		thisDiv.statBox5 = addDiv("", "offerStatBox", thisDiv);

		thisDiv.statBox1.innerHTML = "Q: " + this.qty;
		thisDiv.statBox2.innerHTML = "P: " + this.price;
		thisDiv.statBox3.innerHTML = "U: " + this.quality;
		thisDiv.statBox4.innerHTML = "O: " + this.pollution;
		thisDiv.statBox5.innerHTML = "R: " + this.rights;

		let baseCost = this.qty * this.price;
		let stCost = baseCost * this.salesTax/10000;
		let importCost = baseCost * 0;
		let totalCost = baseCost + stCost + importCost;
		thisDiv.priceBox = addDiv("", "offerPriceBox", thisDiv);
		thisDiv.priceBox.innerHTML = "Base Cost: " + (baseCost) + "<br>Sales Tax: " + stCost + "<br>Import Tax: " + importCost;

		thisDiv.buyBox.innerHTML = "Buy " + this.qty + " for " + totalCost;

		//thisDiv.nameDiv = addDiv("asdf", "sumName", thisDiv);
		//thisDiv.nameDiv.setAttribute("data-boxName", "unitName");

		//thisDiv.nameDiv.innerHTML = this.qty + " @ " + (this.price)/100 + " tax " + (this.salesTax/100);
		return thisDiv;
	}

	renderSale(target) {
		var thisDiv = addDiv(null, 'udHolder', target);
		console.log("render product "+ this.productID);
		productArray[this.productID].renderSummary(thisDiv);

		//thisDiv.setAttribute("data-unitid", this.unitID);

		thisDiv.buyBox = addDiv("", "offerTranBox", thisDiv);
		thisDiv.buyBox.innerHTML = "sell";

		thisDiv.nameDiv = addDiv("asdf", "sumName", thisDiv);
		//thisDiv.nameDiv.setAttribute("data-boxName", "unitName");

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
		// product box is 100w x 120h
		//console.log('draw ' + this.objID + "(" +objNames[this.objID]+")")
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

	renderDtls(target, qty, mCost, lCost, qual, pol) {
		var thisDiv = addDiv(null, 'productHolder', target);
		thisDiv.setAttribute("data-unitid", this.unitID);

		thisDiv.nameDiv = addDiv("asdf", "laborName", thisDiv);
		thisDiv.nameDiv.setAttribute("data-boxName", "unitName");

		addImg("asdf", "productImg", thisDiv); // labor image

		thisDiv.qtyDiv = addDiv("asdf", "productQty", thisDiv);
		thisDiv.qtyDiv.innerHTML = qty.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");

		thisDiv.lCost = addDiv("", "", thisDiv);
		thisDiv.mCost = addDiv("", "", thisDiv);

		thisDiv.lCost.innerHTML = "L: " + Math.round(100*lCost/qty)/100;
		thisDiv.mCost.innerHTML = "M: " + Math.round(100*mCost/qty)/100;

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
		this.playerID = data[0];
		this.moneyCash = data[1] || 0;
		this.moneyGold = data[2] || 0;
		this.money = this.moneyCash;
		this.gold = this.moneyGold;
		this.boosts = new Array();
	}

	set money (x) {
		//console.log("setting player money to " + x);
		this.moneyCash = x;
		document.getElementById("cashBox").innerHTML = "$" + (this.moneyCash/100).toFixed(2);
	}

	set gold (x) {
		//console.log("setting playergold to  " + x);
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
	constructor(dat) {
		this.factoryID = dat[0];
		this.endTime = dat[8];
		this.timeBoost = 0;
		this.material = dat[12];
		this.qty = dat[2];
		this.orderNum = dat[1];
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

	updateOrder() {
		console.log("update order #" + this.orderNum);
		this.factoryID = materialOrder[this.orderNum*18];
		this.endTime = materialOrder[this.orderNum*18+8];
		this.timeBoost = 0;
		this.material = materialOrder[this.orderNum*18+12];
		this.qty = materialOrder[this.orderNum*18+2];
		this.orderNum = materialOrder[this.orderNum*18+1];
		this.showItem(this.displayBox, true);
	}
	/*
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
	}*/

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

class contract {
	constructor(buffer) {
		var dat = new Int32Array(buffer);
		this.spot = dat[0];
		this.owner = dat[1];
		this.time = dat[2];
		this.productID = dat[3];
		this.quantity = dat[4];
		this.minQual = dat[5];
		this.maxPol = dat[6];
		this.maxRights = dat[7];
		this.status = dat[8];
		this.bidLink = dat[11];
		this.price = dat[16];
		this.targetFactory = dat[12];
		this.sentAmt = dat[17];
		this.sentQual = dat[18];
		this.sentPol = dat[19];
		this.sentRights = dat[20];
		this.seller = dat[21];
		this.contractID = dat[26];
	}

	render(trg) {
		var contractContain = addDiv("", "contractSummary", trg);
		contractContain.parentContract = this;
		if (this.contractID == 0) {
			this.renderEmpty(trg, contractContain);
		} else {
			this.renderActive(trg, contractContain);
			contractContain.item = this;
		}

		return contractContain;
	}

	renderActive(trg, contain) {
		productArray[this.productID].renderSummary(contain);

		let summArea = addDiv("", "contractSummary", contain);
		summArea.innerHTML = "C#" + this.contractID +"<br>Price: " + this.price + "<br>" + "Qty: " + this.sentAmt + "/" + this.quantity + "<br>Qual: " +
		 this.sentQual + "/" + this.minQual + "<br>Rights: " + this.sentRights + "/" + this.maxRights + "<br>Pollution: " +
		 this.sentPol + "/" + this.maxPol + "<br>Status:" + this.status;

		contain.addEventListener("click", function (e) {
			e.stopPropagation();
			this.item.renderDetail();
		})

		if (this.status == 1) {
			if (this.bidLink > 0) {
				let bidArea = addDiv("", "", contain);
				bidArea.innerHTML = "BIDS RECEIVED (" + this.bidLink +")";
			}
		}
		if (this.status == 2) {
			let claimButton = newButton(contain, function () {scrMod(this.sendStr)});
			claimButton.sendStr = "1074,"+this.contractID;
			claimButton.innerHTML = "File a claim";
		}

		if (this.status == 3 || this.status == 4) {
			let legalButton = newButton(contain, function (e) {
				let contractLegal = useDeskTop.newPane("contractLegal");
				contractLegal.innerHTML = "";

				for (i=0; i<serviceInv.length; i+=2) {
					if ($serviceInv[i] == 1) {

					}
				}
				sendButton = newButton(contractLegal, function() {scrMod(this.sendStr)});
				sendButton.sendStr = "1075,"+this.contractID;
			});

			legalButton.innerHTML = "Add legal support";
		}
	}

	renderEmpty(trg, contain) {
		contain.innerHTML = "Create a new contract";
		contain.trgFactory = this.targetFactory;

		contain.addEventListener("click", function (e) {
			e.stopPropagation();
			contractCreateMenu(this.trgFactory);
		})
	}

	renderDetail() {
		let thisDetail = useDeskTop.newPane("contractDetail");
		thisDetail.innerHTML = "";
		thisDetail.optionArea = null;

		let contain = addDiv("", "contractDetail", thisDetail);
		productArray[this.productID].renderSummary(contain);

		contain.parentContract = this;

		let summArea = addDiv("", "contractSummary", contain);
		summArea.innerHTML = "Price: " + this.price/100 + "<br>" + "Qty: " + this.sentAmt + "/" + this.quantity + "<br>Qual: " +
		 this.sentQual + "/" + this.minQual + "<br>Rights: " + this.sentRights + "/" + this.maxRights + "<br>Pollution: " +
		 this.sentPol + "/" + this.maxPol + "<br>Status: " + this.status;

		 // check player factories that provide this item
		if (this.status == 1) {
			// accepting bids for the contract
			if (this.owner == thisPlayer.playerID) {
				//textBlob("", contain, "Cancel taking bids or view bids and stuff");
				let cancelButton = newButton(contain);
				cancelButton.sendStr = "1076,"+this.contractID;
				cancelButton.innerHTML = "Cancel taking bids or view bids and stuff";
				cancelButton.addEventListener("click", function () {scrMod(this.sendStr)});

				contain.bidArea = addDiv("", "stdFloatDiv", contain);

				// Load the bid info
				let bidDat = scrMod("1069,"+this.contractID);
				loadData("1069,"+this.contractID, function (x) {
					contractBids(x.split(","), contain.bidArea);
				});

			} else {
				textBlob("", contain, "Bid on contract - set your bid price" + this.owner + "/" + thisPlayer.playerID);
				contain.priceBox = priceBox(contain, "0.00");

				var submitBid = newButton(contain, function () {scrMod("1068,"+this.parentNode.parentContract.contractID + "," + thisPlayer.playerID + ","+this.parentNode.priceBox.value)});
				submitBid.innerHTML = "Submit Bid";

				if (thisDetail.optionArea == null) thisDetail.optionArea = addDiv("", "stdFloatDiv", thisDetail);
				console.log("# checks:" + playerFactories.length);
				for (var i=0; i<playerFactories.length; i++) {
					let check = playerFactories[i].prod.indexOf(this.productID);
					console.log("Check factory " + i + "for product " + this.productID + " with a result of " + check);
					console.log(playerFactories[i].prod);
					if (check > -1) {
						// show the factories that provide this with an option to send
						playerFactories[i].prodDetail(thisDetail.optionArea, check);
					}
				}
			}
		}
		else if (this.status == 2) {
			// contract is active
			console.log("stat 2: " + this.seller + "/" + thisPlayer.playerID + ", " + this.owner + "/" + thisPlayer.playerID);
			if (this.seller == thisPlayer.playerID) {
				let sendButton = newButton(contain, function () {
					if (thisDetail.optionArea == null) thisDetail.optionArea = addDiv("", "stdFloatDiv", thisDetail);
					console.log("# checks:" + playerFactories.length);
					for (var i=0; i<playerFactories.length; i++) {
						let check = playerFactories[i].prod.indexOf(this.parentContract.productID);
						console.log("Check factory " + i + "for product " + this.parentContract.productID + " with a result of " + check);
						console.log(playerFactories[i].prod);
						if (check > -1) {
							// show the factories that provide this with an option to send
							//this.factoryID + "," + prodIndex + "," + contractID;
							playerFactories[i].itemBar(thisDetail.optionArea, check, "1072," + playerFactories[i].objID + "," + check + "," + this.parentContract.contractID);
						}
					}
				});
				sendButton.parentContract = this;
				sendButton.innerHTML = "Send Products";
			}
			else if (this.owner == thisPlayer.playerID) {
				console.log("Detail info for the owner of the contract");
				let claimButton = newButton(contain, function () {scrMod(this.sendStr)});
				claimButton.sendStr = "1074,"+this.contractID;
				claimButton.innerHTML = "File a claim";
			}
		}

		/*
		let leaveButton = newButton(contain, function () {
			scrMod("1065," + this.parentContract.contractID);
		})
		leaveButton.innerHTML = "Leave the Contract";*/
	}
}

class invoice {
	constructor(dat) {
		console.log(dat);
		this.invInfo = new Int32Array(dat.buffer.slice(0,80));
		this.taxInfo = new Uint16Array(dat.buffer.slice(80, 140));
		this.instances = [];
		console.log(this);
	}

	renderFSum(trg) {
		let contain = addDiv("", "invoiceContain", trg);
		contain.innerHTML = "QTY: " + this.invInfo[2] + " of product " + this.invInfo[1] + "  with a total cost of " + this.invInfo[12] +
		". Unit cost is " + this.invInfo[3];
		contain.parentInvoice = this;

		contain.addEventListener("click", function (e) {
			console.log("click on FSum");
			console.log(this.parentInvoice);
			e.stopPropagation();
			this.parentInvoice.renderDetail()});

		return contain;
	}

	renderDetail() {
		console.log("invoice dtl");
		console.log(this);
		let thisDetail = useDeskTop.newPane("invDetail");

		let contain = addDiv("", "invoiceContain", thisDetail);
		contain.innerHTML = "QTY: " + this.invInfo[2] + " of product " + this.invInfo[1] + "  with a total cost of " + this.invInfo[12] +
		". Unit cost is " + this.invInfo[3];

		let payButton = newButton(thisDetail, function () {
			scrMod(this.sendStr);
		});
		payButton.sendStr = "1077,"+this.invInfo[9];
	}
}

class bid {
	constructor(dat) {
		this.bidPlayer = dat[1];
		this.bidPrice = dat[2]/100;
		this.bidQual = dat[3];
		this.bidPol = dat[4];
		this.bidRts = dat[5];
		this.bidTime = dat[6];
		this.contractID = dat[7];
		this.sentTime = dat[8];
		this.exTime = dat[9];
		this.prodID = dat[10];
		this.quantity = dat[11];
		this.status = dat[18];
		this.bidID = dat[20];
	}

	renderSummary(trg) {
		let contain = addDiv("", "", trg);
		contain.bidItem = this;
		contain.innerHTML = "Bid (#" + this.bidID +")" + this.bidPrice + " on " + this.quantity + " of " + this.prodID;
		productArray[this.prodID].renderSummary(contain);


		contain.addEventListener("click", function (e) {
			e.stopPropagation();
			let bidDtl = useDeskTop.newPane("bidDetail");
			this.bidItem.renderDetail(bidDtl);
		});

		return contain;
	}

	renderDetail (trg) {
		let contain = addDiv("", "", trg);
		contain.bidItem = this;
		productArray[this.prodID].renderSummary(contain);

		contain.qty = addDiv("", "", contain);
		contain.qty.innerHTML = "Bid Price: " + this.bidPrice/100 + "<br>" +
		"Bid Status: " + this.status;
	}


	renderDecision (trg) {
		let summary = this.renderSummary(trg);

		let acceptButton = newButton(summary);
		acceptButton.sendStr = "1070,"+this.bidID;
		acceptButton.innerHTML = "Accept";
		acceptButton.addEventListener("click", function (e) {
			e.stopPropagation();
			scrMod(this.sendStr);
		});
	}
}
