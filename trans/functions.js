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

function init() {
	contentDiv = document.getElementById("content");
  loadData();
}

function initSort() {
	let monthBox = document.getElementById("monthSelect");
	monthBox.desc = addDiv("", "sortBar", monthBox);
	monthBox.desc.innerHTML = "Selected Month:";

	monthBox.selected = addDiv("", "sortBar", monthBox);
	monthBox.selected.innerHTML = "None";

	monthBox.addEventListener("click", function () {
		let boxOptions = [-1, "None"];
		let monthNum, yearNum;
		for (let i=0; i<monthList.length; i++) {
			monthNum = monthList[i]%12;
			yearNum = Math.floor(monthList[i]/12)+1970;
			boxOptions.push(monthList[i], monthNames[monthNum] + " " + yearNum);
		}
		new sortBox(boxOptions, transactions, "monthNum", this);
	});
	
	let catBox = document.getElementById("catSelect");
	catBox.desc = addDiv("", "sortBar", catBox);
	catBox.desc.innerHTML = "Category:";

	catBox.selected = addDiv("", "sortBar", catBox);
	catBox.selected.innerHTML = "None";
	
	catBox.addEventListener("click", function () {
		let boxOptions = [-1, "None"];
		for (let i=0; i<categories.length; i++) {
			boxOptions.push(i, categories[i]);
		}
		new sortBox(boxOptions, transactions, "category", this);
	});
}

function initTest() {
	categories[0] = "Unassigned";
	let timeSpace = 15*24*3600;
	for (let i=0; i<20; i++) {
		transactions.push(new transaction ([i, i*timeSpace+1, i, i, i, "item " + i]));
		categories.push("Cat " + i);
	}
	contentDiv = document.getElementById("content");
	showData(transactions, contentDiv, null);

	categorySelect = new optionSelect(categories);
	loadMonths(transactions);
	initSort();
	initViews();
}

function initViews() {
	let list = document.getElementById("voList");
	list.addEventListener("click", function () {
		contentDiv.innerHTML = "";
		showData(transactions, contentDiv, null);
	});
	
	let summary = document.getElementById("voSummary");
	summary.addEventListener("click", function () {
		console.log("clear div");
		contentDiv.innerHTML = "";
		summarize();		
	})
}

function loadMonths(itemList) {
	let date = new Date(itemList[0].date*1000);
	let monthNum = calcMonthNum(date);
	let loMonth = monthNum;
	let hiMonth = monthNum;

	for (let i=1; i<itemList.length; i++) {
		date = new Date(itemList[i].date*1000);
		monthNum = calcMonthNum(date);

		loMonth = Math.min(loMonth, monthNum);
		hiMonth = Math.max(hiMonth, monthNum);
	}
	let numMonths = hiMonth - loMonth;
	monthList = new Array(numMonths + 1);
	for (i=0; i<=numMonths; i++) {
		monthList[i] = loMonth+i;
	}
}

function calcMonthNum (date) {
	monthNum = ((date.getUTCFullYear()-1970)*12) + date.getUTCMonth();
	return monthNum;
}

function loadData () {
  getASync("1001").then(v => {

		r = v.split(",");
		console.log(r.length);

		for (i=0; i<100; i+=5) {
			transactions.push(new transaction(r.slice(i, i+6)));
		}
		console.log("transactions loaded");
		showData(transactions, contentDiv, null);
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
				console.log(xmlhttp.response);
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
	let monthTotals = new Array(numMonths*numCategories);
	monthTotals.fill(0);
	
	for (let i=0; i<transactions.length; i++) {
		monthTotals[transactions[i].monthNum*numCategories + transactions[i].category] += transactions[i].amount;
	}
	
	let newTable = document.createElement("table");
	contentDiv.appendChild(newTable);
	
	console.log(numCategories + " categories and " + numMonths + " months");
	let newRow, newTD;
	for (let i=0; i<numCategories; i++) {
		newRow = document.createElement("tr");
		newTD = document.createElement("td");
		newTD.innerHTML = categories[i];
		newRow.appendChild(newTD);
		for (let j=0; j<numMonths; j++) {
			newTD = document.createElement("td");
			newTD.innerHTML = monthTotals[j*numCategories+i];
			newRow.appendChild(newTD);
		}
	newTable.appendChild(newRow);
	}
}
