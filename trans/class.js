class transaction {
  constructor(dat) {
	this.transID = dat[0];
    this.date = dat[1];
    this.card = dat[3];
    this.amount = dat[2]/100;
    this.category = parseInt(dat[4]);
    this.desc = dat[5];
	this.rowItem = null;

	let date = new Date(this.date*1000);
	this.monthNum = calcMonthNum(date);
  }

  tableLine (trg) {
    let newRow = addDiv("", "transRow", trg);

    newRow.date = addDiv("", "transRowDate", newRow);
    newRow.card = addDiv("", "transRowDate", newRow);
    newRow.amount = addDiv("", "transRowDate", newRow);
    newRow.category = addDiv("", "transRowDate", newRow);
    newRow.desc = addDiv("", "transRowDesc", newRow);
	  newRow.parentObj = this;

    let date = new Date(this.date*1000);

    newRow.date.innerHTML = (date.getUTCMonth()+1) + " / " + date.getUTCDate() + " / " + date.getUTCFullYear() + ", " + this.date;
    newRow.card.innerHTML = this.card;
    newRow.amount.innerHTML = this.amount.toFixed(2);
    newRow.category.innerHTML = categories[this.category];
    newRow.desc.innerHTML = this.desc;


	newRow.category.addEventListener("click", function () {
		console.log("category selection");
		let coords = getPos(this);
		console.log(coords);
		console.log(this)
		coords[0] += parseInt(this.offsetWidth);
		console.log(coords);
		categorySelect.showOptions(this.parentNode.parentObj, this.parentNode.parentObj.category, coords);
	});
	this.rowItem = newRow;
  }

  changeCategory(newCat) {
	  this.category = newCat;
	  
	  let sendA = [1002, this.transID, newCat, this.amount];
	  getASync(sendA.join(",")).then(v => {
		  r = v.split("|");
		  if (r[0] == 1) {
			  this.rowItem.category.innerHTML = categories[newCat];
		  } else console.log("An error has occured")
	  });
  }
}

class optionSelect {
	constructor(options) {
		this.options = options;
		this.holder = null;
		this.subDivs = new Array();
		this.transaction = null;
	}

	showOptions(transItem, selected, coords) {
		this.selected = selected;
		//this.subDivs = new Array();
		this.transaction = transItem;

		console.log(this.selected);
		let newBox;
		if (this.holder) {
			console.log("already made " + this.subDivs.length);
			newBox = this.holder;
			for (let i=0; i<this.subDivs.length; i++) {
				this.subDivs[i].className = "optionUn";
			}
		} else {
			console.log("make new");
			newBox = addDiv("", "optionUn", document.getElementById("container"));

			newBox.parentObj = this;
			for (let i=0; i<categories.length; i++) {
				let catOption = addDiv("", "optionUn", newBox);
				catOption.itemNum = i;
				catOption.innerHTML = categories[i];
				catOption.addEventListener("click", function () {
					this.parentNode.parentObj.selectOption(this.itemNum);

					this.parentNode.parentObj.holder.className = "optBoxHidden";
					//this.parentNode.parentObj.holder.parentNode.removeChild(this.parentNode.parentObj.holder);
				});
				this.subDivs.push(catOption);
			}
			this.holder = newBox;
		}
		newBox.className = "optBoxShow";
		newBox.style.left = coords[0];
		newBox.style.top = coords[1];
		//this.subDivs[selected].innerHTML = "SELECTED";
		this.subDivs[selected].className = "optionSel";
		//console.log(this.subDivs);
	}

	selectOption(optionNum) {
		this.transaction.changeCategory(optionNum);
	}
}

class sortBox {
	constructor (options, baseList, property) {
		this.container = null;
		this.optionList = options;

		this.baseList = baseList;
		this.prop = property;
		this.returnTarget = null;
		this.selectStatus = new Array(options.length);
		this.selectStatus.fill(0);
		this.childDivs = new Array();
		this.container = null;

		this.makeBox();
	}

