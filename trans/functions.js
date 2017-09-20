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

function init() {
	contentDiv = document.getElementById("content");
  loadData();
}

function initSort() {
	let monthBox = document.getElementById("monthSelect");
	console.log(monthList);
	monthBox.addEventListener("click", function () {
		console.log("create sort box");
		let boxOptions = [];
		let monthNum, yearNum;
		for (let i=0; i<monthList.length; i++) {
			monthNum = monthList[i]%12;
			yearNum = Math.floor(monthList[i]/12)+1970;
			boxOptions.push(monthList[i], monthNames[monthNum] + " " + yearNum);
		}
		new sortBox(boxOptions, transactions, "monthNum");
	});
	let catBox = document.getElementById("catSelect");
}

function initTest() {
	categories[0] = "None";
	let timeSpace = 15*24*3600;
	for (let i=0; i<20; i++) {
		transactions.push(new transaction ([i, i*timeSpace+1, i, i, i, "item " + i]));
		categories.push("Cat " + i);
	}
	contentDiv = document.getElementById("content");
	showData(transactions, contentDiv);
	
	categorySelect = new optionSelect(categories);
	loadMonths(transactions);
	initSort();
}

function loadMonths(itemList) {
	let date = new Date(itemList[0].date*1000);
	let monthNum = calcMonthNum(date);
	let loMonth = monthNum;
	let hiMonth = monthNum;
	console.log(itemList[0].date +" = " + monthNum + "//" + (date.getUTCFullYear()-1970)*12)
	console.log("himonth: " + hiMonth + ", loMonth: " + loMonth);
	for (let i=1; i<itemList.length; i++) {
		date = new Date(itemList[i].date*1000);
		monthNum = calcMonthNum(date);
		
		console.log(itemList[i].date +" = " + monthNum)
		
		loMonth = Math.min(loMonth, monthNum);
		hiMonth = Math.max(hiMonth, monthNum);
	}
	console.log("himonth: " + hiMonth + ", loMonth: " + loMonth);
	let numMonths = hiMonth - loMonth;
	monthList = new Array(numMonths + 1);
	for (i=0; i<=numMonths; i++) {
		monthList[i] = loMonth+i;
	}
	console.log(monthList);
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
		showData(transactions, contentDiv);
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

function showData (list, trg) {
	trg.innerHTML = "show Data";
	for (i=0; i<list.length; i++) {
		list[i].tableLine(trg);
	}
}


