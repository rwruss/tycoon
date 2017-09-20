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

function init() {
	contentDiv = document.getElementById("content");
  loadData();
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

function showData (list, trg) {
	trg.innerHTML = "show Data";
	for (i=0; i<list.length; i++)	 {
		list[i].tableLine(trg);
	}
}

async function getASync(val) {
	let r = await loadDataPromise(val);
	return r;
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
