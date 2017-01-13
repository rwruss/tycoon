actionBox = function(trg, prm, maxPoints) {
	let thisBox = addDiv("", "selectContain", trg);
	thisBox.unitSpace = addDiv("", "selectContain", thisBox);
	thisBox.slider = slideValBar(thisBox, "", 0, maxPoints);
	thisBox.orderButton = optionButton("", thisBox, "100%");

	thisBox.orderButton.addEventListener("click", function() {
		console.log("functin to " + prm);
		let msg = prm + "," + thisBox.slider.slide.value;
		console.log(msg)
		scrMod(msg);
		})

	return thisBox;
}

slideBox = function (trg, maxPoints) {
	let thisBox = addDiv("", "selectContain", trg);
	thisBox.unitSpace = addDiv("", "selectContain", thisBox);
	thisBox.slider = slideValBar(thisBox, "", 0, maxPoints);
	thisBox.selectHolder = thisBox.unitSpace;

	return thisBox;
}

payBox = function (trg, maxPoints) {
	let thisBox = addDiv("", "selectContain", trg);
	thisBox.slider = slideValBar(thisBox, "", 0, maxPoints);

	return thisBox;
}

qtyBox = function (trg, maxPoints) {
	let thisBox = addDiv("", "selectContain", trg);
	thisBox.slider = slideValBar(thisBox, "", 0, maxPoints);

	return thisBox;
}

setSlideQty = function(trg, max) {
	console.log(trg.slider);
	trg.slide.max = max;
	trg.maxVal.innerHTML = max;
}

setSlideVal = function (trg, val) {
	console.log(trg.slider);
	trg.slider.slide.value = val;
	trg.slider.setVal.innerHTML = val;
}

addDiv = function(id, useClassName, target) {
	var newDiv = document.createElement("div");
	newDiv.className = useClassName;
	newDiv.id = id;
	var trg;
	if (target !== null) {
		if (typeof(target) == "string") trg = document.getElementById(target);
		else trg = target;
		trg.appendChild(newDiv);
	}
	return newDiv;
}

addImg = function(id, useClassName, target) {
	var newImg = document.createElement("img");
	newImg.className = useClassName;
	newImg.id = id;
	//alert(target)
	target.appendChild(newImg);

	return newImg;
}

addSelect = function(id, useClassName, target) {
	var trg;
	if (typeof(target) == "string") trg = document.getElementById(target);
	else trg = target;

	var newSelect = document.createElement("select");
	newSelect.className = useClassName;

	trg.appendChild(newSelect);

	return newSelect;
}

confirmBox = function (msg, prm, type, trg, aSrc, dSrc) {
	var boxHolder = addDiv("confirmBox", "cBox", document.getElementsByTagName('body')[0]);
	boxHolder.style.zIndex = zCount+9999;
	zCount++;
	var boxMsg = addDiv("confirmBox", "cBoxM", boxHolder)
	var dButton = addDiv("optionDecline", "cBoxD", boxHolder);


	boxMsg.innerHTML = msg;
	if (type == 2 || 3) {
		var acceptButton = addDiv("optionAccept", "cBoxA", boxHolder);
		if (aSrc) {
			acceptButton.innerHTML = aSrc;
		} else {
			acceptButton.innerHTML = "Accept";
		}
		acceptButton.addEventListener("click", function() {
			console.log("accepted");
			this.parentNode.parentNode.removeChild(this.parentNode);
			scrMod(prm)});
	}

	if (type == 1 || 2) {
		if (dSrc.length > 0) {
			dButton.innerHTML = dSrc;
		} else {
			dButton.innerHTML = "Decline";
		}
	}

	if (trg.length > 0) {
		dButton.addEventListener("click", function() {
			this.parentNode.parentNode.removeChild(this.parentNode);
			killBox(document.getElementById(trg))});
	} else {
		dButton.addEventListener("click", function() {
			this.parentNode.parentNode.removeChild(this.parentNode)});
		}
}

confirmButtons = function (msg, prm, trg, opt, asrc, dsrc) {

	var boxHolder = addDiv(trg+"_confirmButtons", "cButtons", trg);

	var boxMsg = addDiv("confirmBox", "cBoxM", boxHolder);
	var buttonHolder = addDiv(trg+"buttonHolder", "cButtons", boxHolder);

	boxMsg.innerHTML = msg;

	if (opt == 2 || opt == 3) {
		var acceptButton = addDiv("optionAccept", "cBoxA", buttonHolder);
		if (asrc) {
			acceptButton.innerHTML = asrc;
		} else {
			acceptButton.innerHTML = "Accept";
		}
		acceptButton.addEventListener("click", function() {scrMod(prm)});
	}

	if (opt == 1 || opt == 2) {
		var dButton = addDiv("optionDecline", "cBoxD", buttonHolder);
		if (dsrc) {
			dButton.innerHTML = dsrc;
		} else {
			dButton.innerHTML = "Decline";
		}
		dButton.addEventListener("click", function () {killBox(dButton);event.stopPropagation();});
	}
}

