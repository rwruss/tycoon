class transaction {
  constructor(dat) {
	this.transID = dat[0];
    this.date = dat[1];
    this.card = dat[2];
    this.amount = dat[3]/100;
    this.category = dat[4];
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
	
	
	newRow.addEventListener("click", function () {
		console.log("category selection");
		categorySelect.showOptions(this.parentObj, this.parentObj.category);
	});
	this.rowItem = newRow;
  }
  
  changeCategory(newCat) {
	  this.category = newCat;
	  this.rowItem.category.innerHTML = categories[newCat];
  }
}

class optionSelect {
	constructor(options) {
		this.options = options;
		this.holder = null;
		this.subDivs = new Array();
		this.transaction = null;
	}
	
	showOptions(transItem, selected) {
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
			newBox = addDiv("", "optionUn", contentDiv);		
		
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
		//this.subDivs[selected].innerHTML = "SELECTED";
		this.subDivs[selected].className = "optionSel";
		console.log(this.subDivs);
	}
	
	selectOption(optionNum) {
		this.transaction.changeCategory(optionNum);
	}
}

class sortBox {
	constructor (options, baseList, property) {
		this.container = null;
		this.optionList = options;
		this.showOptions();
		this.baseList = baseList;
		this.prop = property;
		console.log(this.optionList);
	}
	
	showOptions() {
		let newBox = addDiv("", "optionUn", contentDiv);
		newBox.innerHTML = "OPTIONS"
		for (let i=0; i<this.optionList.length; i+=2) {
			let tmpDiv = addDiv("", "", newBox);
			tmpDiv.innerHTML = this.optionList[i+1];
			tmpDiv.itemNum = this.optionList[i];
			tmpDiv.parentObj = this;
			tmpDiv.addEventListener("click", function () {
				console.log("selected " + this.itemNum);
				this.parentObj.sortList(this.itemNum);
			})
		}
	}
	
	sortList(itemNum) {
		console.log("sort based on item num " + itemNum + " --> " + this.optionList[itemNum*2+1] + " and property " + this.prop);
		let tmpA = [];
		for (let i=0; i<this.baseList.length; i++) {
			console.log(this.baseList[i][this.prop] + " vs " + this.optionList[itemNum*2]);
			if (this.baseList[i][this.prop] == this.optionList[itemNum*2]) {
				tmpA.push(i);
			}
		}
		console.log(tmpA);
	}
}