	makeBox() {
		let newBox = addDiv("", "optBoxHidden", document.getElementById("container"));
		newBox.innerHTML = "OPTIONS"
		newBox.parentObj = this;

		for (let i=0; i<this.optionList.length; i+=2) {
			let tmpDiv = addDiv("", "optionUn", newBox);
			tmpDiv.innerHTML = this.optionList[i+1];
			tmpDiv.itemNum = i;
			tmpDiv.parentObj = this;

			this.childDivs.push(tmpDiv);
			tmpDiv.addEventListener("click", function () {
				console.log("selected " + this.itemNum);
				this.parentObj.selectItem(this.itemNum, this);
			})
		}

		let tmpDiv = addDiv("", "optionUn", newBox);
		tmpDiv.parentObj = this;
		tmpDiv.addEventListener("click", function () {
			let items = new Array();
			for (let i=0; i<this.parentObj.selectStatus.length; i++) {

				if (this.parentObj.selectStatus[i] == 1) {
					items.push(i);
				}
			}
			console.log(items);
			this.parentNode.className = "optBoxHidden";
			this.parentObj.sortList(items);
		});
		tmpDiv.innerHTML = "APPLY FILTER";

		this.container = newBox;
	}

	showOptions(returnTrg, coords) {
		this.container.className = "optBoxShow";
		this.container.style.left = coords[0];
		this.container.style.top = coords[1];

		this.returnTarget = returnTrg;

	}

	selectItem(itemNum, div) {
		if (itemNum == 0) {
			this.selectStatus.fill(0);
			for (i=0; i<this.childDivs.length; i++) {
				this.childDivs[i].className = "optionUn";
			}
		return;
		}

		if (this.selectStatus[itemNum] == 0) {
			this.selectStatus[itemNum] = 1;
			div.className = "optionSel";
		} else {
			this.selectStatus[itemNum] = 0;
			div.className = "optionUn";
		}
	}

	sortList(items) {
		console.log(items);
		if (items.length == 0) {
			filterLists[this.prop] = allItems;
			//showData(transactions, contentDiv, null);
		} else {

			let tmpA = new Array();
			let checkVals = [];
			for (let i=0; i<items.length; i++) {
				checkVals.push(this.optionList[items[i]]);
			}

			console.log(checkVals);
			for (let i=0; i<this.baseList.length; i++) {
				let testVal = checkVals.indexOf(this.baseList[i][this.prop]);
				//console.log("Look for value " + testVal + " in property " + this.prop);
				if (checkVals.indexOf(this.baseList[i][this.prop]) >= 0) {
					tmpA.push(i);
				}
			}
			//console.log(filterLists);
			filterLists[this.prop] = tmpA;

		}
    console.log(filterLists);
		let useA = allItems.slice();
		for (let key in filterLists) {
			//console.log(useA);
			//console.log(key)
			//console.log(filterLists[key]);
			if (filterLists[key].length > 0)	{
        useA = intersect(useA, filterLists[key]);
      } else useA = [];
		}
		//console.log(useA);

		displayList = useA;
		console.log(displayList);
		displayList.sort(sortByProp(currentSort));
		console.log(displayList);
		showData(transactions, contentDiv, displayList);

		console.log(this.returnTarget)
		//this.returnTarget.selected.innerHTML = this.optionList[itemNum+1];
	}

}

class sortBar {
	constructor() {}
	
	render(trg) {
		let newRow = addDiv("", "transRow", trg);
		newRow.parentObj = this;
		this.rowObject = newRow;
		newRow.parentObj = this;

		newRow.date = addDiv("", "transRowDate", newRow);
		newRow.card = addDiv("", "transRowDate", newRow);
		newRow.amount = addDiv("", "transRowDate", newRow);
		newRow.category = addDiv("", "transRowDate", newRow);
		newRow.desc = addDiv("", "transRowDesc", newRow);
		
		
		newRow.date.innerHTML = "DATE";
		newRow.card.innerHTML = "CARD";
		newRow.amount.innerHTML = "AMOUNT";
		newRow.category.innerHTML = "TYPE";
		newRow.desc.innerHTML = "DESCRIPTION";
		
		newRow.date.addEventListener("click", function () {
			this.parentNode.parentObj.sortBy("date");
		});
		newRow.amount.addEventListener("click", function () {
			this.parentNode.parentObj.sortBy("amount");
		});
	}
	
	sortBy(desc) {
		if (currentSort[1] == desc) {
			currentSort[0] *= -1;
		} else {
			currentSort = [1, desc];
		}
	
	displayList.sort(sortByProp(currentSort));
	showData(transactions, contentDiv, displayList);
	}
	
}