optionButton = function (prm, trg, src) {
	var newButton = addDiv("button", "cBoxA", trg);
	//newButton.addEventListener("click", function () {scrMod(prm)})
	newButton.innerHTML = src;
	return newButton;
}

scrButton = function (prm, trg, src) {
	//console.log("Use prm " + prm);
	var newButton = addDiv("button", "button", trg);
	newButton.addEventListener("click", function () {scrMod(prm)})
	newButton.innerHTML = src;
	return newButton;
}

msgBox = function (trg, prm, opt) {
	console.log("opt = " + opt);
	subBox = document.createElement("input");
	if (opt == 0) {

		subBox.style.width="100%";
		subBox.addEventListener("keydown", function (event) {event.stopPropagation()});
		subBox.addEventListener("click", function (event) {event.stopPropagation()});
		trg.appendChild(subBox);
	} else {
		subBox.value = "";
	}
	/*
	box = document.createElement("textArea");
	box.style.width="100%";
	box.addEventListener("keydown", function (event) {event.stopPropagation()});
	box.addEventListener("mousedown", function (event) {console.log(event); this.parentNode.parentNode.setAttribute("draggable", false); });
	box.addEventListener("mouseup", function (event) {console.log(event); this.parentNode.parentNode.setAttribute("draggable", true); });
	box.addEventListener("mouseout", function (event) {console.log(event); this.parentNode.parentNode.setAttribute("draggable", true); });
	trg.appendChild(box);
	*/
	sendButton = addDiv("", "", trg);
	sendButton.innerHTML = "send message";
	sendButton.addEventListener("click", function () {
		//alert("send");
		//alert(msgBox.value);});
	scrMod(prm + "<!*!>" + subBox.value + "<!*!>" + box.value);});
}

killButton = function (trg, src) {
	var newButton = addDiv("button", "button", trg);
	newButton.addEventListener("click", function () {
		this.parentNode.parentNode.removeChild(this.parentNode);
		killBox(document.getElementById(trg))});
}

boxButton = function (prm, trg, src) {
	var newButton = addDiv("button", "button", trg);
	newButton.addEventListener("click", function () {makeBox("assignLeader", prm, 500, 500, 200, 50)})
	newButton.innerHTML = src;
	return newButton;
}

confirmButton = function (msg, prm, trg, src) {
	var newButton = addDiv("button", "button", trg);
	newButton.addEventListener("click", function () {confirmBox(msg, prm, 2, document.getElementsByTagName('body')[0], "Yes", "No")});
	//confirmBox = function (msg, prm, type, trg, aSrc, dSrc)
	newButton.innerHTML = src;
	return newButton;
}

plotDetail = function (obj, trg) {
	var newPlot = document.createElement("div");
	newPlot.className = "plotSummary";

	var descBox = addDiv("descBox", "plotDesc", newPlot);
	descBox.innerHTML = obj.desc;
	var prm1 = "1081,"+obj.id;
	descBox.addEventListener("click", function () {makeBox("plotDtl", prm1, 500, 500, 200, 50)});

	var targetBox = addDiv("targetBox", "plotTarget", newPlot);
	var dtlBox = addDiv("dtlBox", "plotDtl", newPlot);

	trg.appendChild(newPlot);
	return newPlot;
}

plotSummary = function (obj, trg) {
	//console.log("mk a plot sum - " + trg);
	var newPlot = document.createElement("div");
	newPlot.className = "plotSummary";

	var dtlButton = addDiv("", "sumDtlBut", newPlot);
	var prm1 = "1081,"+obj.unitID;
	dtlButton.addEventListener("click", function () {makeBox("plotDtl", prm1, 500, 500, 200, 50)});

	var targetBox = addDiv("targetBox", "plotTarget", newPlot);
	newPlot.dtlBox = addDiv("dtlBox", "plotDtl", newPlot);

	newPlot.dtlBox.innerHTML = "plot Details";
	//console.log("attach the plot to " + trg);
	var actDiv = addDiv("asdf", "sumAct", newPlot.dtlBox);
	actDiv.setAttribute("data-boxName", "apBar");
	actDiv.setAttribute("data-boxUnitID", obj.unitID);

	var optBar = addDiv("", "fullBar", newPlot);
	targetBox.innerHTML = obj.unitName;
	trg.appendChild(newPlot);
	return newPlot;
}



