class object {
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
					//console.log(this.instances[i]);
					//console.log(this.instances[i].updateFunction);
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
		this.taxes.push(6,1,2,25);  // test sting
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
			let me = this;
			thisDiv.updateFunction = function (x) {
				console.log("update summary");
				me.renderSummary(x)};

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
		event.stopPropagation();
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

			let button = newButton(container, function (e) {
				e.stopPropagation();
				console.log(this.sendStr);
				saleWindow(prodIndex, this.parentNode.slide.slide.value, this.factoryID, this.sendStr);
				//scrMod(this.parentNode.sendStr + "," + this.parentNode.slide.slide.value);
			});
			button.factoryID = this.factoryID;
			button.sendStr = sendStr;
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
		trg.innerHTML = "";
		this.factoryOrders = new Array();
		for (var i=0; i<this.materialOrder.length; i+=28) {
			console.log(this.materialOrder.slice(i, i+28));
			this.factoryOrders.push(new factoryOrder(this.materialOrder.slice(i, i+28)));
			//factoryOrders.push(new factoryOrder('.$postVals[1].', materialOrder[i], materialOrder[i+1], materialOrder[i+2], i/3));
		}

		showOrders(trg, this.factoryOrders);
	}

	showLabor(trg) {
		let factoryLabor = new Array();
		factoryLabor.push(new laborItem({objID:0, pay:0, ability:0, laborType:0}));
		for (var i=0; i<this.labor.length; i+=10) {
			factoryLabor.push(new laborItem({objID:(this.labor[i]/10+1), pay:(this.labor[i+5]), ability:(this.labor[i+8]), laborType:this.labor[i]}));
		}

		trg.innerHTML = "";

		for (var i=1; i<factoryLabor.length; i++) {
			let laborItem = factoryLabor[i].renderFire(factoryDiv.laborSection.aassigned, "1059,"+i+","+this.objID);
			if (factoryLabor[i].laborType > 0) {
				} else {
				}
			//let itemNum = i;
			laborItem.sendStr = "1023,"+this.objID+","+i
			laborItem.addEventListener("click", function () {scrMod(this.sendStr)});
		}
	}

	showOutputs(trg) {
		trg.innerHTML = "";
		console.log(this.productStores);
		console.log(this.prodDtls);
		for (var i=0; i<5; i++) {
			if (this.productStores[i]>0) {
				//productArray[this.productStores[i]].renderQty(trg, this.productStores[i+5]);
				productArray[this.prod[i]].renderDtls(trg, this.productStores[5+i], this.prodDtls[i*5+4], this.prodDtls[i*5+3], 0, 0);
			}
		}
	}

	setProdRate(rate, trg) {
		this.currentRate = rate;
		trg.innerHTML = "Rate: " + this.currentRate + "<br>Lifetime Earnings: $TBD<br>Period Earnings: $TBD";
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
		console.log("show sales" + this.factorySales.length);
		let oList = [];
		for (var i=0; i<this.factorySales.length; i+=25) {
			oList.push(new offer(this.factorySales.slice(i, i+25)));
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

	startProduction(param, trg) {
		setupPromise("1028,"+this.objID+","+param).then(v => {
			let result = setArrayInts(v.split(","));
			console.log(result);
			let fProduction = new factoryProduction(result[0], result[1], result[2], result[3]);
			trg.parentNode.parentNode.prodContain.innerHTML = "";
			let fProductionBox = fProduction.render(trg.parentNode.parentNode.prodContain);

			selFactory.materialInv = result.slice(4);
			selFactory.showInventory(factoryDiv.reqBox.stores);
		});
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

class shipment {
	constructor(dat) {
		this.status = dat[0];
		this.prodID = dat[1];
		this.qty = dat[2];
		this.qual = dat[4];
		this.poll = dat[5];
		this.rights = dat[6];
		this.sentTime = dat[7];
		this.invoiceNum = dat[9];
		this.delTime = dat[11];
		this.matCost = dat[14];
		this.labCost = dat[15];
		this.fromFac = dat[16];
		this.trgCity = dat[17];
		this.cityPop = dat[20];
		this.price = -1;
		this.taxRates = [];
		this.demands = [];

		this.instances = [];

		this.nationalDemos = dat.slice(21, 33); // 12 items national pay demographics
		let tmpGroups = dat.slice(33, 43); // 10 items product demand by decile
		this.supplyHead = dat.slice(43, 46); // 3 items supply head ???
		let tmpDemand = dat.slice(46, 56); // 10 items income groups levels
		console.log(dat);
		console.log(tmpDemand);

		let tmpA = [0];
		this.incomeGroups = tmpA.concat(tmpGroups);
		this.incomeGroups.push(0);


		this.productDemand = [];
		this.productDemand[0] = 0;

		for (let i=0; i<tmpDemand.length; i++) {
			this.productDemand[i+1] = tmpDemand[i]*this.cityPop/100;
		}
		this.productDemand.push(0);
	}

	update(dat) {
		this.status = dat[0];
		this.prodID = dat[1];
		this.qty = dat[2];
		this.qual = dat[4];
		this.poll = dat[5];
		this.rights = dat[6];
		this.sentTime = dat[7];
		this.invoiceNum = dat[9];
		this.delTime = dat[11];
		this.matCost = dat[14];
		this.labCost = dat[15];
		this.fromFac = dat[16];
		this.trgCity = dat[17];

		for (var i=0; i<this.instances.length; i++) {
			console.log(this.instances[i])
			this.instances[i].updateFunction(this.instances[i]);
		}
	}

	renderSummary(trg) {
		let container;
		console.log(trg.divType)
		if (trg.divType == "summary") {
			container = trg;
			container.innerHTML = "";
			if (this.status == 99) {
				container.parentNode.removeChild(container);
				return;
			}
		} else {
			if (this.status == 99) return;
			container = addDiv("", "shipContain", trg);
			this.instances.push(container);
		}
		console.log(this);

		container.divType = "summary";

		productArray[this.prodID].renderDtls(container, this.qty, this.matCost, this.labCost, this.qual, this.poll);
		container.shipment = this;

		container.arriveTime = addDiv("", "", container);
		//console.log(this.delTime);
		var currTime = new Date().getTime();
		let shipStatus = ["unknown", "Not Paid", "Paid", "In transit", "Delivered"];
		let d = new Date(this.delTime*1000);

		if (this.delTime*1000 <= currTime && this.status == 3) this.status = 4;

		container.arriveTime.innerHTML = "<br>Status: " + shipStatus[this.status] + "<br> " + d.getDate() + " / " + (d.getMonth()+1) + " / " + d.getFullYear() + "<br>"+
			d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();

		container.dest = addDiv("", "", container);
		container.dest.innerHTML = "city " + this.trgCity;

		console.log("This price is " + this.price);
		if (this.price < 0) {
			this.price = productPrice(this.qty, 0, this.nationalDemos, this.productDemand, this.incomeGroups, 0);
		}
		console.log("This price is " + this.price);

		container.price = addDiv("", "shipPrice", container);
		container.price.innerHTML = "$ " + this.price.toFixed(2);

		container.fromFac = addDiv("", "", container);
		container.fromFac.innerHTML = "From: " + this.fromFac;

		container.addEventListener("click", function (e) {
			e.stopPropagation();
			this.shipment.renderMenu()});

		container.instanceType = "summary";
		container.parentItem = this;
		container.updateFunction = function () {
			this.parentItem.renderSummary(this);
		}
	}

	renderMenu() {
		let dtlWindow = useDeskTop.newPane("shipmentDetail");
		dtlWindow.innerHTML = "";
		dtlWindow.shipment = addDiv("", "stdContain", dtlWindow);
		dtlWindow.factory = addDiv("", "stdContain", dtlWindow);
		dtlWindow.taxes = addDiv("", "stdContain", dtlWindow);

		if (this.taxRates.length == 0) {
			// get the tax rates
			getASync("1086,"+this.invoiceNum).then(v => {
			this.taxRates = v.split(",");
			taxTable(this.taxRates, dtlWindow.taxes);
			});
		}
		if (this.demands.length == 0) {
			let returnVal = getASync("1087," + this.trgCity + "," + this.prodID).then(v => {
				let tmpA = v.split(",");
				this.demands = tmpA.slice(0, 12);
				this.cityIncome = tmpA.slice(12, 24);
				this.nationalIncome = tmpA.slice(24,36);
				this.population = tmpA.slice(36);
				console.log(v);
				console.log(this.demands);
				console.log(this.cityIncome);
				console.log(this.nationalIncome);
				console.log(this.population);

				tmpA = this.demands;
				for (let i=0; i<tmpA.length; i++) {
					tmpA[i] *= this.population/100;
				}

				let funcTest = productPrice(this.qty, this.prodID, this.nationalIncome, tmpA, this.cityIncome, 0); //qty, productID, me.nationalPayDemos, tmpA, me.incomeLvls, 0
				console.log(funcTest);
				return v;
			});
		}


		this.renderSummary(dtlWindow.shipment);
		let now = new Date().getTime()/1000;
		if (this.delTime <= now) {
			dtlWindow.sellButton = newButton(dtlWindow.shipment);
			dtlWindow.sellButton.innerHTML = "Sell these goods";
			dtlWindow.sellButton.sendStr = "1080," + this.invoiceNum;
			dtlWindow.sellButton.addEventListener("click", function () {
				scrMod(this.sendStr);
				console.log(useDeskTop.getPane("shipmentDetail"));
				console.log(useDeskTop.getPane("shipmentDetail").parentNode.parentObj);
				useDeskTop.getPane("shipmentDetail").parentNode.parentObj.destroyWindow();
				});
		} else {
			dtlWindow.button1 = newButton(dtlWindow.shipment);
			dtlWindow.button1.innerHTML = "Speed Up Delivery " + (this.delTime - now) + " seconds left";
			dtlWindow.button1.sendStr = "1081,"+this.invoiceNum;
			dtlWindow.button1.addEventListener("click", function  () {
				let transMenu = addDiv("", "", dtlWindow);
				transMenu.innerHTML = "Transportation options for this shipment";
				async function tmpFunc(x) {
					let p;
					p = await loadDataPromise(x);
					console.log(p);
				}
				tmpFunc(this.sendStr).then(v => {
					console.log("did something 409" + v)
				});
			});
		}

	for (var i=0; i<playerFactories.length; i++) {
		if (playerFactories[i].objID == this.fromFac) {
			playerFactories[i].renderSummary(dtlWindow.factory);
			}
		}
	}
}

class city {
	constructor(objDat, laws, taxes) {

		this.objID = objDat[0];
		this.objType = 5;
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
		this.population = objDat[2];
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
		//thisDiv.setAttribute("data-unitid", this.unitID);

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
		containerDiv.taxes = addDiv(null, "cdTax", containerDiv);
		containerDiv.income = addDiv("cityIncome", "cdTax", containerDiv);

		containerDiv.income.innerHTML = "city income chart" + this.nationalPayDemos;

		containerDiv.population = addDiv(null, "cdPop", containerDiv.stats);
		containerDiv.education = addDiv(null, "cdEd", containerDiv.stats);
		containerDiv.affluence = addDiv(null, "", containerDiv.stats);
		containerDiv.region = addDiv(null, "", containerDiv.stats);

		containerDiv.population.innerHTML = "Pop: " + this.details[2];
		containerDiv.education.innerHTML = "Education: " + this.details[14];
		containerDiv.affluence.innerHTML = "Aff: " + this.details[15];
		containerDiv.region.innerHTML = "Region: " + this.details[20];

		taxTable(this.taxes, containerDiv.taxes);

		containerDiv.taxes.taxEx = addDiv("", "taxEx", containerDiv.taxes);
		containerDiv.taxes.taxEx.parentObj = this;
		containerDiv.taxes.taxEx.addEventListener("click", function (e) {
			e.stopPropagation();
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
		var currentSupply;
		console.log("qty " + qty + ", ID: " + productID);

		if (this.loadedProduct != productID) {
			this.productDemandLevels = [0, 1, 2, 3, 4, 0, 0, 0, 0, 0, 0, 0];
			let me = this;

				var assEat = async function(str) {
					console.log("eat the ass");
					let x = await loadDataPromise(str);

					let rtnVals = x.split(",");
					console.log(rtnVals);
					for (var i=1; i<11; i++) {
						//console.log("set " + me.productDemandLevels[i] + " to " + rtnVals[i+3]/100)
						me.productDemandLevels[i] = rtnVals[i+3]/100;
					}

					currentSupply = x[1];
					me.loadedProduct = productID;

					console.log("Population:" + me.population);
					let tmpA = me.productDemandLevels.slice();
					for (let i=0; i<tmpA.length; i++) {
						tmpA[i] *= me.population;
					}

					let funcTest = productPrice(qty, productID, me.nationalPayDemos, tmpA, me.incomeLvls, 0);
					console.log(funcTest);
					return funcTest;
					//console.log(x);
					//return x;
				}


				let returnVal = assEat("1079," + this.objID + "," + productID).then(v => {
					console.log(v);
					return v;
				});
				console.log("return " + returnVal);
				return returnVal;
		} else {
			console.log(this.productDemandLevels);

			let tmpA = this.productDemandLevels.slice();
			for (let i=0; i<tmpA.length; i++) {
				tmpA[i] *= this.population;
			}

			let funcTest = productPrice(qty, productID, this.nationalPayDemos, tmpA, this.incomeLvls, 0);
			console.log(funcTest);
			return funcTest;

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

		thisDiv.qtySelect = slideValBar(thisDiv, "", 0, this.qty);

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

		// MAKE CASE FOR QTY == 0

		qty == 0 ? function () {lCost = 0; mCost = 0} : function ()  {lCost /= qty; mCost /= qty};
		//qty == 0 ? {lCost = 0}, lCost = lCost/qty;
		thisDiv.lCost.innerHTML = "L: " + Math.round(100*lCost)/100;
		thisDiv.mCost.innerHTML = "M: " + Math.round(100*mCost)/100;

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
		hireContain.className = "hireContain";
		if (this.laborType > 0) {
			hireContain.hireButton = newButton(hireContain);
			hireContain.hireButton.className = "hireButton";
			hireContain.hireButton.innerHTML = "Fire!";
			hireContain.hireButton.sendStr = sendStr;

			hireContain.hireButton.addEventListener("click", function (e) {
				e.stopPropagation();
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
		this.transport = data[3] || 0;
		this.money = this.moneyCash;
		this.gold = this.moneyGold;
		this.boosts = new Array();
		this.transOptions = new Array();
		this.transRoutes = new Array();
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
		this.endTime = dat[9];
		this.timeBoost = 0;
		this.material = dat[13];
		this.qty = dat[3];
		this.orderNum = dat[2];
		this.orderID = dat[1];
		this.orderSize = dat.length;

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
		this.factoryID = materialOrder[this.orderNum*this.orderSize];
		this.endTime = materialOrder[this.orderNum*this.orderSize+8];
		this.timeBoost = 0;
		this.material = materialOrder[this.orderNum*this.orderSize+12];
		this.qty = materialOrder[this.orderNum*this.orderSize+2];
		this.orderNum = materialOrder[this.orderNum*this.orderSize+1];
		this.showItem(this.displayBox, true);
	}

	newOrder (trg) {
		textBlob("", trg, "Select which item you want to order");
		let tmpInventory = [];
		console.log("look for factory " + this.factoryID)
		for (let f=0; f<playerFactories.length; f++) {
			console.log(playerFactories[f].objID + " VS " +this.factoryID)
			if (playerFactories[f].objID == this.factoryID) {
				console.log("matched factory " + this.factoryID)
				for (i=0; i<playerFactories[f].materialInv.length; i+=2) {
					tmpInventory.push(new product({objID:playerFactories[f].materialInv[i]}));
				}
			break;
			}
		}

		let invList = new uList(tmpInventory);
		invList.reset();

		trg.orderBox1 = invList.SLsingleButton(trg);
		var orderSelectButton = newButton(trg, function () {scrMod(this.sendStr + ", "+ SLreadSelection(trg.orderBox1))});
		orderSelectButton.sendStr = "1009, " + this.factoryID
		trg.offerContainer = addDiv("", "stdContain", trg);

		orderSelectButton.innerHTML = "Find Offers";
	}

	orderDetails (trg) {
		textBlob("", trg, "Order Details");
		let transportOpts = newButton(trg);
		transportOpts.innerHTML = "arrange transport";
		transportOpts.sendStr = "1093,"+this.factoryID+","+this.orderID;
		transportOpts.addEventListener("click", function () {
			getASync(this.sendStr).then(v => {
				let result = v.split(",");
			})
		});
	}

	showItem (containerBox, boost=true) {
		containerBox.innerHTML = "";
		materialBox(this.material, this.qty, containerBox);
		containerBox.timeBox = addDiv("", "timeFloat", containerBox);
		containerBox.parentObj = this;
		var thisObject = this;
		if (this.material == 0) {
			containerBox.addEventListener("click", function (e) {

			useDeskTop.newPane("xyzPane");
			orderPane = useDeskTop.getPane("xyzPane");
			orderPane.innerHTML = "";

			e.stopPropagation();
			thisObject.newOrder(orderPane);
			});
		} else {
			containerBox.addEventListener("click", function (e) {
				useDeskTop.newPane("xyzPane");
				orderPane = useDeskTop.getPane("xyzPane");
				orderPane.innerHTML = "";

				e.stopPropagation();
				thisObject.orderDetails(orderPane);
			})
		}

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
	constructor(id, endTime, productID, qty, objID) {
		this.objID = objID || 0; // dummy filler
		this.factoryID = id;
		this.endTime = endTime;
		this.timeBoost = 0;
		this.material = productID;
		this.qty = qty;
	}

	boostClock(deltaT) {
		this.timeBoost += deltaT;
	}

	renderSummary(target) {
		let tmpVal = this.render(target);
		return tmpVal;
	}

	render(target, boost=true) {
		console.log("redner prod");
		console.log(this);
		console.log(target);
		//target.innerHTML = "";
		var containerBox = addDiv("", "orderContain", target);
		materialBox(this.material, this.qty, containerBox);
		containerBox.timeBox = addDiv("", "timeFloat", containerBox);
		containerBox.parentObj = this;

		let date = new Date();
		if (this.endTime > Math.floor(date.getTime()/1000)) {this.startClock(containerBox, boost)}
		return containerBox;
	}

	startClock(trg, boost) {
		console.log("redner clock");
		var objectPointer = this;
		trg.clockObj = setInterval(function () {runClock(objectPointer.endTime, trg, objectPointer, function (trgObject) {
			console.log(trg.clockObj);
			if (selFactory.objID == objectPointer.factoryID) {
				for (var i=0; i<5; i++) {
					if (selFactory.productStores[i] == objectPointer.material) selFactory.productStores[i+5] += objectPointer.qty;
				}
				selFactory.showOutputs(factoryDiv.productInvSection);
				//trg.parentNode.removeChild(trg);
				console.log(trg.parentNode);
				console.log(objectPointer);
				objectPointer.qty = 0;
				objectPointer.render(trg.parentNode)
			}
		}, objectPointer.timeBoost)}, 1000);


		if (boost) {
			trg.boostBox = addDiv("", "buildSpeedUp", trg);
			trg.boostBox.innerHTML = "S";

			let useID = this.factoryID;
			trg.boostBox.addEventListener("click", function () {scrMod("1035,"+this.factoryID)});
		}
		return trg
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

class legRoute {
	constructor (dat, arraySpot = 0) {
		dat = setArrayInts(dat);
		console.log(dat);
		console.log("array spot is " + arraySpot);
		// optionID, legNum, routeID, owner, mode, distance, speed, cost/vol, cost/wt, cap-vol, cap-wt, status, vehicle
		this.optionID = dat[0];
		this.legNum = dat[1];
		this.routeID = dat[2];
		this.owner = dat[3];
		this.mode = dat[4];
		this.dist = dat[5];
		this.speed = dat[6];
		this.costVol = dat[7];
		this.costWt = dat[8];
		this.capVol = dat[9];
		this.capWt = dat[10];
		this.stat = dat[11];
		this.vehicle = dat[12];
		this.arraySpot = arraySpot;
	}

	renderOption (trg) {
		let thisOpt = addDiv("", "routeOpt", trg);
		thisOpt.dtl = addDiv("", "", thisOpt);
		thisOpt.dist = addDiv("", "", thisOpt);
		thisOpt.cost = addDiv("", "", thisOpt);
		thisOpt.time = addDiv("", "", thisOpt);

		if (this.owner == 0) thisOpt.dtl.innerHTML = "Item # " + this.optionID + "-> DEFAULT OPTION";
		else thisOpt.dtl.innerHTML = "Item # " + this.optionID + "-> company" + this.owner;
		thisOpt.cost.innerHTML = "Cost: " + this.costWt;

		thisOpt.dist.innerHTML = "Distance: " + this.dist;

		let time = this.dist/this.speed;
		let hrs = parseInt(time/3600);
		let min = Math.floor((time-hrs*3600)/60);
		let sec = time%60;
		let timeStr;
		if (hrs > 0) timeStr = hrs + ' hr : ' + min + ' min : ' + sec + " sec";
		else timeStr = min + ' min : ' + sec + " sec";
		thisOpt.time.innerHTML =  timeStr;

		thisOpt.parent = this;

		return thisOpt;
	}
}
