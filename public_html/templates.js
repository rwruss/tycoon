scrMod = function (val) {
		params = "val1="+val;
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.open("POST", "gameScr.php?gid="+gameID, true);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				document.getElementById("scrBox").innerHTML = xmlhttp.response;
				ncode_div("scrBox");
				}
			}

		xmlhttp.send(params);
		}

loadData = function (val, callback) {
		console.log("loadting data");
		params = "val1="+val;
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.open("POST", "gameScr.php?gid="+gameID, true);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				console.log(xmlhttp.response);
				callback(xmlhttp.response);
				}
			}

		xmlhttp.send(params);
	}

loadDataPromise = function (val) {
		return new Promise(resolve => {
			params = "val1="+val;
			var xmlhttp = new XMLHttpRequest();
			xmlhttp.open("POST", "gameScr.php?gid="+gameID, true);
			xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xmlhttp.onreadystatechange = function() {
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
					console.log(xmlhttp.response);
					resolve(xmlhttp.response);
					}
				}

			xmlhttp.send(params);
		})
	}

setupPromise = async function (val) {
	let p;
	p = await loadDataPromise(val);
	return p;
}

arrayUnique = function (v, i, s) {
	return s.indexOf(v) === index;
}

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

newButton = function(trg, action) {
	button1 = addDiv("button1", "button", trg);
	button1.addEventListener("click", action);

	button1.innerHTML = "button";

	return button1;
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
		console.log("kill the pane");
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
		console.log("set " + target+"_tab"+tabHolder.currentSelection + "to 1");
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
}

materialBox = function (id, qty, target) {
	console.log("show material box for produ " + id);
	let thisRsc = productArray[id].renderSummary(target);

	thisRsc.qtyDiv = addDiv("asdf", "productQty", thisRsc);

	thisRsc.qtyDiv.innerHTML = qty;
	return thisRsc;

}

bPos = [0,0];
paneBox = function(bName, val, h, w, x, y) {
	var newDiv = document.createElement('div');

	var killBut = document.createElement('div');
	killBut.className = "paneCloseButton";
	killBut.innerHTML = "X";

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
		this.divEl = paneBox(desc, 0, 1000, 600, 250, 250);
		//console.log(this.divEl.childNodes);
		this.desc = desc;
		this.deskHolder = desktop;
		//this.divEl.childNodes[0].parentObj = this;
		this.divEl.parentObj = this;
		this.divEl.destructFunctions = [];
		//this.deskHolder.arrangePanes();
		this.nodeType = "pane";

		this.divEl.addEventListener("click", function(event) {
			//console.log("move up via click");
			this.parentObj.toTop();
		});

		this.divEl.childNodes[0].addEventListener("click", function (event) {
			console.log("destroying " + this.parentNode.parentObj.nodeType + "  via " + this);
			for (var i=0; i<this.parentNode.destructFunctions.length; i++) {
				console.log("run desfunc " + i);
				this.parentNode.destructFunctions[i]();
			}
			this.parentNode.parentObj.destroyWindow();
			event.stopPropagation();
			});

		//this.toTop();
		//return this.divEl.childNodes[1];
	}

	destroyWindow() {
		//this.divEl.remove();
		this.divEl.innerHTML = "";
		this.divEl.parentNode.removeChild(this.divEl);

		this.deskHolder.removePane(this);
		//delete this;
	}

	toTop() {
		this.deskHolder.paneToTop(this);
	}
}

class menu extends pane {
	constructor(desc, desktop) {
		super(desc, desktop);
		//console.log("set menu style for " + this.divEl);
		this.divEl.className = "menu";
	}
}

class regPane extends pane {
	constructor(desc, desktop) {
		super(desc, desktop);
		//console.log("set regPane style for " + this.divEl);
		this.divEl.className = "regPane";
		//console.log(this.divEl);
		//console.log("created pane with parent of " + this.divEl.parentNode)
		//return this.divEl.childNodes[1];
	}
}

class deskTop {
	constructor () {
		this.paneList = [];
		//console.log("make list " + this.paneList);
		//console.log("List keys " + Object.keys(this.paneList))
		this.id = "a desktop";
	}

	newPane (desc, type="") {
		//console.log("start list " + Object.keys(this.paneList));
		var newPaneSpot;
		newPaneSpot = this.paneList.length;
		let foundPane = false;
		for (let i=0; i<this.paneList.length; i++) {
			if (this.paneList[i].desc == desc) {
				console.log(this.paneList);
				console.log("already made: " + desc + " -> " + Object.keys(this.paneList));
				newPaneSpot = i;
				foundPane = true;
				break;
			}

		}
		//console.log(newPaneSpot);
		if (!foundPane) {
			if (type == "menu") {
				var newPane = new menu(desc, this);
				this.paneList[newPaneSpot] = newPane;
				console.log("just made " + desc + " --- "  + Object.keys(this.paneList));
			} else {
				var newPane = new regPane(desc, this);
				this.paneList[newPaneSpot] = newPane;
				//console.log("just made " + desc + " --- "  + Object.keys(this.paneList));
			}
		}

		/*
		if (this.paneList[desc]) {
			console.log(this.paneList);
			console.log("already made: " + this.constructor.name + " -> " + Object.keys(this.paneList));
		} else {
			if (type == "menu") {
				var mkPane = new menu(desc, this);
				this.paneList[desc] = mkPane;
				console.log("just made " + desc + " --- "  + Object.keys(this.paneList));
			} else {
				var mkPane = new regPane(desc, this);
				//console.log(mkPane);
				this.paneList[desc] = mkPane;
				//console.log("just made " + desc + " --- "  + Object.keys(this.paneList));
			}
			//console.log("created " + desc);
		}*/
		//console.log("Set item " + newPaneSpot + " to top");
		this.paneToTop(this.paneList[newPaneSpot]);
		return this.paneList[newPaneSpot].divEl.childNodes[1];
	}

	arrangePanes(desc, currZ) {
		//console.log(desc + " currZ " + currZ);
		for (var i=0; i<this.paneList.length; i++ ) {
			if (this.paneList[i].desc == desc) {
				this.paneList[i].divEl.style.zIndex = this.paneList.length;
			} else {
				if (this.paneList[i].divEl.style.zIndex > currZ)	{
					//console.log(this.paneList[i].divEl.style.zIndex);
					this.paneList[i].divEl.style.zIndex = parseInt(this.paneList[i].divEl.style.zIndex) - 1;
				}
			}
		}
		/*
				var count = 1;
		for (var item in this.paneList) {
			//console.log("arrange " + item + " = " + count);
			this.paneList[item].divEl.style.zIndex = count;
			count++;
		}*/
	}

	getPane(desc) {
		//console.log(" Set " + desc + " to top");
		for (let i=0; i<this.paneList.length; i++) {
			if (this.paneList[i].desc == desc) {
				this.paneToTop(this.paneList[i]);
				return this.paneList[i].divEl.childNodes[1];
			}
		}
		/*
		if (this.paneList[desc]) {
			this.paneToTop(this.paneList[desc]);
			return this.paneList[desc].divEl.childNodes[1];
		} else {
		}*/
	}

	paneToTop(thisPane) {
		//console.log(thisPane.desc);
		if (this.paneList[this.paneList.length-1].desc != thisPane.desc) {
			//console.log("move " + thisPane.desc + " to the top");
			//delete this.paneList[thisPane.desc];
			//this.paneList[thisPane.desc] = thisPane;

		}
		this.arrangePanes(thisPane.desc, thisPane.divEl.style.zIndex);
	}