makeTabMenu = function(id, trg) {
	var tabObject = addDiv(id+"_header", "taskHeader", trg);
	var tabCM = addDiv(id+"_tabs", "centeredmenu", trg);
	var tabUL = document.createElement("ul");
	tabUL.id = id+"_tabs_ul";
	tabCM.appendChild(tabUL);
	addDiv(id+"_options", "taskOptions", trg);

	newTabMenu(id);
	return tabObject;
	//<div class="taskHeader" id="task_'.$postVals[1].'_header"></div>
	//<div class="centeredmenu" id="task_'.$postVals[1].'_tabs"><ul id="task_'.$postVals[1].'_tabs_ul"></ul></div>
	//<div class="taskOptions" id="task_'.$postVals[1].'_options"></div>';
}

newTabMenu = function(target) {
	var tabHolder = document.getElementById(target+"_tabs");
	tabHolder.currentSelection = 1;
}

newTab = function(target, count, desc) {
	var tabHead = document.createElement("li");
	tabHead.id = target+"_head"+count;
	document.getElementById(target+"_tabs_ul").appendChild(tabHead);
	tabHead.addEventListener("click", function() {tabSelect(target, count);});
	if (desc) tabHead.innerHTML = desc;
	else tabHead.innerHTML = "Option " + count;

	var tabContent = document.createElement("div");
	tabContent.className = "tabBox";
	tabContent.id = target+"_tab"+count;
	document.getElementById(target+"_options").appendChild(tabContent);

	return tabContent;
}

tabSelect = function(target, selection) {
	var tabHolder = document.getElementById(target+"_tabs");
	document.getElementById(target+"_tab"+selection).style.visibility =  "visible";
	//alert(document.getElementById(target+"_tabs").style.visibility);
	if (tabHolder.currentSelection != selection)	{
		//alert("set " + target+"_tab"+tabHolder.currentSelection + "to 1");
		document.getElementById(target+"_tab"+tabHolder.currentSelection).style.visibility =  "hidden";
	}
	tabHolder.currentSelection = selection;
}

messageBox = function (msg, trg) {
	var boxHolder = addDiv("confirmBox", "cBox", document.getElementsByTagName('body')[0]);
	var boxMsg = addDiv("confirmBox", "cBoxM", boxHolder)
	var dButton = addDiv("optionDecline", "cBoxD", boxHolder);

	boxMsg.innerHTML = msg;
	dButton.innerHTML = "OK";
	dButton.addEventListener("click", function() {
		this.parentNode.parentNode.removeChild(this.parentNode)});
}

reqBox = function (src, trg, have, need) {

		if (have < need) {
			var rscBox = addDiv("confirmBox", "reqBox2", document.getElementById(trg));
		} else {
			var rscBox = addDiv("confirmBox", "reqBox1", document.getElementById(trg));
		}

		addImg(src, "reqImg", rscBox);
		var textDiv = addDiv("a", "reqText", rscBox);
		textDiv.innerHTML = src + ': ' + have + '/' + need;
}

resourceBox = function (id, qty, target) {
	rBox = addDiv(id, "rscQty", document.getElementById(target));
	rBox.innerHTML = id + ' = ' + qty;

	return rBox;
}

taskOpt = function(id, target, prm, desc) {

	var thisOpt = addDiv(id, "tdHolder", target);
	if (desc) thisOpt.innerHTML = desc;

	if (prm) thisOpt.addEventListener("click", function() {scrMod("1026,"+id+","+prm);});
	else thisOpt.addEventListener("click", function() {scrMod("taskDtl", "1026,"+id,500, 500, 200, 50);});
}

textBlob = function (id, target, content) {
	if (typeof(target) == "string") trg = document.getElementById(target);
	else trg = target;

	var thisBlob = addDiv(id, "textBlob", trg);
	thisBlob.innerHTML= content;
	thisBlob.style.width = "100%";

	return thisBlob;
}

optionScreen = function(prm) {
	console.log("create option box");
	targetBox = addDiv("optScreen", "optionScreen", "gmPnl");

	let killButton = addDiv("", "paneCloseButton", targetBox);
	killButton.innerHTML = 'X';

	//var killBut = document.createElement('div');
	//killBut.className = "paneCloseButton";
	//killBut.innerHTML = 'X';
	passClick(prm, targetBox);
}

newBldgOpt = function(id, base, target, desc) {

}

newBldgSum = function(id, target, pctComplete, status) {

}

