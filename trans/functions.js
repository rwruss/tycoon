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

async function getASync(val) {
	let r = await loadDataPromise(val);
	return r;
}

function getPos(el) {
    // yay readability
    for (var lx=0, ly=0;
         el != null;
         lx += el.offsetLeft, ly += el.offsetTop, el = el.offsetParent);
    //return {x: lx,y: ly};
	return [lx, ly];
}

function intersect(a, b) {
	var t;
    if (b.length > a.length) t = b, b = a, a = t; // indexOf to loop over shorter
    return a.filter(function (e) {
        return b.indexOf(e) > -1;
    });
}

function init() {
	contentDiv = document.getElementById("content");

  loadData();
}

function initTest() {
	categories[0] = "Unassigned";
	let timeSpace = 15*24*3600;
	for (let i=0; i<20; i++) {
		transactions.push(new transaction ([i, i*timeSpace+1, i, i, i, "item " + i]));
		categories.push("Cat " + i);
		displayList.push(i);
	}

	finishInit();
}

function finishInit() {


	allItems = displayList;
	//console.log("finish init");
	contentDiv = document.getElementById("content");
	showData(transactions, contentDiv, displayList);

	let newHeight = parseInt(window.innerHeight) - 100;
	console.log(newHeight);
	contentDiv.style.height = parseInt(window.innerHeight) - 100;

	categorySelect = new optionSelect(categories);
	loadMonths(transactions);
	initSort();
	initViews();
}

function initSort() {
	filterLists["monthNum"] = allItems.slice();
	filterLists["category"] = allItems.slice();

	summaryFilters["monthNum"] = monthList;
	summaryFilters["category"] = [];

	console.log(filterLists);

	let monthBox = document.getElementById("monthSelect");
	monthBox.desc = addDiv("", "sortBar", monthBox);
	monthBox.desc.innerHTML = "Selected Month:";

	monthBox.selected = addDiv("", "sortBar", monthBox);
	monthBox.selected.innerHTML = "None";

	//let boxOptions = [-1, "All"];
	let boxOptions = [];
	let monthNum, yearNum;
	console.log(monthList);
	for (let i=0; i<monthList.length; i++) {
		monthNum = monthList[i]%12;
		yearNum = Math.floor(monthList[i]/12)+1970;
		boxOptions.push(monthList[i], monthNames[monthNum] + " " + yearNum);
	}
	sortItems["monthNum"] = new sortBox(boxOptions, transactions, "monthNum");

	monthBox.selected.addEventListener("click", function (e) {
		e.stopPropagation();
		let coords = getPos(this);
		coords[1] += parseInt(this.offsetHeight);
		//new sortBox(this, coords);
		sortItems["monthNum"].showOptions(this, coords);
	});

	let catBox = document.getElementById("catSelect");
	catBox.desc = addDiv("", "sortBar", catBox);
	catBox.desc.innerHTML = "Category:";

	catBox.selected = addDiv("", "sortBar", catBox);
	catBox.selected.innerHTML = "None";

	//boxOptions = [-1, "All"];
	boxOptions = [];
	for (let i=0; i<categories.length; i++) {
		boxOptions.push(i, categories[i]);
		summaryFilters["category"].push(i);
	}
	sortItems["category"] = new sortBox(boxOptions, transactions, "category");

	catBox.selected.addEventListener("click", function (e) {
		e.stopPropagation();
		let coords = getPos(this);
		coords[1] += parseInt(this.offsetHeight);
		sortItems["category"].showOptions(this, coords);
	});
}

function initViews() {
	let list = document.getElementById("voList");
	list.addEventListener("click", function () {
		contentDiv.innerHTML = "";
		currentView = "list";
		showData(transactions, contentDiv, displayList);
	});

	let summary = document.getElementById("voSummary");
	summary.addEventListener("click", function () {
		contentDiv.innerHTML = "";
		currentView = "summary";
		calcSummary(showSummary);
	})

	let graph = document.getElementById("voGraph");
	graph.addEventListener("click", function () {
		contentDiv.innerHTML = "";
		currentView = "graph";
		calcSummary(drawGraph);
	})
}

function calcMonthNum (date) {
	monthNum = ((date.getUTCFullYear()-1970)*12) + date.getUTCMonth();
	return monthNum;
}

