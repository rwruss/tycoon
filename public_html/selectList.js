class objectList {
	constructor () {
		this.sortOptions = [];
		this.filterOptions = [];
		this.filterNames = [];
		this.sortNames = [];
		this.sortBy = null;
		this.sortDir = 1;
		this.emptyItem = null;
	}

	addSort(val, desc) {
		this.sortOptions.push(val);
		this.sortNames.push(desc);
	}

	addFilter(val, desc) {
		this.filterOptions.push(val);
		this.filterNames.push(desc);
	}

	setEmpty(key) {
		this.emptyItem = key;
	}

	SLsingleButton(target, opts) {
		//console.log(this.selectedItems);
		var selectButton = addDiv("b1", "listButton", target);
		selectButton.innerHTML = "button";
		
		selectButton.listItem = this;
		selectButton.selectedValue = false;

		// Set default render
		let item = this;
		var renderFunction = function(x, y) {
				return item.showItem(x, y);
			}

		// Set default selectButton
		var selectFunction = function() {};
		// Apply options
		if (typeof opts !== "undefined") {
			//console.log(opts);
			if (opts.setVal !== "undefined") {
				//console.log("show a default value");
				this.existingValue(selectButton, opts);
			}
			if (opts.renderFunction) {
				console.log("load a different function");
				renderFunction = opts.renderFunction;
			} else {
			}
			if (opts.selectFunction) {
				console.log("set select function");
				selectFunction = opts.selectFunction;
			} else {
			}
		}

		selectButton.addEventListener("click", function () {
			item.SLsingleSelect(selectButton, renderFunction, selectFunction)
			});
		return selectButton;


	}

	SLsingleSelect(target, renderFunction, selectFunction) {
		//console.log("SLSELECT: " + target.innerHTML + " function is " + renderFunction)
		var showContain;
		if (document.getElementById("selectMenu")) showContain = document.getElementById("selectMenu");
		else showContain = addDiv("selectMenu", "selectMenu", "gmPnl");


		showContain.sortBar = addDiv("", "selectSort", showContain);
		showContain.closeButton = addDiv("", "paneCloseButton", showContain);
		showContain.closeButton.innerHTML = "X";

		showContain.closeButton.addEventListener("click", function () {
			this.parentNode.remove()});

		let sortTarget = this;
		for (var i=0; i<this.sortOptions.length; i++) {
			var sortButton = addDiv("", "button", showContain.sortBar);
			sortButton.innerHTML = this.sortNames[i];
			var testVal = this.sortOptions[i];
			sortButton.addEventListener("click", function () {
				SLsortBy(sortTarget, testVal);
				sortTarget.SLshowList(target, showContain.content, renderFunction, selectFunction);
			});
		}

		// Filter options for selection
		let filterTarget = this;
		var filterBox;
		for (var i=0; i<this.filterOptions.length; i++) {
			console.log("filter for property " + this.filterOptions[i]);
			filterBox = addSelect("", "", showContain.sortBar);

			let prop = this.filterOptions[i];

			// Get list of options
			let itemList = ["None"];
			for (var itemNum = 0; itemNum<this.listItems.length; itemNum++) {
				console.log(this.parentList[this.listItems[itemNum]])
				console.log("CHeck " + this.parentList[this.listItems[itemNum]] + " for prop " + prop)
				if (itemList.indexOf(this.parentList[this.listItems[itemNum]][prop]) == -1) {
					itemList.push(this.parentList[this.listItems[itemNum]][prop]);
					let newOpt = document.createElement("option");
					newOpt.text = this.parentList[this.listItems[itemNum]][prop];
					filterBox.add(newOpt);
				}
			}
		}
		// End of filter options for SLreadSelection

		if (this.filterOptions.length > 0) {
			var filterButton = addDiv("", "button", showContain.sortBar);
			filterButton.innerHTML = "Apply Filters";

			filterButton.addEventListener("click", function () {
				console.log("apply filters");
				for (var p=0; p<filterTarget.filterOptions.length; p++) {
					filterTarget.listItems = filterTarget.startItems;
					SLFilterBy(filterTarget, filterTarget.filterOptions[p], filterBox.value);
					console.log("fitlered list " + filterTarget.listItems);
					console.log(filterTarget);

					filterTarget.SLshowList(target, showContain.content, renderFunction, selectFunction);
				}
			});
		}


		showContain.content = addDiv("", "", showContain);
		showContain.content.style.float = "left";
		showContain.content.style.position = "relative";
		showContain.content.innerHTML = "";

		//console.log("pass this function");
		//console.log(renderFunction);
		this.SLshowList(target, showContain.content, renderFunction, selectFunction);
	}
	
	SLShowAll(target, renderFunction) {
		for (var i=0; i<this.listObj.length; i++) {
			let object = renderFunction(this.parentList[this.listItems[i]], target, i);
		}
	}

	SLshowList(target, selectContainer, renderFunction, selectFunction) {
		selectContainer.innerHTML = "";
		for (var i=0; i<this.listItems.length; i++) {
			if (this.parentList[this.listItems[i]] instanceof objectList) {

				let object = this.parentList[this.listItems[i]].typeIcon(selectContainer);
				let subtarg = this.parentList[this.listItems[i]];
				if (this.parentList[this.listItems[i]] != "undefined") object.addEventListener("click", function () {
					subtarg.SLsingleSelect(target, function() {})
					});
			} else {
			let emptyCheck = -1;
			if (this.emptyItem !== null) emptyCheck = this.parentList[this.emptyItem].objID;
			if ((this.selectedItems[i] == 0 && this.parentList[this.listItems[i]].objID != this.emptyItem) || (this.emptyItem == i)) {
				let object = renderFunction(this.parentList[this.listItems[i]], selectContainer, i);
				//console.log("empty Item " + this.emptyItem + " vs " + i);
				//console.log("2 check " + this.parentList[this.listItems[i]].objID + " vs " + this.emptyItem
				object.owner = this;
				object.objID = this.listItems[i];
				object.addEventListener("click", function () {

					//console.log("set slected to " + object.objID + " which equals " + this.owner);
					object.parentNode.parentNode.remove();
					SlclearTarget(target);

					target.selectedValue = this.owner.parentList[object.objID].objID;

					this.owner.selectedItems[target.selectedIndex] = 0;
					//console.log("unselect item " + target.selectedIndex);
					this.owner.selectedItems[object.objID] = 1;
					target.selectedIndex = object.objID;
					//this.owner.showSelected(object.objID, target);
					//console.log(this.owner.selectedItems);
					renderFunction(object.owner.parentList[object.objID], target);
					selectFunction();
					});
				} else {
					console.log("error case 33 " + this.selectedItems[i] + " == 0; " + this.parentList[this.listItems[i]].objID + " != " + this.emptyItem);
					console.log(this.parentList);
				}
			}
		}
	}

	SLmultiSelect(target, limit) {
		var selButton = addDiv("", "button", showContain);
		selButton.innerHTML = "Select Thsese";
		selButton.owner = this;
		selButton.addEventListener("click", function () {
			selButton.parentNode.remove();
			SlclearTarget(target);
			});
	}
}