newTaskDetail = function(id, target, pctComplete, killLink) {
	var thisDetail = addDiv(id, "tdHolder", target);
	addDiv(id+"_prog", "udAct", thisDetail);
	setBarSize(id+"_prog", pctComplete, 150);
	addImg(id+"_img", "tdImg", thisDetail);
	document.getElementById(id+"_img").src = "./textures/borderMask3.png"

	if (!killLink) thisDetail.addEventListener("click", function() {makeBox("taskDtl", "1040,"+id, 500, 500, 200, 50);});
	//alert('New task finished');

}

newTaskSummary = function(id, target, pctComplete) {
	var thisDetail = addDiv("tSum_"+id, "tdHolder", document.getElementById(target));
	addDiv("tSum_"+id+"_prog", "udAct", thisDetail);
	setBarSize("tSum_"+id+"_prog", pctComplete, 150)
	addImg("tSum_"+id+"_img", "tdImg", thisDetail);
	document.getElementById("tSum_"+id+"_img").src = "./textures/borderMask3.png"

	thisDetail.addEventListener("click", function() {makeBox("taskDtl", "1040,"+id, 500, 500, 200, 50);});
	//alert('New task finished');
}

newTaskOpt = function(id, target) {
	var thisDetail = addDiv("taskOpt_"+id, "tdHolder", document.getElementById(target));
	thisDetail.innerHTML = id;
}

unitTaskOpt = function(id, target, desc) {
	var thisOpt = addDiv("utOpt_"+id, "tdHolder", target);
	thisOpt.innerHTML = desc;

	thisOpt.addEventListener("click", function () {
		//makeBox("taskDtl", "1060,"+id, 500, 500, 200, 50);
	scrMod("1060,"+id)});
}

charTaskOpt = function(id, target, desc) {
	var thisOpt = addDiv("utOpt_"+id, "tdHolder", document.getElementById(target));
	thisOpt.innerHTML = desc;

	thisOpt.addEventListener("click", function () {makeBox("taskDtl", "1078,"+id, 500, 500, 200, 50);});
}

newUnitDetail = function(id, target) {

	var holderDiv = document.createElement("div")
	holderDiv.className = "udHolder";
	holderDiv.id = "Udtl_"+id;

	var uDAv = document.createElement("img");
	uDAv.className = "udAvatar";
	uDAv.id = "Udtl_"+id+"_avatar";
	holderDiv.appendChild(uDAv);

	var uDType = document.createElement("div");
	uDType.className = "udType"
	uDType.id = "Udtl_"+id+"_type";
	holderDiv.appendChild(uDType);

	var uDLvl = document.createElement("div");
	uDLvl.className = "udLvl";
	uDLvl.id = "Udtl_"+id+"_lvl";
	holderDiv.appendChild(uDLvl);

	var uDAct = document.createElement("div");
	uDAct.className = "uDAct";
	uDAct.id = "Udtl_"+id+"_act";
	holderDiv.appendChild(uDAct);

	var uDExp = document.createElement("div");
	uDExp.className = "udExp";
	uDExp.id = "Udtl_"+id+"_exp";
	holderDiv.appendChild(uDExp);

	var uDName = document.createElement("div");
	uDName.className = "udName";
	uDName.id = "Udtl_"+id+"_name";
	holderDiv.appendChild(uDName);

	var uDGoto = document.createElement("img");
	uDGoto.className = "udGoto";
	holderDiv.appendChild(uDGoto);

	document.getElementById(target).appendChild(holderDiv);

	return
}

plotSum = function (id, target) {
	var holder = document.createElement("div");
	holder.className = "tdHolder";

	var targets = document.createElement("div");
	targets.className = "stdContain";
	targets.id = "plot_"+id+"_targets";

	var progress = document.createElement("div");
	progress.className = "stdContain";
	progress.id = "plot_"+id+"_progress";

	document.getElementById(target).appendChild(holder);
}

scrSelectBox = function (trg) {
	if (selectedItem)	selectedItem.style.borderColor = "000000";
	selectedItem = trg.parentNode;
	trg.parentNode.style.borderColor = "#FF0000";
	console.log(trg);
}

selectionHead = function (trg) {
	var container = addDiv("", "stdContainer", trg);
	container.left = addDiv("", "stdContainer", container);
	container.center = addDiv("", "stdContainer", container);
	container.right = addDiv("", "stdContainer", container);

	container.left.style.width = "33%";
	container.center.style.width = "33%";
	container.center.style.textAlign = "CENTER";
	container.right.style.width = "33%";

	return container;
}

setBarSize = function(id, pct, full) {
	if (document.getElementById(id)) {
		document.getElementById(id).style.width = full * pct;
		//document.getElementById("Udtl_"+id+"_act").style.color = 150 * pct;
		var colorVal = 255*pct;
		var r = parseInt(255*(1-pct));
		var g = parseInt(255*pct);
		var b = parseInt(0);
		document.getElementById(id).style.background = "rgb(" + r + "," + g + ",0)";
	}
}