function loadMonths(itemList) {
	let date = new Date(itemList[0].date*1000);
	let monthNum = calcMonthNum(date);
	loMonth = monthNum;
	let hiMonth = monthNum;

	for (let i=1; i<itemList.length; i++) {
		date = new Date(itemList[i].date*1000);
		monthNum = calcMonthNum(date);

		loMonth = Math.min(loMonth, monthNum);
		hiMonth = Math.max(hiMonth, monthNum);
	}
	let numMonths = hiMonth - loMonth;
	console.log(numMonths + " = " + hiMonth + " - " + loMonth)
	//console.log("numMOnths " + numMonths + "(" + hiMonth +"- " + loMonth + ")");
	monthList = new Array(numMonths + 1);
	for (i=0; i<=numMonths; i++) {
		monthList[i] = loMonth+i;
	}
}

function loadData () {
  getASync("1001").then(v => {

		r = v.split("|");
		console.log(r.length);
		//categories = r.slice(1, r[0]);
		console.log(r[0] + " categories ");
		let tmpA = [];
		let transCount = (r.length - (r[0]+1) - 1)/6;  // subtract the amount of categories plus the category count and the dummy item on the end due to an extra comma
		let offset = parseInt(r[0])+1;
		for (i=0; i<transCount; i++) {

			transactions.push(new transaction(r.slice(offset+i*6, offset+i*6+7)));
			displayList.push(i);
			//categories.push("Cat " + i);
		}
		//categories = ["Unassigned"];
		categories = r.slice(1, parseInt(r[0])+1);
		//console.log(transactions);

		categorySorted = [];
		categoryOrder = [];
		for (let i=0; i<categories.length; i++) {
			categoryOrder.push(i);
			categorySorted.push(new categoryItem(i, categories[i]));
		}
		console.log(categoryOrder);
		console.log(categorySorted);
		categoryOrder.sort(categorySort);

		console.log(categoryOrder);

		finishInit();

		console.log("transactions loaded");
	});
}

function loadDataPromise(val) {
	return new Promise(resolve => {
		params = "val1="+val;
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.open("POST", "servItem.php?gid=0", true);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				//console.log(xmlhttp.response);
				resolve(xmlhttp.response);
				}
			}

		xmlhttp.send(params);
	})
}

function showData (baselist, trg, showItems) {
	trg.innerHTML = "";
	let total = 0;
	let count = 0;

	//console.log(baselist);
	//console.log(showItems);

	headerRow = new sortBar();
	headerRow.render(trg);

	if (showItems) {
		for (i=0; i<showItems.length; i++) {
			baselist[showItems[i]].tableLine(trg);
			total += baselist[showItems[i]].amount;
		}
		count = showItems.length;
	} else {
		for (i=0; i<baselist.length; i++) {
			baselist[i].tableLine(trg);
			total += baselist[i].amount;
		}
		count = baselist.length;
	}
	summaryLine(count, total, trg);
}


summaryLine = function (count, total, trg) {
	let newRow = addDiv("", "summaryRow", trg);

	newRow.date = addDiv("", "transRowDate", newRow);
	newRow.card = addDiv("", "transRowDate", newRow);
	newRow.amount = addDiv("", "transRowDate", newRow);
	newRow.category = addDiv("", "transRowDate", newRow);
	newRow.desc = addDiv("", "transRowDesc", newRow);

	newRow.date.innerHTML = "SUMMARY:"
	newRow.card.innerHTML = "."
	newRow.amount.innerHTML = total.toFixed(2);
}

calcSummary = function (callback) {
	console.log(summaryFilters);

	let numMonths = monthList.length;
	let numCategories = categories.length;
	monthCatTotals = new Array(numMonths*numCategories);
	monthCatTotals.fill(0);

	for (let i=0; i<transactions.length; i++) {
		monthCatTotals[(transactions[i].monthNum - loMonth)*numCategories + transactions[i].category] += transactions[i].amount;
	}

	callback();
}