class uList extends objectList {
	constructor(parentList, opts) {
		super();
		console.log(parentList);
		this.listItems = Object.keys(parentList);
		this.selectedItems = Array(this.listItems.length).fill(0);
		//this.selectedItems.fill(0, 0, 10);

		if (typeof opts !== "undefined") {
			console.log("review otts")
			//if (opts.items.length > 0) this.listItems = opts.items;
			this.listItems = opts.items || this.listItems;
			this.prefix = opts[1] || 1;
			if (opts.useItems) {
				console.log("select list items");
				this.listItems = opts.useItems;
			}
		}
		this.startItems = this.listItems;
		this.parentList = parentList;

	}

	reset() {
		this.selectedItems = Array(this.listItems.length).fill(0);
	}

	existingValue(target, opts) {
		//console.log("ulist existing to " + opts.setVal);
		//console.log(this.parentList);
		for (var i=0; i<this.parentList.length; i++) {
			if (this.parentList[i].objID == opts.setVal) {
				//console.log(opts.setVal + " found at " + i);
					this.selectedItems[i] = 1;
					this.showSelected(i, target);

					target.selectedValue = this.parentList[i].objID;
					target.selectedIndex = i;
					break;
			}
		}

	}

	getValue(trg) {
		console.log("return a value of " + trg.selectedValue);
		console.log(trg);
		return "2,"+trg.selectedValue;
		//if (trg.selectedValue) return "2,"+trg.selectedValue;
		//return false;
	}

	showItem(id, trg) {
		//console.log(id);
		var objBox = id.renderSummary(trg);
		//console.log("Show the default uList item " + objBox)
		return objBox;
	}

	showSelected(id, trg) {
		SlclearTarget(trg);
		//trg.innerHTML = id;
		trg.listItem = this;
		trg.selectedValue = this.parentList[id].objID;
		//console.log("select item " + id);
		//console.log("set value to " + this.parentList[id].objID);
		this.parentList[id].renderSummary(trg);
	}

	typeIcon(trg) {
		let objBox = addDiv("", "rscContain", trg);
		objBox.innerHTML = "resources";

		return objBox;
	}
}