laborBox = function (id, target) {
	laborArray[id].renderSummary(target);
	//var thisLabor = addDiv("", "", target);
	//thisLabor.innerHTML = id;
}

orderBox = function (time, rscID, qty, target) {

	var thisOrder = addDiv("", "orderContain", target);
	materialBox(rscID, qty, thisOrder);
	var timeBox = addDiv("", "orderTime", thisOrder);
	if (time > 0) {
		timeBox.runClock = true;
		countDownClock(time, timeBox);
		//thisOrder.innerHTML = rscID + " - " + time + "-" + qty;
	}

	return thisOrder;
}

materialBox = function (id, qty, target) {
	let thisRsc = productArray[id].renderSummary(target);

	thisRsc.qtyDiv = addDiv("asdf", "productQty", thisRsc);

	thisRsc.qtyDiv.innerHTML = qty;
	return thisRsc;

}

bPos = [0,0];
paneBox = function(bName, val, h, w, x, y) {
	var newDiv = document.createElement('div');
	/*
	newDiv.draggable = "true";

	newDiv.addEventListener("drag", function (event) {
		if (event.clientX > 0) {
		//console.log("drag start");
		this.style.left = bPos[0] - bPos[2] + event.clientX;
		this.style.top = bPos[1] - bPos[3] + event.clientY;
		}
	});
	newDiv.addEventListener("dragend", function (event) {
		event = event || window.event;
		//console.log(event + " = " + event.clientX + ", " + event.clientY);
		this.style.left = bPos[0] - bPos[2] + event.clientX;
		this.style.top = bPos[1] - bPos[3] + event.clientY;
	});
	*/
	var killBut = document.createElement('div');
	killBut.className = "paneCloseButton";
	killBut.innerHTML = 'X';

	var newContent = document.createElement('div');
	newContent.className = "paneContent"
	newContent.style.overflow = "auto";

	document.getElementsByTagName('body')[0].appendChild(newDiv);
	newDiv.appendChild(killBut);
	newDiv.appendChild(newContent);
	return newDiv;
}

addtion = function () {
	var trg = document.getElementById("objNum").value;
	var amt = document.getElementById("amt").value;
	unitList.add(trg, "strength", amt);
}

class unitList {

	newUnit (object) {
		if (this["unit_" + object.unitID]) {
			if (this["unit_" + object.unitID].type == object.unitType) {
				this["unit_" + object.unitID].update(object);
			} else {
				delete this["unit_" + object.unitID];
				this.newUnit(object);
			}
		} else {
			switch (object.unitType) {
				case "factory":
					this["unit_" + object.unitID] = new factory(object);
					break;

				default:
					console.log("Unknown Type - " + object.unitType);
					break;
			}
		}
	}

	renderSum(id, target) {
		if (this["unit_"+id]) {
			return this["unit_"+id].renderSummary(target);
			//console.log("Unit " + id + " Summary");
		} else {
			console.log("Unit " + id + " Render Error")
		}
	}

	renderDtl(id, target) {
		if (this["unit_"+id]) {
			this["unit_"+id].renderDetail(target);
		} else {
		}
	}

	renderDtlWork(id, target) {
		if (this["unit_"+id]) {
			console.log(this["unit_"+id]);
			this["unit_"+id].renderDetailWork(target);
		} else {
		}
	}

	change(id, desc, value) {
		if (this["unit_"+id]) {
			//this["unit_"+id].changeAttr(id, desc, value);
			console.log("Change " + this["unit_"+id][desc] + " to " + value);
			this["unit_"+id][desc] = value;

		} else {
		}
	}

	add(id, desc, value) {
		if (this["unit_"+id]) {
			value = parseInt(value) +  this["unit_"+id][desc];
			console.log(parseInt(value) + " + " + this["unit_"+id][desc] + " = " + value);
			console.log(desc + " = " + value);
			this["unit_"+id][desc] = value;
			//this["unit_"+id].changeAttr(id, desc, value);
		} else {
		}
	}

	renderSingleSum(id, target) {
		if (this["unit_"+id]) {
			this["unit_"+id].renderSingleSummary(target);
		}
	}
}