	removePane (thisPane) {
		//console.log(this.paneList);
		//console.log(Object.keys(this.paneList));

		//console.log(thisPane.desc + " --> " + this.paneList.length);
		for (let i=0; i<this.paneList.length; i++) {
			//console.log("check item " + i);
			if (this.paneList[i].desc == thisPane.desc) {
				this.paneList.splice(i, 1);
				//console.log("delete spot " + i);
			} else {
				//console.log(this.paneList[i].desc + " != " +  thisPane.desc)
			}
		}
		//delete this.paneList[thisPane.desc];

		//console.log(this.paneList);
		//console.log(Object.keys(this.paneList));

		//let i = this.paneList.indexOf(thisPane.desc);
		//console.log("delete index " + i);
		//this.paneList.splice(i, 1);
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
	var contain = addDiv("", "slideContain", trg);
	contain.descr = addDiv("", "slideTitle", contain);

	contain.minVal = addDiv("", "slideMin", contain);
	contain.minVal.innerHTML = low;

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

	contain.minVal.addEventListener("click", function (event) {
		event.stopPropagation();contain.slide.stepDown(1);
		contain.setVal.innerHTML = contain.slide.value;
		console.log(this.parentNode.slide);

		if (document.createEvent) {
		    event = document.createEvent("HTMLEvents");
		    event.initEvent("change", true, true);
		  } else {
		    event = document.createEventObject();
		    event.eventType = "change";
		  }

		  event.eventName = "change";

		  if (document.createEvent) {
		    this.parentNode.slide.dispatchEvent(event);
		  } else {
		    this.parentNode.slide.fireEvent("on" + event.eventType, event);
		  }

		//this.parentNode.slide.dispatchEvent("change");
	});
	contain.addEventListener("click", function() {event.stopPropagation()});
	contain.maxVal.addEventListener("click", function (event) {
		event.stopPropagation();
		contain.slide.stepUp(1);
		contain.setVal.innerHTML = contain.slide.value;

		if (document.createEvent) {
		    event = document.createEvent("HTMLEvents");
		    event.initEvent("change", true, true);
		  } else {
		    event = document.createEventObject();
		    event.eventType = "change";
		  }

		  event.eventName = "change";

		  if (document.createEvent) {
		    this.parentNode.slide.dispatchEvent(event);
		  } else {
		    this.parentNode.slide.fireEvent("on" + event.eventType, event);
		  }

	});

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

objectClock = function (object, dst, callback = function () {console.log("default functin")}) {
	//dst.timeBox = addDiv("", "timeFloat", dst);
	//target.boostBox = addDiv("", "buildSpeedUp", dst);
	//dst.boostBox.innerHTML = "S";
	dst.clockObj = setInterval(function () {runClock(object.endTime, dst.timeBox, dst.clockObj, callback, object.boost)}, 1000)
}

countDownClock = function (endTime, target, callback = function () {console.log("default functin")}) {
	target.boost = 0;
	target.timeBox = addDiv("", "timeFloat", target);
	target.boostBox = addDiv("", "buildSpeedUp", target);
	target.boostBox.innerHTML = "S";
	target.endTime = endTime;
	target.clockObj = setInterval(function () {runClock(endTime, target, target.clockObj, callback, target.boost)}, 1000);

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

clockBoost = function (target, amount) {
	target.boost += amount;
}

runClock = function (endTime, target, object, callback, boost) {
	//console.log("boost " + boost);
	var date = new Date();
	var remaining = (endTime - boost - Math.floor(date.getTime()/1000));

	if (remaining > 0) {
		//console.log(endTime + " - " + Math.floor(date.getTime()/1000) + " = " + (remaining) );

		var hrs = Math.floor(remaining/3600);
		var mins = Math.floor((remaining - hrs*3600)/60);
		var secs = remaining%60;

		target.timeBox.innerHTML = ("0" + hrs).slice(-2) + " : " + ("0" + mins).slice(-2) + " : " + ("0" + secs).slice(-2);
	} else {
		target.timeBox.innerHTML = "";
		clearInterval(target.clockObj);
		console.log("test callbuck");
		callback(object);
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

/*
function showInventory(factory, inventory) {
	//console.log("Shw inv");
	if (factory == selectedFactory) {
		reqBox.stores.innerHTML = "";
		textBlob("", reqBox.stores, "Current resource stores:");
		for (var i=0; i<inventory.length; i+=2) {
			materialBox(inventory[i], inventory[i+1], reqBox.stores);
		}
	}
}*/

/*
function showLabor(factory, factoryLabor) {
	//console.log(factoryLabor);
	factoryDiv.laborSection.aassigned.innerHTML = "";

	for (var i=1; i<factoryLabor.length; i++) {
		let laborItem = factoryLabor[i].renderFire(factoryDiv.laborSection.aassigned, "1059,"+i+","+factory);
		if (factoryLabor[i].laborType > 0) {
			//let laborItem = factoryLabor[i].renderFire(factoryDiv.laborSection.aassigned, "1059,"+i+","+factory);
		} else {
			//let laborItem = factoryLabor[i].renderFire(factoryDiv.laborSection.aassigned, "1059,"+i+","+factory);
		}
		let itemNum = i;
		laborItem.addEventListener("click", function () {scrMod("1023,"+factory+","+itemNum)});
	}
}*/

function updateMaterialInv(factory, materialInv) {
	if (factory == selectedFactory) {
		showInventory(factory, materialInv);
	}
}

function updateBoostTarget(boostTarget, displayTarget) {
	let clone = boostTarget.cloneNode(true);
	clone.clock = boostTarget.clock;
	console.log(boostTarget.clock.innerHTML);
	//countDownClock(boostTarget.clock.endTime, clone.clock);
	//clone.clock.boostBox.remove();
	displayTarget.appendChild(clone);
	return clone;
}

function incrBox(target) {
	let container = addDiv("", "", target);
	container.setValue = 0;
	container.display = addDiv("container", "slideContain", container);
	container.lgStepDn = addDiv("", "slideMin", container);
	container.smStepDn = addDiv("", "slideMin", container);
	container.smStepUp = addDiv("", "slideMin", container);
	container.lgStepUp = addDiv("", "slideMin", container);

	container.display.innerHTML = "0";
	container.lgStepDn.innerHTML = "<<";
	container.smStepDn.innerHTML = "<";
	container.smStepUp.innerHTML = ">";
	container.lgStepUp.innerHTML = ">>";

	container.lgStepDn.addEventListener("click", function () {incrStep(this, -10);});
	container.smStepDn.addEventListener("click", function () {incrStep(this, -1);});
	container.lgStepUp.addEventListener("click", function () {incrStep(this, 10);});
	container.smStepUp.addEventListener("click", function () {incrStep(this, 1);});

	return container;
}

function incrStep(trg, incr) {
	trg.parentNode.setValue = Math.max(0, trg.parentNode.setValue+incr);
	trg.parentNode.display.innerHTML = trg.parentNode.setValue;
}

function resourceQuery(msg, products, services, nextFunction) {
	var targetPane = useDeskTop.getPane("dialogPane");
	textBlob("", targetPane, msg);

	var productArea = addDiv("", "stdFloatDiv", targetPane);
	var serviceArea = addDiv("", "stdFloatDiv", targetPane);
	var productCheck = true;
	var serviceCheck = true;

	for (var i=0; i<products.length; i+=2) {
		let thisProduct = productArray(productArea, products[i]).renderQty(products[i+1]);
		if (playerProducts[i] < products[i+1]) {
			thisProduct.style.border = "red";
			productCheck = false;
		}
	}

	for (var i=0; i<services.length; i+=2) {
		let thisService = productArray(serviceArea, services[i]).renderQty(services[i+1]);
		if (playerServices[i] < services[i+1]) {
			thisService.style.border = "red";
			serviceCheck = false;
		}
	}

	if (productCheck && serviceCheck) {
		sendButton = newButton(headSection, nextFunction);
		sendButton.innerHTML = "Proceed";
	} else {
		sendButton = newButton(headSection, function () {targetPane.parentObj.destroyWindow();});
		sendButton.innerHTML = "Go back";
	}
}

/*
showProdRequirements = function(trg, productMaterial) {
	trg.innerHTML = "";
	for (var i=0; i<productMaterial.length; i+=2) {
		materialBox(productMaterial[i], productMaterial[i+1], trg);
	}
}*/

/*
showRequiredLabor = function(trg, reqdLabor) {
	trg.innerHTML = "";
	for (var i=0; i<reqdLabor.length; i++) {
		if (reqdLabor[i]>0) laborArray[reqdLabor[i]].renderSimple(trg);
		//laborBox(reqdLabor[i], trg);
	}
}*/

showOrders = function (trg, orderInfo) {
	console.log(orderInfo);
	for (i=0; i<orderInfo.length; i++) {
		orderInfo[i].render(trg);
	}
}

factoryRate = function (trg, rate) {
	trg.innerHTML = "Rate: " + rate;
}

/*
showOutputs = function (trg, productStores) {
	//console.log(productStores);
	trg.innerHTML = "";
	for (var i=0; i<5; i++) {
		if (productStores[i]>0) {
			productArray[productStores[i]].renderQty(trg, productStores[i+5]);
		}
	}
}*/

loadShipments = function (dat, list) {
	for (var i=0; i<dat.length; i+=56) {
		let thisShipment = new shipment(dat.slice(i, i+56));
		console.log(thisShipment);
		list.push(thisShipment);

		//console.log(new shipment(dat.slice(i, i+55)));
		//console.log(list);
	}
	//console.log(dat);
}

showShipments = function (list, trg) {
	for (var i=0; i<list.length; i++) {
		list[i].renderSummary(trg);
	}
}

updateShipment = function(dat, list) {
	for (var i=0; i<list.length; i++) {
		if (list[i].invoiceNum == dat[9]) {
			console.log(list[i]);
			list[i].update(dat);
		}
	}
}

msgSummary = function (trg, fromName, fromID, time, subject, msgStatus, s, e) {
	msgContain = addDiv("", "", trg);

	let msgTime = addDiv("", "", msgContain);
	msgTime.innerHTML = time;

	let msgFrom = addDiv("", "", msgContain);
	//msgFrom.innerHTML = fromName;
	//msgFrom.addEventListener("click", scrMod("1045,"+fromID));

	let msgSubject = addDiv("", "", msgContain);
	msgSubject.innerHTML = subject;

	msgContain.addEventListener("click", scrMod("1046,"+s+","+e));
}

receiveOffers = function(offerDat) {
	console.log(offerDat.length);
	if (offerDat.length == 0) {
		orderPane.offerContainer.innerHTML = "No offers available."
		return};
	offerList = [];
	for (var i=0; i<offerDat.length; i+=26) {
		console.log("offer " + i);
		offerList.push(new offer(offerDat.slice(i, i+26)));
	}

	showOffers = new uList(offerList);
	console.log(offerList);

	orderPane.offerContainer.innerHTML = "";
	showOffers.SLShowAll(orderPane.offerContainer, function(x, y) {
		let offerItem = x;
		let item = x.renderSummary(y);
		item.buyBox.addEventListener("click", function () {scrMod("1010," + selectedFactory + "," +offerItem.objID + "," +  SLreadSelection(orderPane.orderBox1) + "," + this.parentNode.qtySelect.slide.value)});
		});
}

/*
showSales = function(trg, saleDat) {
	//console.log("show sales");
	oList = [];
	for (var i=0; i<saleDat.length; i+=12) {
		oList.push(new offer(saleDat.slice(i, i+12)));
	}
	for (var i=0; i<oList.length; i++) {
		console.log("show offer " + i);
		oList[i].renderCancel(trg);
	}
}*/

class tabMenu {
	constructor (children = new Array()) {
		this.tabNames = children;
		this.renderKids = new Array();
		this.selected = 0;
		this.tabItems = new Array();
	}

	renderTabs(trg) {
		this.tabContainer = addDiv("", "stdFloatDiv", trg);
		this.tabHead = addDiv("", "centeredMenu", this.tabContainer);
		this.tabUL = document.createElement("ul");
		this.tabHead.appendChild(this.tabUL);
		this.tabContent = addDiv("", "tabContentDiv", this.tabContainer);

		for (var i=0; i<this.tabNames.length; i++) {
			let tabItem = document.createElement("li");
			tabItem.innerHTML = this.tabNames[i];
			let trgItem = this;
			let trgNum = i;
			tabItem.addEventListener("click", function () {
				trgItem.selectTab(trgNum);
			});
			this.tabUL.appendChild(tabItem);
			this.tabItems[i] = tabItem;

			let newTab = addDiv("", "tabBox", this.tabContent);
			newTab.style.visibility = "hidden";
			//newTab.innerHTML = "tab " + i;
			this.renderKids.push(newTab);

		}
		this.renderKids[0].style.visibility = "visible";	}

	selectTab(item) {
		this.renderKids[this.selected].style.visibility = "hidden";
		this.renderKids[item].style.visibility = "visible";
		this.selected = item;
	}

	tabFunction (trgTab, functionToAttach) {
		this.tabItems[trgTab].addEventListener("click", functionToAttach);
	}
}

edictBox = function (trg, numOpts) {
	let thisBox = addDiv("", "stdFloatDiv", trg);
	thisBox.desc = addDiv("", "stdFloatDiv", thisBox);
	thisBox.effects = addDiv("", "stdCenterDiv", thisBox.desc);
	thisBox.optionArea = addDiv("", "stdCenterDiv", thisBox);
	thisBox.options = new Array();
	for (i=0; i<numOpts; i++) {
		thisBox.options[i] = addDiv("", "edictButton", thisBox.optionArea);
	}

	return thisBox;
}

showDemoChange = function(trg, item, qty) {
	let container = addDiv("", "demoChanger", trg);
	let itemDiv = addDiv("", "demoChangeItem", container);
	itemDiv.innerHTML = item + "==>";
	let qtyDiv = addDiv("", "demoChangeQty", container);
	qtyDiv.innerHTML = qty;

}

buildParks = function(trg, cityID, effects) {
	let edictItem = edictBox(trg, 1);
	edictItem.cityID = cityID;
	textBlob("", edictItem.desc, "Adding parks will make the citizens in this area happy.  It will also improve health and environment scores.");
	edictItem.options[0].innerHTML = "Build more Parks!";
	edictItem.options[0].addEventListener("click", function () {scrMod("1052,1,"+cityID)});

	for (i=0; i<effects.length; i+=2) {
		showDemoChange(edictItem.effects, effects[i], effects[i+1]);
	}
}

showSchools = function(trg, cityID, factoryID, customInfo) {
	for (var i=0; i<schoolList.length; i++) {
			schoolList[i].renderCitySchools(trg, cityID, factoryID, customInfo[i*3], customInfo[i*3+1], customInfo[i*3+2]);
	}
}

showSchoolsHire = function(trg, cityID, customInfo) {}

edictDetail = function(trg, cityID, effects, desc, buttonDescs) {
	let edictItem = edictBox(trg, buttonDescs.length);
	edictItem.cityID = cityID;
	textBlob("", edictItem.desc, desc);

	for (i=0; i<buttonDescs.length; i++) {
		edictItem.options[i].innerHTML = buttonDescs[i];
	}

	for (i=0; i<effects.length; i+=2) {
		showDemoChange(edictItem.effects, effects[i], effects[i+1]);
	}
}

loadCompanyLabor = function (laborDat) {
	console.log("REDO loadCompanyLabor");
	console.log(laborDat);
	let tmpArray = new Array();
	for (var i=0; i<laborDat.length; i+=30) {
		tmpArray.push(new laborItem(laborDat.slice(i, i+30)));
	}
	return tmpArray;
	/*
	let tmpArray = new Array();
	for (var i=0; i<laborDat.length; i+=11) {
		//console.log("TYPE " + laborDat[i]);
		if (laborDat[i+1]>0)	tmpArray.push(new laborItem({objID:laborDat[i]+100, pay:laborDat[i+6], ability:laborDat[i+9], laborType:laborDat[i+1]}));
	}
	return tmpArray;*/
}

addCompanyLabor = function (laborDat, laborArray) {
	for (var i=0; i<laborDat.length; i+=11) {
		if (laborDat[i+1]>0)	laborArray.push(new laborItem({objID:laborDat[i]+100, pay:laborDat[i+6], ability:laborDat[i+9], laborType:laborDat[i+1]}));
	}
}

loadLaborItems = function(laborDat) {
	let tmpArray = new Array();
	for (var i=0; i<laborDat.length; i+=11) {
		//console.log("TYPE " + laborDat[i]);
		if (laborDat[i+1]>0) tmpArray.push(new laborItem({objID:laborDat[i], pay:laborDat[i+6], ability:laborDat[i+9], laborType:laborDat[i+1]}));
	}

	return tmpArray;
}


loadFactoryLabor = function (laborDat) {
	factoryLabor = new Array();
	factoryLabor.push(new laborItem({objID:0, pay:0, ability:0, laborType:0}));
	for (var i=0; i<laborDat.length; i+=10) {
		factoryLabor.push(new laborItem({objID:(laborDat[i]/10+1), pay:(laborDat[i+5]), ability:(laborDat[i+8]), laborType:laborDat[i]}));
	}
}

factoryLaborDetail = function(thisLabor, factoryID, target) {
	console.log(thisLabor);
	let item = thisLabor.renderSummary(target);
	item.itemNo = 0;

	item.addEventListener("click", function () {
		let emptyLabor = new laborItem([0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]);
		if (thisLabor.laborType > 0) {
			companyLabor.push(thisLabor);
			companyLaborOptions(companyLabor, factoryID, laborTabs.renderKids[0]);
			thisLaborItem = emptyLabor;
		}
	target.innerHTML = "";
	emptyLabor.renderSummary(target);
	thisDiv.payArea.innerHTML = "";
	})
}

laborPaySettings = function(laborItem, factoryID, target) {
	target.innerHTML = "";
	textBlob("", target, "Current pay for this employee");
	thisDiv.laborPay = payBox(target, 1000);
	thisDiv.laborPay.slider.slide.step = ".01";
	setSlideVal(thisDiv.laborPay, laborItem.pay/100);
}

companyLaborOptions = function(laborList, factoryID, trg) {
	console.log(companyLabor);
	trg.innerHTML = "";
	for (let i=0; i<companyLabor.length; i++) {
		//console.log("render " + companyLabor[i].objID);
		let item = companyLabor[i].renderSummary(trg);
		item.addEventListener("click", function () {
			if (thisLaborItem.laborType > 0) {
				console.log("move old down (" + thisLaborItem.objID + ")");
				companyLabor.push(thisLaborItem);
				//let tmp = laborList[this.itemNo];
				//laborList[this.itemNo] = thisLaborItem;
				//thisLaborItem = tmp;
		} else {

		}
		console.log("move item " + companyLabor.indexOf(this.parentObj));
		console.log(companyLabor.splice(companyLabor.indexOf(this.parentObj),1));
		companyLaborOptions(null, factoryID, trg);
		thisLaborItem = this.parentObj;
		thisDiv.laborDescArea.innerHTML = "";
		factoryLaborDetail(thisLaborItem, factoryID, thisDiv.laborDescArea);
		//laborPaySettings(thisLaborItem, factoryID, thisDiv.payArea);
	});
	}
	/*
	console.log(laborList);
	trg.innerHTML = "";
	cLaborList = new uList(laborList);
	cLaborList.SLShowAll(trg, function(x,y,z) {
		let item = x.renderSummary(y);
		item.itemNo = z;
		item.addEventListener("click", function(z) {
			console.log("remove item " + this.itemNo + "(type) " + laborList[this.itemNo].laborType);
			// move the selected item back into the laborList
			if (thisLaborItem.laborType > 0) {
				console.log("move old down");
				laborList.push(thisLaborItem);
				//let tmp = laborList[this.itemNo];
				//laborList[this.itemNo] = thisLaborItem;
				//thisLaborItem = tmp;
			} else {

			}



			console.log("remove item " + this.itemNo)
			thisLaborItem = laborList[this.itemNo];
			laborList.splice(this.itemNo, 1);

			companyLaborOptions(laborList, factoryID, trg);
			console.log("move labor item up");
			thisDiv.laborDescArea.innerHTML = "";

			factoryLaborDetail(thisLaborItem, factoryID, thisDiv.laborDescArea);
			laborPaySettings(thisLaborItem, factoryID, thisDiv.payArea);
		})
	});*/
}

companyLaborList = function(laborList, trg) {
	//console.log(laborList);
	trg.innerHTML = "";
	cLaborList = new uList(laborList);
	cLaborList.SLShowAll(trg, function(x,y,z) {
		let item = x.renderFire(y, "1059,"+x.objID+",0");
		//item.itemNo = z;
		//item.fireDiv.sendStr = "1059,"+z+",0";
		/*
		item.fireDiv.addEventListener("click", function(z) {
			console.log("detail for item " + this.parentNode.itemNo + "(type) " + laborList[this.parentNode.itemNo].laborType);
			console.log("fure " + this.sendStr);
			scrMod(this.sendStr);
		});*/
	});
}

showCityLabor = function(trg, cityID, laborDat) {
	laborList = new Array();
	for (let i=0; i<laborDat.legnth; i+=29) {

	}
	/*
	cLaborList = new uList(laborList);
	cLaborList.SLShowAll(trg, function(x,y,z) {
		let item = x.renderSummary(y);
		item.itemNo = z;
		item.addEventListener("click", function(z) {
			console.log("detail for item " + this.itemNo + "(type) " + laborList[this.itemNo].laborType);
		});
	});*/
}

laborHireList = function(trg, factoryID, laborList) {
	console.log(laborList);
	//let laborItems = loadLaborItems(laborList);

	cLaborList = new uList(laborList);
	cLaborList.SLShowAll(trg, function(x,y,z) {
		sendStr = "0,"+x.objID+","+factoryID+",0";
		let item = x.renderHire(y, x.quality, sendStr);
		item.itemNo = z;
		item.addEventListener("click", function(z) {
			console.log("detail for item " + this.itemNo + "(type) " + laborList[this.itemNo].laborType);
		});
	});
}

laborTypeMenu = function(trg, factoryID) {
	/*
	let newMenu = document.createElement("select");

	for (var i=1; i<laborNames.length; i++) {
		let newItem = document.createElement("option");
		newItem.appendChild(document.createTextNode(laborNames[i]));
		newItem.value = i;
		newMenu.appendChild(newItem);
	}*/
	let newMenu = selectMenu(laborNames);

	newMenu.addEventListener("change", function() {
		let menu = this;
		loadData("1060,"+this.value, function (x) {
			if (x.length > 0) {
				let list = new Array();
				list = x.split(",");
				laborHireList(trg.subTarget, factoryID, loadLaborItems(list));
			} else (trg.subTarget.innerHTML = "no " + menu.options[menu.value-1].text + " available");
		});
	});
	trg.appendChild(newMenu);
}

selectMenu = function(items) {
	//console.log(items);
	let newMenu = document.createElement("select");

	for (var i=1; i<items.length; i++) {
		let newItem = document.createElement("option");
		newItem.appendChild(document.createTextNode(items[i]));
		newItem.value = i;
		newMenu.appendChild(newItem);
	}
	return newMenu;
}

arrayToSelect = function(trg, aList) {
	console.log(aList);
	let newMenu = document.createElement("select");

	for (var i=1; i<aList.length; i++) {
		let newItem = document.createElement("option");
		newItem.appendChild(document.createTextNode(aList[i]));
		newItem.value = i;
		newMenu.appendChild(newItem);
	}

	trg.appendChild(newMenu);
	return newMenu;
}

factoryBuildMenu = function () {
	console.log("factorybuildmenu");
	thisDiv = useDeskTop.newPane("adsfasfdsf");
	//thisDiv = useDeskTop.getPane("adsfasfdsf");
	thisDiv.innerHTML = "Where would you like to build this factory?";

	thisDiv.locationBar = addDiv("", "stdFloatDiv", thisDiv);

	thisDiv.cityDetail = addDiv("", "stdFloatDiv", thisDiv);
	thisDiv.buildingSelect = addDiv("", "stdFloatDiv", thisDiv);
	thisDiv.buildingDetail = addDiv("", "stdFloatDiv", thisDiv);
	thisDiv.buildingInProgress = addDiv("buildMenuInProgress", "stdFloatDiv", thisDiv);
	thisDiv.buildingInProgress.innerHTML = "In Progress";

	locationSelect(thisDiv.locationBar, nationList, 1);
}

transportMenu = function () {
	console.log("transportMenu");
	thisDiv = useDeskTop.newPane("adsfasfdsf");
	thisDiv.innerHTML = "";
	thisDiv.headSection = addDiv("", "stdFloatDiv", thisDiv);
	thisDiv.options = addDiv("", "stdFloatDiv", thisDiv);
	thisDiv.content = addDiv("", "stdFloatDiv", thisDiv);
	//thisDiv = useDeskTop.getPane("adsfasfdsf");
	if (thisPlayer.transport == 0) {
		thisDiv.headSection.innerHTML = "In your company there is problem.  That problem is transport.";
	} else {
		thisDiv.headSection.innerHTML = "You have transport.";
	}

	let rightsButton = newButton(thisDiv.options);
	rightsButton.addEventListener("click", function (e) {
		e.stopPropagation();
		let thisDiv = this.parentNode.parentNode;
		console.log(thisDiv);
		thisDiv.content.innerHTML = "where you can transport stuff";

		// Load access rights list
		if (thisPlayer.transOptions.length == 0) {
			async function tmpFunc(x) {
				let p;
				p = await loadDataPromise("1088");
				console.log(p);
				return p;
			}
			tmpFunc("1088").then(v => {
				let transList = v.split(",");
				thisPlayer.transOptions = setArrayInts(transList);
				console.log(thisPlayer.transOptions);
				routeAccess(thisPlayer.transOptions, thisDiv.content);
			});
		}
	});
	rightsButton.innerHTML = "Manage Access Rights";

	let routesButton = newButton(thisDiv.options);
	routesButton.addEventListener("click", function (e) {
		e.stopPropagation();
		let thisDiv = this.parentNode.parentNode;
		console.log(thisDiv);
		thisDiv.content.innerHTML = "how you can transport stuff";

		// Load player routes list
		async function tmpFunc(x) {
			let p;
			p = await loadDataPromise("1089");
			console.log(p);
			return p;
		}
		tmpFunc("1089").then(v => {
			let transList = v.split(",");
			playerRoutes(setArrayInts(transList), thisDiv.content);
		});
	});
	routesButton.innerHTML = "Manage Routes";

	let createRouteButton = newButton(thisDiv.options);
	createRouteButton.innerHTML = "Create a new Route";
	createRouteButton.addEventListener("click", function (e) {
		e.stopPropagation();
		let thisDiv = this.parentNode.parentNode;
		thisDiv.content.innerHTML = "Select which vehicle to purchase";
		thisDiv.content.sales = addDiv("", "", thisDiv.content);

		// load list of vehicles for sale
		setupPromise("1092").then(v => {
			vehicleList = v.split(",");
			showVehicles(vehicleList, thisDiv.content.sales);
		})
	})
}

showVehicles = function(data, trg) {
	for (var i=0; i<data.length; i+=17) {
		let thisVehicle = addDiv("", "", thisDiv.content);
		thisVehicle.innerHTML = "V# " + data[0];

		thisVehicle.sendStr = "1091,"+data[0];
		thisVehicle.addEventListener("click", function () {
			scrMod(this.sendStr);
		})
		}
}

routeAccess = function(data, trg) {
	console.log(data);
	// area ID, modes, expiry
	let now = new Date().getTime() / 1000;
	for (let i=0; i<data.length; i+=3) {
		console.log(i);
		let thisItem = addDiv("", "", trg);
		if (data[i+2] == 0)	{
			thisItem.innerHTML = "Area " + areaID + " currently unrestricted";
		} else {
			if (data[i+2] > now) {
				let expire = new Date(data[i+2]*1000)
				thisItem.innerHTML = "Area " + data[i] + " expires " + expire.getHours() + ":" + expire.getMinutes() + " on " + expire.getDate() + "/" + (expire.getMonth()+1) + "/" + (expire.getYear()+1900);
			} else {
				thisItem.innerHTML = "Area " + data[i] + " access is expired";
				let renewButton = newButton(thisItem);
				renewButton.addEventListener("click", function (e) {
					e.stopPropagation();
					console.log("renew access");
				})
			}
		}
	}
}

playerRoutes = function (data, trg) {
	trg.innerHTML = data;
	for (let i=0; i<data.length; i+=55) {
		let routeContain = addDiv("", "", trg);

		routeContain.innerHTML = i +": route " + data[i];
		routeContain.route = addDiv("","", routeContain);
		routeContain.vehicle = addDiv("","", routeContain);

		let s = 15;
		let count = 0;
		let str = "";
		let totDist = 0;
		routeContain.route.innerHTML = "No Route";
		console.log(cityList);
		while (data[i+s] > 0 && count <10) {
			console.log(i + " + " + s);
			console.log(data[i+s]-1);
			//str = str + data[i+s] + " -> ";
			str = str + cityList[data[i+s]-1].objName + " -> ";
			s++;
			totDist += data[i+s+10];
			count++;
		}
		routeContain.route.innerHTML = str + " DIST: " + totDist;
		routeContain.vehicle.innerHTML = "vehicle " + data[8] + " @ speed " + data[2];
		let changeRoute = newButton(routeContain);
		changeRoute.data = data.slice(i*55, i*55+55);
		changeRoute.addEventListener("click", function (e) {
			e.stopPropagation();
			routeChangeMenu(this.data, this.parentNode);
		})
		changeRoute.innerHTML = "change Route";
	}
}

routeChangeMenu = function (data, trg) {
	console.log("route Change");
	trg.changeMenu = addDiv("", "stdFloatDiv", trg);

	trg.changeMenu.pricing = addDiv("", "stdFloatDiv", trg.changeMenu);
	trg.changeMenu.pricing.volPrice = slideValBar(trg.changeMenu.pricing, "", 0, 10000);
	console.log(trg.changeMenu.pricing.volPrice)
	trg.changeMenu.pricing.volPrice.value = data[4];

	trg.changeMenu.pricing.wtPrice = slideValBar(trg.changeMenu.pricing, "", 0, 10000);
	trg.changeMenu.pricing.wtPrice.value = data[5];

	trg.changeMenu.nodes = addDiv("", "stdFloatDiv", trg.changeMenu);
	trg.changeMenu.nodes.maxList = 9;
	trg.changeMenu.nodes.nodeList = new Array();
	trg.changeMenu.nodes.subLocs = [0,0,125,0,250,0,375,0,500,0,0,125,125,125,250,125,375,125];
	trg.changeMenu.options = addDiv("", "stdFloatDiv", trg.changeMenu);

	trg.changeMenu.nodes.innerHTML = "selected items:";
	trg.changeMenu.options.innerHTML = "Options:";

	let saveRoute = newButton(trg.changeMenu);
	saveRoute.sendStr = "1090,"+data[0] + ",";
	saveRoute.addEventListener("click", function (e) {
		e.stopPropagation();
		let stopsList = new Array();
		console.log(this.parentNode.pricing.volPrice.slide.value);
		stopsList[0] = this.parentNode.pricing.volPrice.slide.value;
		stopsList[1] = this.parentNode.pricing.wtPrice.slide.value;
		for (let i=0; i<this.parentNode.nodes.nodeList.length; i++) {
			stopsList.push(this.parentNode.nodes.nodeList[i].listVal);
		}
		console.log(this.parentNode.nodes.nodeList);
		console.log(stopsList);
		scrMod(this.sendStr + stopsList.join(","));
	})
	saveRoute.innerHTML = "Save Route";

	let tmpArray = new Array();
	for (let i=0; i<cityList.length; i++) {
		let someCity = cityList[i].renderSummary(trg.changeMenu.options);
		someCity.listVal = cityList[i].objID;
		someCity.addEventListener("click", function () {
			console.log(this.listVal);
			addToRouteList(trg.changeMenu.nodes, this);
		});
		tmpArray[i] = someCity;
	}

	// show existing route
	for (let i=15; i<24; i++) {
		if (data[i] > 0) {
			forceEvent(tmpArray[data[i]-1], "click");
		}
	}
}

addToRouteList = function (trg, item) {
	if (trg.nodeList.length < trg.maxList) {
		console.log(item.listVal);
		let newNode = item.cloneNode(true);
		newNode.listVal = item.listVal;
		newNode.listSpot = trg.nodeList.length;
		newNode.parentList = trg;
		console.log(newNode.listVal);

		newNode.addEventListener("click", function () {
			removeFromRouteList(this);
		})
		trg.appendChild(newNode);

		trg.nodeList.push(newNode);
		renderRouteList(trg);
		}
}

removeFromRouteList = function (item) {
	console.log(item);
	console.log(item.parentList.nodeList.length);
	item.parentList.nodeList.splice(item.listSpot, 1);
	item.parentNode.removeChild(item);
	renderRouteList(item.parentList);
}

renderRouteList = function (trg) {
	console.log("render list");
	for (let i=0; i<trg.nodeList.length; i++) {
		console.log("item : " + i);;
		//trg.nodeList[i].style.position = "absolute";
		trg.nodeList[i].style.left = trg.subLocs[i*2];
		trg.nodeList[i].style.top = trg.subLocs[i*2+1];
		trg.nodeList[i].listSpot = i;
	}
}

factoryHireMenu = function(trg, factoryID) {
	laborTypeMenu(trg, factoryID);
	//locationSelect(trg, nationList, 1);
}

locationSelect = function (trg, itemList, tier, offset=0) {
	// output location selection menus
	let newSelect = listSelectMenu(trg, itemList, offset);
	newSelect.regionTier = tier;
	newSelect.id = "location_"+tier;
	newSelect.addEventListener("change", function() {
		if (this.regionTier < 3) {
			let newTarget = this.parentNode;
			let oldTier = this.regionTier;
			loadData("1061,"+this.value+","+this.regionTier, function (x) {
			if (x.length > 0 || true) {
				let list = new Array();
				list = x.split(",");
				let nextTier = list.splice(0,1);
				let startOffset = list.splice(0,1);
				if (oldTier < 3) locationSelect(newTarget, list, nextTier, startOffset);
				if (nextTier == 3) {
					console.log("NT is " + nextTier)
					let selectCity = newButton(newTarget);
					selectCity.innerHTML = "Select this city";
					selectCity.addEventListener("click", function () {
						let trgSelect = document.getElementById("location_3")
						if (trgSelect.value != "null") {
							loadData("1062,"+document.getElementById("location_3").value, function (x) {
								console.log(x);
								let cityLists = x.split(";");
								console.log(cityLists[0]);
								console.log(cityLists[1]);
								console.log(cityLists[2]);
								//renderCityDetail(newTarget.parentNode.cityDetail, cityLists[0].split(","), cityLists[1].split(","), cityLists[2].split(","));
							});

							buildOptionList(newTarget.parentNode.buildingSelect, newTarget.parentNode.buildingDetail, factoryNames);
							showFactoriesInProgress(newTarget.parentNode.buildingInProgress);
						} else {
							console.log("SELECT A CITY!");
						}
						})
					}
				}
			});
		}
	});
}

buildOptionList = function(trg, detailTrg, bldgList) {
	trg.innerHTML = "";
	let bldgSelect = listSelectMenu(trg, factoryNames, numProducts);
	bldgSelect.addEventListener("change", function () {
		console.log(this.value)
		detailTrg.innerHTML = "";
		let item = factoryList[this.value-numProducts].renderDetail(detailTrg);
		console.log(factoryList);
		let buildButton = newButton(item.buttonDiv);
		buildButton.innerHTML = "build this factory";
		let trgSelect = this;
		buildButton.addEventListener("click", function () {
			console.log(trgSelect.value+","+document.getElementById("location_3").value);
			getASync("1008,"+trgSelect.value+","+document.getElementById("location_3").value).then(v => {
				let r = v.split(",");
				if (r[0] == -1) {
					errorAlert("error in buildOptionList");
					return;
				} else {
					thisPlayer.money = r[1];
					addFactory(r.slice(2));
					showFactoriesInProgress(document.getElementById("buildMenuInProgress"));
				}
			})
			//scrMod("1008,"+trgSelect.value+","+document.getElementById("location_3").value)
		});
	});
}

errorAlert = function (str) {
	alert(str);
}

customSelectMenu = function (trg, itemList, itemNumbers) {
	let newMenu = document.createElement("select");

	let newItem = document.createElement("option");
	newItem.appendChild(document.createTextNode("- Select an Option -"));
	newItem.value = null
	newItem.disabled = true;
	newItem.selected = true;
	newMenu.appendChild(newItem);

	for (var i=0; i<itemList.length; i++) {
		let newItem = document.createElement("option");
		newItem.appendChild(document.createTextNode(itemList[i]));
		newItem.value = itemNumbers[i];
		newMenu.appendChild(newItem);
	}

	trg.appendChild(newMenu);
	return newMenu;
}

listSelectMenu = function (trg, itemList, startCount = 0) {
	/*
	let newMenu = document.createElement("select");

	let newItem = document.createElement("option");
	newItem.appendChild(document.createTextNode("- Select an Option -"));
	newItem.value = null
	newItem.disabled = true;
	newItem.selected = true;
	newMenu.appendChild(newItem);

	for (var i=0; i<itemList.length; i++) {
		let newItem = document.createElement("option");
		newItem.appendChild(document.createTextNode(itemList[i]));
		newItem.value = i+parseInt(startCount);
		newMenu.appendChild(newItem);
	}

	trg.appendChild(newMenu);
	return newMenu;*/
	let startVal = parseInt(startCount);
	let itemNumbers = new Array();
	for (let i=0; i<itemList.length; i++) {
		itemNumbers.push(i+startVal);
	}

	let newMenu = customSelectMenu(trg, itemList, itemNumbers);
	return newMenu;
}

renderCityDetail = function (trg, data, laws, taxes) {
	trg.innerHTML = "";
	tmpCity = new city(data, laws, taxes);
	tmpCity.renderDetail(trg);
}

showContracts = function (dat, trg) {
	console.log(dat);
	for (var i=0; i<dat.byteLength; i+=108) {
		let thisContract = new contract(dat.slice, i, i+108);
		let contractItem = thisContract.render(trg);
	}
}

contractCreateMenu = function (itemID, optionList = invList) {
	console.log("factory " + itemID);
	console.log(optionList);
	contractMenu = useDeskTop.newPane("createContract");
	contractMenu.innerHTML = "";
	textBlob("", contractMenu, "Select what you would like to purchase with this contract");
	contractItemBox = optionList.SLsingleButton(contractMenu);

	let quanBox = addDiv("", "stdFloatDiv", contractMenu);
	let qualBox = addDiv("", "stdFloatDiv", contractMenu);
	let pollBox = addDiv("", "stdFloatDiv", contractMenu);
	let rightsBox = addDiv("", "stdFloatDiv", contractMenu);

	quanBox.innerHTML = "Set Quantity";
	qualBox.innerHTML = "Set Quality";
	pollBox.innerHTML = "Set Pollution";
	rightsBox.innerHTML = "Set Rights";
	let objQ = slideValBar(quanBox, "", 0, 10000);
	let minQual = slideValBar(qualBox, "", 0, 100);
	let maxPoll = slideValBar(pollBox, "", 0, 100);
	let maxRts = slideValBar(rightsBox, "", 0, 100);

	createButton = newButton(contractMenu, function () {
		let tmp = [];
		tmp.push(itemID, SLreadSelection(contractItemBox), objQ.slide.value, minQual.slide.value, maxPoll.slide.value, maxRts.slide.value);
		console.log(tmp);
		scrMod("1066,"+ tmp.join(","))
	});
	createButton.innerHTML = "Issue Contract";
}


showBids = function (bidDat, trg) {
	console.log("Show big list");
	if (bidDat.length == 0) {
		trg.innerHTML = "No open Bids";
		return;
	}
	for (var i=0; i<bidDat.length; i+=21) {
		let thisBid = new bid(bidDat.slice(i, i+21));
		thisBid.renderSummary(trg);
	}
}

contractBids = function (bidDat, trg) {
	console.log(bidDat.length);
	if (bidDat.length < 21) return;
	for (var i=0; i<bidDat.length; i+=21) {
		let thisBid = new bid(bidDat.slice(i, i+21));
		thisBid.renderDecision(trg);
	}
}
var inProgressFactories = [];
addFactory = function (dat) {
	for (var i=0; i<dat.length; i+=41) {
		playerFactories.push(new factory(dat.slice(i, i+41)));
		inProgressFactories.push(new factory(dat.slice(i, i+41)));
	}
}

showFactoriesInProgress = function (trg) {
	trg.innerHTML = "Factories in Progress";
	for (var i=0; i<inProgressFactories.length; i++) {
		inProgressFactories[i].render(trg);
	}
}

updateFactory = function (dat) {
	console.log("update a factoryS")
	//let tmp = new factory(dat);
	let found = true;
	for (i=0; i<playerFactories.length; i++) {
		if (playerFactories[i].factoryID == dat[3]) {
			playerFactories[i].update(dat);
			break;
		}
	}
	if (!found) playerFactories.push(tmp)
}

/*
factoryContracts = function (dat, trg) {
	console.log("fac contracts size is  " + dat.length);
	let startPos = dat[0]+1;
	let invCount = 0;
	for (var i=0; i<dat[0]; i++) {
		let contractHolder = addDiv("", "facContract", trg);
		console.log("make con");
		let thisContract = new contract(new Int32Array(dat.slice(startPos, startPos+27)));
		console.log(thisContract);
		thisContract.render(contractHolder);

		let invStart = dat[0]*27+dat[0]+1+invCount*50;
		//let invBase = new Int32Array(dat.slice(invStart, invStart+14));
		//let invTaxes = new Int16Array(dat.slice(invStart+14))
		console.log("invoices from " + invStart + " to " + (invStart + dat[1+i]*50))
		contractInvoice(dat.slice(invStart, invStart + dat[1+i]*50), contractHolder);
		invCount += dat[i+1];
	}
}*/

contractInvoice = function (dat, trg) {
	console.log("load invoice size " + dat.length);
	console.log(dat);
	for (var i=0; i<dat.length; i+=50) {
		let invBase = new Int32Array(dat.slice(i, i+14));
		let invTax = new Int16Array(dat.slice(i+14, i+50));
		let tmp = new Uint8Array(invBase.byteLength + invTax.byteLength);
		tmp.set(new Uint8Array(invBase.buffer), 0);
		tmp.set(new Uint8Array(invTax.buffer), invBase.byteLength);
		console.log(invBase.byteLength + " + " + invTax.byteLength);
		console.log("invoice datlenght " + (tmp.byteLength));
		let thisInvoice = new invoice(tmp);
		thisInvoice.renderFSum(trg);
	}
}

invoiceList = function (dat) {
	tmpA = [];
	for (var i=0; i<dat.byteLength; i+=140) {
		tmpA.push(new invoice(dat.slice(i, i+140)));
	}
	return tmpA;
}

getFactoriesByProduct = function (factoryList, productID) {
	let tmpA = [];
	for (var i = 0; i<factoryList.length; i++) {
		let check = factoryList[i].prod.indexOf(parseInt(productID));
		console.log("Check factory " + i + "for product " + productID + " with a result of " + check);
		console.log(factoryList[i].prod);
		tmpA.push(check);
		/*
		if (check > -1) {
			// show the factories that provide this with an option to send
			tmpA.push(i)
		}*/
	}
	return tmpA;
}

calcTaxRates = function (prodDat, baseRates) {
	for (var i=11; i<baseRates.length; i+=4) {
		if (prodDat[baseRates[i]] == baseRates[i+2]) {
			baseRates[baseRates[i+1]] += baseRates[i+3];
		}
	}
}

forceEvent = function (target, type) {
	if (document.createEvent) {
			event = document.createEvent("HTMLEvents");
			event.initEvent(type, true, true);
		} else {
			event = document.createEventObject();
			event.eventType = type;
		}

		event.eventName = type;

		if (document.createEvent) {
			target.dispatchEvent(event);
		} else {
			target.fireEvent("on" + event.eventType, event);
		}
}

productPrice = function (qty, productID, nationalPayDemos, productDemand, incomeGroups, currentSupply) {
		var totalSupply = [];
		var totalDemand = [];
		currentSupply = 375000 + parseInt(qty);

		console.log(nationalPayDemos);
		console.log(productDemand);
		console.log(incomeGroups);

		// calculate demand levels based on population, city income levels, and demand levels
		let popLvls = [].fill(0,incomeGroups.length);
		for (let i=0; i<incomeGroups.length; i++) {

			totalDemand[i] = incomeGroups[i]*productDemand[i]/100;
		}

		console.log("total demand");
		console.log(totalDemand);

		let remSupply = currentSupply;
		let lastSupply = 0;
		let lastInterval = 1;
		let remDemand = 1;
		// Assign the supply the the different brackets from the top down and see what is left
		for (i=incomeGroups.length-1; i>0; i--) {
			lastSupply = Math.min(remSupply, totalDemand[i]);
			//console.log(remSupply + ", " + totalDemand[i]);
			remSupply -= lastSupply;
			remDemand = totalDemand[i] - lastSupply;

			console.log(i + ": Rem Dem = " + remDemand + " ->> " + totalDemand[i] + " - " + lastSupply + " / Rem Supply: " + remSupply);

			if (remDemand > 0) {
				lastInterval = i;
				break;
			}
		}

		let tmpCheck = Math.round((nationalPayDemos[lastInterval+1]-(nationalPayDemos[lastInterval+1]-nationalPayDemos[lastInterval])*(totalDemand[lastInterval]-remDemand)/totalDemand[lastInterval])*100)/100;

		console.log("(" + nationalPayDemos[lastInterval+1] + " - (" + nationalPayDemos[lastInterval+1] + " - " + nationalPayDemos[lastInterval] + " ) * (" + totalDemand[lastInterval] + "-" + remDemand + ")/" + totalDemand[lastInterval] + ")*100");
		// interpolate last interval with remaining supply
		return Math.round((nationalPayDemos[lastInterval+1]-(nationalPayDemos[lastInterval+1]-nationalPayDemos[lastInterval])*(totalDemand[lastInterval]-remDemand)/totalDemand[lastInterval])*100)/100;
	}

saleWindow = function (prodIndex, saleQty, factoryID, sendStr) {
	let salePane = useDeskTop.newPane("saleWindow");
	salePane.innerHTML = "";
	salePane.head = addDiv("", "stdFloatDiv", salePane);
	salePane.legArea = addDiv("", "stdFloatDiv", salePane);

	// display the factory summary
	console.log(playerFactories);
	console.log(factoryID);
	for (var i=0; i<playerFactories.length; i++) {
		if (playerFactories[i].factoryID == factoryID) playerFactories[i].renderSummary(salePane.head);
	}

	let sendButton = newButton(salePane.head);
	sendButton.innerHTML = "Place Order";
	sendButton.sendStr = sendStr + "," + saleQty;
	sendButton.addEventListener("click", function () {
		let nuSendStr = "1017," + this.sendStr + "," + selectedRouteList.join(",");
		scrMod(nuSendStr);
	});


	// display the recommended route
	getASync(sendStr + "," + saleQty).then(v => {
		//console.log(v);
		routeDat = v.split(",");
		if (routeDat[0] == -1) {
			console.log("SaleWindow error");
			return;
		}
		routeSelection(routeDat, salePane);
	});
}

routeSelection = function (routeDat, trg) {
	let routeOptionList = [];
	selectedRouteList = [];
	trg.legItems = [];
	trg.opts = addDiv("", "stdFloatDiv", trg);
	trg.opts.innerHTML = "route options";
	for (var i=0; i*13<routeDat.length; i++) {
		// optionID, routeID, owner, mode, distance, speed, cost/vol, cost/wt, cap-vol, cap-wt, status, vehicle
		console.log(routeDat.slice(i*13, i*13+13));
		routeOptionList.push(new legRoute(routeDat.slice(i*13, i*13+13), i));
		selectedRouteList[i*2] = 0;
		selectedRouteList[i*2+1] = 0;
		console.log(routeOptionList);

		// Verify a container for this item
		if (!(routeOptionList[i].legNum in trg.legItems)) {
			trg.legItems[routeOptionList[i].legNum] = addDiv("", "stdFloatDiv", trg.legArea);
			trg.legItems[routeOptionList[i].legNum].innerHTML = "Leg " + routeOptionList[i].legNum;
		}

		let routeOption = routeOptionList[i].renderOption(trg.opts);
		//console.log(selectedRouteList);
		routeOption.addEventListener("click", function () {
			console.log("adjust leg " + this.parent.legNum);
			selectedRouteList[this.parent.legNum*2] = this.parent.optionID; // route ID
			selectedRouteList[this.parent.legNum*2+1] = this.parent.arraySpot; // spot in array
			useDeskTop.getPane("saleWindow").legItems[this.parent.legNum].appendChild(this);
			console.log(selectedRouteList);
		});
	}
}

getASync = async function (val) {
	let r = await loadDataPromise(val);
	return r;
}

setArrayInts = function (a) {
	result = a.map(function (x) {return parseInt(x)})
	return result;
}

taxTable = function (rates, trg) {
	rates = setArrayInts(rates);
	console.log(rates);
	let taxTable = document.createElement("table");
	taxTable.className = "taxTable";
	taxTable.cells = new Array();
	for (let i=0; i<7; i++) {
		let thisRow = taxTable.insertRow(-1);
		for (let j=0; j<5; j++) {
			let thisCell = thisRow.insertCell(-1);
		}
	}

	trg.appendChild(taxTable);
	let total;

	taxTable.rows[0].cells[1].innerHTML = "C";
	taxTable.rows[0].cells[2].innerHTML = "R";
	taxTable.rows[0].cells[3].innerHTML = "N";
	taxTable.rows[0].cells[4].innerHTML = "T";

	taxTable.rows[1].cells[0].innerHTML = "IT";
	taxTable.rows[1].cells[1].innerHTML = (rates[0]/100).toFixed(2);
	taxTable.rows[1].cells[2].innerHTML = rates[10]/100;
	taxTable.rows[1].cells[3].innerHTML = rates[20]/100;
	total = rates[0]/100 + rates[10]/100 + rates[20]/100;
	taxTable.rows[1].cells[4].innerHTML = total.toFixed(2);

	taxTable.rows[2].cells[0].innerHTML = "PT";
	taxTable.rows[2].cells[1].innerHTML = rates[1]/100;
	taxTable.rows[2].cells[2].innerHTML = rates[11]/100;
	taxTable.rows[2].cells[3].innerHTML = rates[21]/100;
	total = rates[3]/100 + rates[11]/100 + rates[21]/100;
	taxTable.rows[2].cells[4].innerHTML = total.toFixed(2);

	taxTable.rows[3].cells[0].innerHTML = "VT";
	taxTable.rows[3].cells[1].innerHTML = rates[2]/100;
	taxTable.rows[3].cells[2].innerHTML = rates[12]/100;
	taxTable.rows[3].cells[3].innerHTML = rates[22]/100;
	total = rates[2]/100 + rates[12]/100 + rates[22]/100;
	taxTable.rows[3].cells[4].innerHTML = total.toFixed(2);

	taxTable.rows[4].cells[0].innerHTML = "PI";
	taxTable.rows[4].cells[1].innerHTML = rates[3]/100;
	taxTable.rows[4].cells[2].innerHTML = rates[13]/100;
	taxTable.rows[4].cells[3].innerHTML = rates[23]/100;
	total = rates[3]/100 + rates[13]/100 + rates[23]/100;
	taxTable.rows[4].cells[4].innerHTML = total.toFixed(2);

	taxTable.rows[5].cells[0].innerHTML = "PO";
	taxTable.rows[5].cells[1].innerHTML = rates[4]/100;
	taxTable.rows[5].cells[2].innerHTML = rates[14]/100;
	taxTable.rows[5].cells[3].innerHTML = rates[24]/100;
	total = rates[4]/100 + rates[14]/100 + rates[24]/100;
	taxTable.rows[5].cells[4].innerHTML = total.toFixed(2);

	taxTable.rows[6].cells[0].innerHTML = "RT";
	taxTable.rows[6].cells[1].innerHTML = rates[5]/100;
	taxTable.rows[6].cells[2].innerHTML = rates[15]/100;
	taxTable.rows[6].cells[3].innerHTML = rates[25]/100;
	total = rates[5]/100 + rates[15]/100 + rates[25]/100;
	taxTable.rows[6].cells[4].innerHTML = total.toFixed(2);
}

objectSelection = function (selectList, optionList, selectTrg, optionTrg) {
	for (let i=0; i<selectList.length; i++) {
		selectTrg.appendChild(selectList[i]);
	}
	for (let i=0; i<optionList.length; i++) {
		optionTrg.appendChild(optionList[i]);
	}
}