function showSummary() {
	contentDiv.innerHTML = "";
	let newTable = document.createElement("table");
	newTable.className = "summaryTable";
	contentDiv.appendChild(newTable);

	let newRow, newTD;

	// Create the month header Row and calculate the total amount for each month
	let numMonths = monthList.length;
	let numCategories = categories.length;
	let monthTotals = new Array(numMonths*numCategories);
	monthTotals.fill(0);

	newRow = document.createElement("tr");
	newRow.className = "summaryTableRow";

	newTD = document.createElement("td");
	newTD.innerHTML = "";
	newTD.className = "summaryTableItem";

	newRow.appendChild(newTD);
	let year;
	let colCount = 0;
	for (let i=0; i<numMonths; i++ ) {
		if (summaryFilters["monthNum"].indexOf(monthList[i]) > -1) {
			year = Math.floor(monthList[i]/12)+1970;

			newTD = document.createElement("td");
			newTD.innerHTML = monthNames[monthList[i]%12] + " - " + year;
			newTD.className = "summaryTableHead";

			newRow.appendChild(newTD);
			colCount++;
		}
	}
	newTable.appendChild(newRow);

	// Total TD at the end of the row
	newTD = document.createElement("td");
	newTD.innerHTML = "Total";
	newTD.className = "summaryTableHead";

	newRow.appendChild(newTD);

	// Average TD at the end of the row
	newTD = document.createElement("td");
	newTD.innerHTML = "Average";
	newTD.className = "summaryTableHead";

	newRow.appendChild(newTD);

	console.log("looking in this array:");
	console.log(summaryFilters["category"]);
	for (let i=0; i<numCategories; i++) {
		if (summaryFilters["category"].indexOf(i) > -1) {
			// create a row for this category
			newRow = document.createElement("tr");
			newRow.className = "summaryTableRow";

			newTD = document.createElement("td");
			newTD.innerHTML = categories[i];
			newTD.className = "summaryTableItem";

			newRow.appendChild(newTD);
			rowTotal = 0;
			for (let j=0; j<numMonths; j++) {
				if (summaryFilters["monthNum"].indexOf(monthList[j]) > -1) {
					newTD = document.createElement("td");
					newTD.innerHTML = monthCatTotals[j*numCategories+i].toFixed(2);
					newTD.className = "summaryTableItem";

					newRow.appendChild(newTD);
					rowTotal += monthCatTotals[j*numCategories+i];

					monthTotals[j] += monthCatTotals[j*numCategories+i];
				}
			}

			// show the total for the row
			newTD = document.createElement("td");
			newTD.innerHTML = rowTotal.toFixed(2);
			newRow.appendChild(newTD);
			newTD.className = "summaryTableItem";

			// show the average for the row
			newTD = document.createElement("td");
			newTD.innerHTML = (rowTotal/numMonths).toFixed(2);
			newRow.appendChild(newTD);
			newTD.className = "summaryTableItem";

			newTable.appendChild(newRow);
		}
	}

	// show the total for each month
	newRow = document.createElement("tr");
	newRow.className = "summaryTableRow";

	newTD = document.createElement("td");
	newTD.innerHTML = "";
	newTD.className = "summaryTableItem";
	newRow.appendChild(newTD);

	let totalSum = 0;
	for (let i=0; i<numMonths; i++) {
		if (summaryFilters["monthNum"].indexOf(monthList[i]) > -1) {
			newTD = document.createElement("td");
			newTD.innerHTML = monthTotals[i].toFixed(2);
			totalSum += monthTotals[i];
			newTD.className = "summaryTableItem";

			newRow.appendChild(newTD);
		}
	}

	// total TD at the end of the row
	newTD = document.createElement("td");
	newTD.innerHTML = totalSum.toFixed(2);
	newTD.className = "summaryTableItem";
	newRow.appendChild(newTD);

	// average TD at the end of the row
	newTD = document.createElement("td");
	newTD.innerHTML = (totalSum/numMonths).toFixed(2);
	newTD.className = "summaryTableItem";
	newRow.appendChild(newTD);

	// add to the table
	newTable.appendChild(newRow);
}

function headerLine() {
	let newRow = addDiv("", "transRow", null);

    newRow.date = addDiv("", "transRowDate", newRow);
    newRow.card = addDiv("", "transRowDate", newRow);
    newRow.amount = addDiv("", "transRowDate", newRow);
    newRow.category = addDiv("", "transRowDate", newRow);
    newRow.desc = addDiv("", "transRowDesc", newRow);
	  newRow.parentObj = this;


	return newRow;
}