class pane {
	constructor (desc, desktop) {
		//console.log("Make a pane " + this);
		this.element = paneBox(desc, 0, 1000, 600, 250, 250);
		//console.log(this.element.childNodes);
		this.desc = desc;
		this.deskHolder = desktop;
		//this.element.childNodes[0].parentObj = this;
		this.element.parentObj = this;
		this.element.destructFunctions = [];
		this.deskHolder.arrangePanes();
		this.nodeType = "pane";

		this.element.addEventListener("click", function(event) {this.parentObj.toTop()});
		this.element.childNodes[0].addEventListener("click", function (event) {
			//console.log("destroying " + this.parentNode.parentObj.nodeType + "  via " + this);
			for (var i=0; i<this.parentNode.destructFunctions.length; i++) {
				console.log("run desfunc " + i);
				this.parentNode.destructFunctions[i]();
			}
			this.parentNode.parentObj.destroyWindow();
			event.stopPropagation();
			});
		this.element.addEventListener("dragstart", function (event) {
			this.parentObj.toTop();
			event.dataTransfer.setData('application/node type', this);
			bPos = [parseInt(this.offsetLeft), parseInt(this.offsetTop), event.clientX, event.clientY];

			console.log(bPos);
		});
		this.toTop();
	}

	destroyWindow() {
		//console.log("remove " + this.desc)
		this.element.remove();
		//var tmp = this.element.parentNode.removeChild(this.element);
		//console.log(tmp);
		this.deskHolder.removePane(this);
		delete this;
		//console.log("final " + Object.keys(this.deskHolder.paneList));
	}

	toTop() {
		this.deskHolder.paneToTop(this);
	}
}

class hPane extends pane {
	constructor(desc, desktop) {
		super(desc, desktop);
		//console.log("set hPane style for " + this.element);
		this.element.className = "hPane";
	}
}

class regPane extends pane {
	constructor(desc, desktop) {
		super(desc, desktop);
		//console.log("set regPane style for " + this.element);
		this.element.className = "regPane";
	}
}

class deskTop {
	constructor () {
		this.paneList = {};
		//console.log("make list " + this.paneList);
		//console.log("List keys " + Object.keys(this.paneList))
		this.id = "a desktop";
	}

	newPane (desc, type) {
		//console.log("start list " + Object.keys(this.paneList))
		if (this.paneList[desc]) {

			//console.log("already made: " + this.constructor.name + " -> " + Object.keys(this.paneList));
		} else {

			if (type == "hPane") {
				var mkPane = new hPane(desc, this);
				this.paneList[desc] = mkPane;
				//console.log("just made " + desc + " --- "  + Object.keys(this.paneList));
			} else {
				var mkPane = new regPane(desc, this);
				this.paneList[desc] = mkPane;
				//console.log("just made " + desc + " --- "  + Object.keys(this.paneList));
			}
			//console.log("created " + desc);
		}
		this.paneToTop(this.paneList[desc]);
	}

	arrangePanes() {
		var count = 1;
		for (var item in this.paneList) {
			//console.log("arrange " + item + " = " + count);
			this.paneList[item].element.style.zIndex = count;
			count++;
		}
	}

	getPane(desc) {
		if (this.paneList[desc]) {
			//console.log("dound " + desc);
			this.paneToTop(this.paneList[desc]);
			return this.paneList[desc].element.childNodes[1];
		} else {
			//console.log(desc + " does not ex");
		}
	}

	paneToTop(thisPane) {
		console.log("move " + thisPane.desc + " to the top");
		delete this.paneList[thisPane.desc];
		this.paneList[thisPane.desc] = thisPane;
		this.arrangePanes();
	}

	removePane (thisPane) {
		//console.log("Base array " + Object.keys(this.paneList));
		//console.log("remove from " + this.constructor.name + " looking for " + thisPane.desc);
		//this.paneList.splice(thisPane.desc, 1);
		delete this.paneList[thisPane.desc];
		//console.log("current List " + Object.keys(this.paneList) + " leaving a size of " + this.paneList.length);
	}
}

selectButton = function (trg, src, id, others) {
	var newButton = addDiv("button", "button", trg);
	newButton.addEventListener("click", function () {
		selectItem(this, id, others);
		})
	newButton.innerHTML = src;
	//return newButton;
}

var selectedItem, selectedID;
selectItem = function (trg, id, others) {
	if (selectedItem)	selectedItem.style.borderColor = "000000";
	selectedItem = trg.parentNode;
	selectedID = id;
	trg.parentNode.style.borderColor = "#FF0000";
	console.log("selectedID is " + selectedID);
	for (var i=0; i<others.length; i++) {
		unitList.renderSingleSum(id, others[i]);
	}
}

var sortList;
groupSort = function (trg, id, dir, limit) {
	var groupContainer = addDiv("", "stdContain", trg);
	groupContainer.left = addDiv("groupSort_1", "stdContain", groupContainer);
	groupContainer.centerBar = addDiv("", "stdContain", groupContainer);
	groupContainer.right = addDiv("groupSort_2", "stdContain", groupContainer);

	var sortButton = addDiv("", "button", groupContainer.centerBar);
	sortButton.innerHTML = "Assign";
	sortButton.addEventListener("click", function () {sortGroup(groupContainer, dir)});

	sortList = [];
	sortList.moved = [id];
	sortList.limit = limit;
	return groupContainer;
}

