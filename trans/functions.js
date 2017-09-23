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
	console.log("finish init");
	contentDiv = document.getElementById("content");
	showData(transactions, contentDiv, displayList);

	categorySelect = new optionSelect(categories);
	loadMonths(transactions);
	initSort();
	initViews();
}

function initSort() {
	sortLists["monthNum"] = allItems.slice();
	sortLists["category"] = allItems.slice();

	console.log(sortLists);

	let monthBox = document.getElementById("monthSelect");
	monthBox.desc = addDiv("", "sortBar", monthBox);
	monthBox.desc.innerHTML = "Selected Month:";

	monthBox.selected = addDiv("", "sortBar", monthBox);
	monthBox.selected.innerHTML = "None";

	let boxOptions = [-1, "All"];
	let monthNum, yearNum;
	console.log(monthList);
	for (let i=0; i<monthList.length; i++) {
		monthNum = monthList[i]%12;
		yearNum = Math.floor(monthList[i]/12)+1970;
		boxOptions.push(monthList[i], monthNames[monthNum] + " " + yearNum);
	}
	sortItems["monthNum"] = new sortBox(boxOptions, transactions, "monthNum");

	monthBox.selected.addEventListener("click", function () {

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

	boxOptions = [-1, "All"];
	for (let i=0; i<categories.length; i++) {
		boxOptions.push(i, categories[i]);
	}
	sortItems["category"] = new sortBox(boxOptions, transactions, "category");

	catBox.selected.addEventListener("click", function () {

		let coords = getPos(this);
		coords[1] += parseInt(this.offsetHeight);
		sortItems["category"].showOptions(this, coords);
	});
}

function initViews() {
	let list = document.getElementById("voList");
	list.addEventListener("click", function () {
		contentDiv.innerHTML = "";
		showData(transactions, contentDiv, displayList);
	});

	let summary = document.getElementById("voSummary");
	summary.addEventListener("click", function () {
		console.log("clear div");
		contentDiv.innerHTML = "";
		summarize();
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
	console.log("numMOnths " + numMonths + "(" + hiMonth +"- " + loMonth + ")");
	monthList = new Array(numMonths + 1);
	for (i=0; i<=numMonths; i++) {
		monthList[i] = loMonth+i;
	}
}

function loadData () {
  getASync("1001").then(v => {

		r = v.split(",");
		console.log(r.length);

		let tmpA = [];
		let transCount = (r.length-1)/6;
		for (i=0; i<transCount; i++) {
			transactions.push(new transaction(r.slice(i*6, i*6+7)));
			displayList.push(i);
			//categories.push("Cat " + i);
		}
		categories = ["Unassigned"];
		console.log(transactions);

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

	console.log(baselist);
	console.log(showItems);

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
	console.log("summary line");
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

summarize = function () {
	let numMonths = monthList.length;
	let numCategories = categories.length;
	let monthCatTotals = new Array(numMonths*numCategories);
	monthCatTotals.fill(0);

	for (let i=0; i<transactions.length; i++) {
		console.log(transactions[i].amount);
		monthCatTotals[(transactions[i].monthNum - loMonth)*numCategories + transactions[i].category] += transactions[i].amount;
	}

	let newTable = document.createElement("table");
	newTable.className = "summaryTable";
	contentDiv.appendChild(newTable);

	console.log(monthCatTotals);
	console.log(monthList);
	console.log(numCategories + " categories and " + numMonths + " months");
	let newRow, newTD;

	// Create the month header Row and calculate the total amount for each month
	let monthTotals = new Array(numMonths*numCategories);
	monthTotals.fill(0);

	newRow = document.createElement("tr");
	newRow.className = "summaryTableRow";

	newTD = document.createElement("td");
	newTD.innerHTML = "";
	newTD.className = "summaryTableItem";

	newRow.appendChild(newTD);
	let year;
	for (let i=0; i<numMonths; i++ ) {
		year = Math.floor(monthList[i]/12)+1970;

		newTD = document.createElement("td");
		newTD.innerHTML = monthNames[monthList[i]%12] + " - " + year;
		newTD.className = "summaryTableItem";

		newRow.appendChild(newTD);
	}
	newTable.appendChild(newRow);

	// blank TD at the end of the row
	newTD = document.createElement("td");
	newTD.innerHTML = "";
	newTD.className = "summaryTableItem";

	newRow.appendChild(newTD);

	for (let i=0; i<numCategories; i++) {

		// create a row for this category
		newRow = document.createElement("tr");
		newRow.className = "summaryTableRow";

		newTD = document.createElement("td");
		newTD.innerHTML = categories[i];
		newTD.className = "summaryTableItem";

		newRow.appendChild(newTD);
		rowTotal = 0;
		for (let j=0; j<numMonths; j++) {
			newTD = document.createElement("td");
			newTD.innerHTML = monthCatTotals[j*numCategories+i].toFixed(2);
			newTD.className = "summaryTableItem";

			newRow.appendChild(newTD);
			rowTotal += monthCatTotals[j*numCategories+i];

			monthTotals[j] += monthCatTotals[j*numCategories+i]
		}

		// show the total for the row
		newTD = document.createElement("td");
		newTD.innerHTML = rowTotal.toFixed(2);
		newRow.appendChild(newTD);
		newTD.className = "summaryTableItem";

		newTable.appendChild(newRow);
	}

	// show the total for each month
	newRow = document.createElement("tr");
	newRow.className = "summaryTableRow";

	newTD = document.createElement("td");
	newTD.innerHTML = "";
	newTD.className = "summaryTableItem";
	newRow.appendChild(newTD);

	for (let i=0; i<numMonths; i++) {
		newTD = document.createElement("td");
		newTD.innerHTML = monthTotals[i].toFixed(2);
		newTD.className = "summaryTableItem";

		newRow.appendChild(newTD);
	}

	// blank TD at the end of the row
	newTD = document.createElement("td");
	newTD.innerHTML = "";
	newTD.className = "summaryTableItem";
	newRow.appendChild(newTD);

	// add to the table
	newTable.appendChild(newRow);
}