function sortByProp (property) {
	var sortOrder = 1;
	sortOrder = property[0];

	property = property[1];
    return function (a,b) {
        var result = (transactions[a][property] < transactions[b][property]) ? -1 : (transactions[a][property] > transactions[b][property]) ? 1 : 0;
		//console.log(transactions[a][property] + " vs " + transactions[b][property] + " = " + result*sortOrder);
        return result * sortOrder;
    }
}

function categorySort (a,b) {
	return (categorySorted[a].desc < categorySorted[b].desc) ? -1 : (categorySorted[b].desc < categorySorted[a].desc) ? 1 : 0;
}

function resetFilters(property) {
	console.log("reset fitlers"  + property);
	if (property == "monthNum") {
		summaryFilters["monthNum"] = monthList;
	}
	else if (property == "category") {
		summaryFilters["category"] = [];
		for (let i=0; i<categories.length; i++) {
			summaryFilters["category"].push(i);
		}
	}
}

function drawGraph() {
	contentDiv.innerHTML = "";
	c = document.createElement("canvas");

	var ctx = c.getContext("2d");
	console.log(parseInt(contentDiv.offsetHeight*0.98))
	ctx.canvas.width = parseInt(contentDiv.offsetWidth*0.98);
	ctx.canvas.height = parseInt(contentDiv.offsetHeight*0.98);

	//ctx.canvas.width = 500;
	//ctx.canvas.height = 500;

	contentDiv.appendChild(c);

	ctx.moveTo(0,0);
	ctx.lineTo(200,100);
	ctx.stroke();

	let numMonths = monthList.length;
	let numCategories = categories.length;
	//monthCatTotals = new Array(numMonths*numCategories);

	let maxMonth = 0;
	let monthTotals = [];
	for (let i=0; i<numMonths; i++) {
		let tmpTotal = 0;
		monthTotals.push(tmpTotal);
		for (let j=0; j<numCategories; j++) {
			tmpTotal += monthCatTotals[i*numCategories+j];
			monthTotals.push(tmpTotal);
		}

		maxMonth = Math.max(maxMonth, tmpTotal);
	}
	console.log("max month is " + maxMonth);

	let colorStepSize = Math.floor(255/Math.ceil(numCategories/7));
	let hStepSize = (ctx.canvas.width - 100)/numMonths;

	let colorSwitchR = [1,1,0,1,1,0,0];
	let colorSwitchG = [1,1,1,0,0,1,0];
	let colorSwitchB = [1,0,1,1,0,0,1];

	ctx.fillStyle = "#000";
	console.log(monthCatTotals);
	let startPoint;
	let x0, x1, y0, y1, y2, y3, r, g, b, colorStep, colorBlend;

	for (let j=0; j<3; j++) {
		for (let i=0; i<numMonths-1; i++) {

		}
	}
	/*
	for (i=0; i<numMonths-1; i++) {
		startPoint = [hStepSize*i, ctx.canvas.height];
		for (j=0; j<3; j++) {
			console.log(i + " / " + j + " start at " + startPoint[0] + ", " + startPoint[1] + " with a value of " + monthCatTotals[(i+1)*numCategories+j]);
			x0 = startPoint[0],
			x1 = startPoint[0] + hStepSize;
			y0 = startPoint[1];
			y1 = (maxMonth - monthCatTotals[(i+1)*numCategories+j])/maxMonth*ctx.canvas.height;
			y2 = (maxMonth - monthCatTotals[(i+1)*numCategories+j+1])/maxMonth*ctx.canvas.height;
			y3 = (maxMonth - monthCatTotals[(i)*numCategories+j+1])/maxMonth*ctx.canvas.height;

			colorStep = Math.floor(j/7);
			colorBlend = j%7;
			r = 255 - colorSwitchR[colorBlend]*colorStepSize;
			g = 255 - colorSwitchG[colorBlend]*colorStepSize;
			b = 255 - colorSwitchB[colorBlend]*colorStepSize;
			ctx.fillStyle = "rgb(" + r + "," + g + "," + b + ")";
			ctx.beginPath();
			ctx.moveTo(x0, y0);
			console.log(x0 + ", " + y0);

			ctx.lineTo(x1,y1);
			console.log(x1 + ", " + y1);

			ctx.lineTo(x1, y2);
			console.log(x1 + ", " +y2);

			ctx.lineTo(x0, y3);
			console.log(x0 + ", " + y3);

			ctx.closePath();
			startPoint = [startPoint[0], y3]
			ctx.fill();
		}
	}*/
}