groupButton = function (trg, id) {
	var newButton = addDiv("button", "button", trg);
	newButton.innerHTML = "asd";
	newButton.objId = id;
	newButton.addEventListener("click", function () {
			var check = sortList.indexOf(this.parentNode);
			if (check >= 0) {
				this.parentNode.style.borderColor = "#000000";
				sortList.splice(check, 1);
				console.log("found at " + check + ". Size is  " + sortList.length);

				var moveCheck = sortList.moved.indexOf(this.objId);
				if (moveCheck >= 0) sortList.moved.splice(moveCheck, 1);
			} else {
				if (sortList.limit > sortList.moved.length-1) {
					console.log("not found " + check);
					this.parentNode.style.borderColor = "#FF0000";
					sortList.push(this.parentNode);

					sortList.moved.push(this.objId);
				}
			}
			console.log(sortList);
	})
}

sortGroup = function(parent, dir) {
	console.log("ASSIGN THE FOLLOWING: " + sortList)
	for (i=0; i<sortList.length; i++) {
		sortList[i].style.borderColor = "#000000";
		if (sortList[i].parentNode == parent.left) {
			parent.right.appendChild(sortList[i]);
		} else {
			parent.left.appendChild(sortList[i]);
		}
	}
	console.log("Move items " + sortList.moved);
	scrMod(dir + ","+sortList.moved);
	sortList = [];
	sortList.moved = [];

}

var groupList = [];
collect = function (group) {
	var retStr = "";
	for (i=0; i<group.length; i++)  {
		console.log(group[i]);
		retStr = retStr + "," + group[i].id + ',' + group[i].value;
	}
	console.log(retStr);
	return retStr;
}


slideValBar = function (trg, slideID, low, hi) {
	console.log("Add a slide bar to " + trg)
	var contain = addDiv("", "slideContain", trg);
	contain.descr = addDiv("", "slideTitle", contain);

	var minVal = addDiv("", "slideMin", contain);
	minVal.innerHTML = low;

	contain.slide = document.createElement("input");
	contain.slide.type="range";
	contain.slide.min=low;
	contain.slide.max=hi;
	contain.slide.value = "0";
	contain.slide.step = "1";
	contain.slide.id = slideID;
	contain.slide.className = "slideBar";
	contain.appendChild(contain.slide);
	contain.maxVal = addDiv("", "slideMin", contain);
	contain.maxVal.innerHTML = hi;


	contain.setVal = addDiv("", "slideVal", contain);
	contain.setVal.innerHTML = 0;

	minVal.addEventListener("click", function (event) {event.stopPropagation();contain.slide.stepDown(1); contain.setVal.innerHTML = contain.slide.value;});
	contain.addEventListener("click", function() {event.stopPropagation()});
	contain.maxVal.addEventListener("click", function (event) {event.stopPropagation();contain.slide.stepUp(1); contain.setVal.innerHTML = contain.slide.value;});

	//groupList.push(slide);

	contain.slide.addEventListener("input", function() {
		//console.log(contain.setVal)
		contain.setVal.innerHTML = this.value;});
	contain.slide.addEventListener("change", function() {contain.setVal.innerHTML = this.value;});

	return contain;
}

priceBox = function (target, currentPrice) {
	var newBox = document.createElement("input");
	target.appendChild(newBox);
	newBox.type = "number";
	newBox.value = currentPrice || 0.00;

	return newBox;
}

factoryPricing = function (factory, target) {
	console.log(factory);
	console.log("type: " + factory.factoryType);
	var contain = addDiv("", "stdFloatDiv", target);
	var formList = [];

	console.log(factoryArray[factory.factoryType]);
	for (let i=0; i<5; i++) {
		var priceContain = addDiv("", "stdFloatDiv", contain);
		productArray[factoryArray[factory.factoryType].items[i]].renderSummary(priceContain);
		formList.push(priceBox(priceContain, factory.prices[i]/100));
	}

	return formList;
}

updateFactory = function (object) {
	for (i=0; i<playerFactories.length; i++) {
		if (playerFactories[i].objID == object.objID) {
			playerFactories[i].factoryType = object.subType || playerFactories[i].factoryType,
			playerFactories[i].prod = object.prod || playerFactories[i].prod,
			playerFactories[i].quality = object.quality || playerFactories[i].quality,
			playerFactories[i].pollution = object.pol || playerFactories[i].pollution,
			playerFactories[i].rights = object.rights || playerFactories[i].rights,
			playerFactories[i].rate = object.rate || playerFactories[i].rate,
			playerFactories[i].items = object.items || playerFactories[i].items,
			playerFactories[i].prices = object.prices || playerFactories[i].prices;

			console.log(playerFactories[i])
		}
	}
}