class saleList extends uList {
	constructor(parentList, opts) {
		super(parentList, opts);
		/*
		this.listItems = Object.keys(parentList);
		this.prefix = 1;
		this.slideDefault = false;
		//console.log(opts);
		if (typeof opts !== "undefined") {
			//console.log("run opts");
			//if (opts.items.length > 0) this.listItems = opts.items;
			this.listItems = opts.items || this.listItems;
			this.prefix = opts.prefix || 1;
			this.slideDefault = opts.max || false;
		}
		this.parentList = parentList;

		//console.log(this);*/
	}
	/*
	getValue(trg) {
		return this.prefix + "," + trg.selectedValue+","+trg.showBox.slider.slide.value;
	}

	existingValue(target, opts) {
		console.log("set exsit ofr rsc")
		this.showSelected(opts.setVal, target);
		setSlideQty(target.showBox, opts.setQty);
		target.showBox.slider.slide.value = opts.setQty;
		console.log(target.showBox);
	}

	showItem(id, trg) {
		let objBox = addDiv("", "rscContain", trg);
		let objContent = addDiv("", "rscImg", objBox);
		let newImg = addImg(id, "rscImg", objContent);
		objBox.style.background = "white";
		objContent.innerHTML = id.objID;
		newImg.src = "./rscImages/"+id+".png";
		newImg.alt = id;

		return objBox;
	}

	showSelected(id, trg) {
		SlclearTarget(trg);
		trg.selectedValue = this.parentList[id].objID;
		trg.showBox = slideBox(trg,0);
		//trg.showBox.unitSpace.innerHTML = id;
		console.log(this.parentList[id]);
		this.parentList[id].renderSummary(trg.showBox.unitSpace);
		if (this.slideDefault) setSlideQty(trg.showBox, this.slideDefault);
		else setSlideQty(trg.showBox, this.parentList[id].qty);
		trg.listItem = this;
	}

	typeIcon(trg) {
		let objBox = addDiv("", "rscContain", trg);
		objBox.innerHTML = "resources";

		return objBox;
	}

	selectItem (trg) {
		trg.className = "rscContainSelected";
	}

	unselectItem(trg) {
		trg.className = "rscContain";
	}
*/
}

class multiList extends objectList {
	constructor(parentList, opts) {
		super();
		this.listItems = Object.keys(parentList);
		if (typeof opts !== "undefined") {
			if (opts.items.length > 0) this.listItems = opts.items;
			this.prefix = opts[1] || 1;
		}
		this.parentList = parentList;
	}

	existingValue(target, opts) {
		console.log("multi target to " + opts.list + " index " + [opts.setVal]);
		opts.list.existingValue(target, opts);
	}

	getValue(trg) {
		return "2,"+trg.selectedValue;
	}

	showItem(id, trg) {
		let objBox = addDiv("", "objContain", trg);
		let objContent = addDiv("", "objContent", objBox);
		objContent.innerHTML = id;

		return objBox;
	}

	showSelected(id, trg) {
		SlclearTarget(trg);
		trg.innerHTML = id;
		trg.listItem = this;
		trg.selectedValue = id;
	}

	typeIcon(trg) {
		let objBox = addDiv("", "rscContain", trg);
		objBox.innerHTML = "resources";

		return objBox;
	}
}

newButton = function(trg, action) {
	button1 = addDiv("button1", "button", trg);
	button1.addEventListener("click", action);

	button1.innerHTML = "button";

	return button1;
}


SlclearTarget = function(trg) {
	//console.log("show object " + this);
	while (trg.firstChild) {
		trg.removeChild(trg.firstChild);
	}
}

SLreadSelection = function(trg) {
	//console.log(trg);
	return(trg.listItem.getValue(trg));
}

SLsortBy = function (listObj, prm) {
	if (listObj.sortBy == prm) {
		listObj.sortDir *= -1;
	} else listObj.sortDir = 1;

	listObj.sortBy = prm;
	console.log(listObj.parentList);
	console.log("check against " + prm)
	listObj.parentList.sort(function (a, b) {
		console.log(a[prm] + " vs " + b[prm]);
		return (a[prm] - b[prm])*listObj.sortDir});
	//console.log(listObj.parentList);
}

SLFilterBy = function(listObj, prop, val) {
	console.log("run filter");
	let showList = [];
	for (var i=0; i<listObj.listItems.length; i++) {
		if (listObj.parentList[listObj.listItems[i]][prop] == val) {
			console.log(listObj.parentList[listObj.listItems[i]][prop] + " = " + val);
			showList.push(listObj.listItems[i]);
		} else {
			console.log(listObj.parentList[listObj.listItems[i]][prop] + " != " + val)
		}
	}
	listObj.listItems = showList;
}