countDownClock = function (endTime, target, callback = function () {console.log("default functin")}) {
	target.clockObj = setInterval(function () {runClock(endTime, target, target.clockObj, callback)}, 1000);

	checkNode = target.parentNode;
	while (checkNode) {
		if (checkNode.destructFunctions) {
			console.log("add to " + checkNode);
			checkNode.destructFunctions.push(function () {
				console.log("stop clock");
				clearInterval(target.clockObj);})
			break;
		}
		checkNode = checkNode.parentNode;
	}

	//target.addEventListener("DOMNodeRemoved", function () {console.log("remove");clearInterval(target.clockObj);}, false);
}

runClock = function (endTime, target, object, callback) {

	var date = new Date();
	var remaining = (endTime - Math.floor(date.getTime()/1000));
	if (remaining > 0) {
		//console.log(endTime + " - " + Math.floor(date.getTime()/1000) + " = " + (remaining) );

		var hrs = Math.floor(remaining/3600);
		var mins = Math.floor((remaining - hrs*3600)/60);
		var secs = remaining%60;

		target.innerHTML = ("0" + hrs).slice(-2) + " : " + ("0" + mins).slice(-2) + " : " + ("0" + secs).slice(-2);
	} else {
		target.innerHTML = "";
		clearInterval(target.clockObj);
		console.log("test callbuck");
		callback();
	}
	//if (!target.runClock) clearInterval(target.clockObj);
}

filterDuplicates = function (a) {
	var dupList = [];
	if (dupList.indexOf(a) == -1) {
		dupList.push(a);
		return true;
	}
	return false;
}

function switchGroupsUnlimited(item, group1, group2, function1, function2) {
	console.log(item.parentNode);
	if (item.parentNode == group1) {
		group2.appendChild(item);
	}
	else if (item.parentNode == group2) {
		group1.appendChild(item);
	}
}

function switchGroups(item, group1, group2, emptyItem, group1Limit) {
	//console.log(item);
	if (item.parentNode == group1 && item.getAttribute('ownerObject') != "empty") {
		console.log(item.getAttribute('ownerObject') + " vs " + emptyItem.getAttribute('ownerObject'))
		group2.appendChild(item);
		let emptyObj = emptyItem.cloneNode(true);
		console.log("added empty item with oo of : " + emptyObj.getAttribute('ownerObject') + " from oo of " + emptyItem.getAttribute('ownerObject'))
		group1.appendChild(emptyObj);
	}
	else if (item.parentNode == group2) {
		let numNodes = group1.childNodes.length;
		for (var i=0; i<numNodes; i++) {
			if (group1.childNodes[i].getAttribute('ownerObject') == "empty") {
				//group1.childNodes[i].parentNode.removeChild(group1.childNodes[i]);
				group1.insertBefore(item, group1.childNodes[i]);
				group1.childNodes[i].parentNode.removeChild(group1.childNodes[i+1]);
				//group1.childNodes[i] = item;
				break;
			}
		}
		if (numNodes  < group1Limit)	group1.appendChild(item);
	}
}

function showOrders(materialOrder, factory) {
	orderItems.innerHTML = "";
	for (var i=0; i<materialOrder.length; i+=3) {
		let thisBox = orderBox(materialOrder[i], materialOrder[i+1], materialOrder[i+2], orderItems);
		if (materialOrder[i] == 0) thisBox.addEventListener("click", function () {
			useDeskTop.newPane("xyzPane");
			orderPane = useDeskTop.getPane("xyzPane");
			event.stopPropagation();
			textBlob("", orderPane, "Select which item you want to order");
			invList.reset();
			orderBox1 = invList.SLsingleButton(orderPane);
			orderSelectButton = newButton(orderPane, function () {scrMod("1009, " + factory + ", "+ SLreadSelection(orderBox1))});
			orderSelectButton.innerHTML = "Find Offers";
			offerContainer = addDiv("", "stdContain", orderPane);
			});
	}
}

function showInventory(factory, inventory) {
	console.log("Shw inv");
	if (factory == selectedFactory) {
		reqBox.stores.innerHTML = "";
		textBlob("", reqBox.stores, "Current resource stores:");
		for (var i=0; i<inventory.length; i+=2) {
			materialBox(inventory[i], inventory[i+1], reqBox.stores);
		}
	}
}

function updateMaterialInv(factory, materialInv) {
	if (factory == selectedFactory) {
		showInventory(factory, materialInv);
	}
}
