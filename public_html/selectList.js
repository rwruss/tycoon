class objectList {
	constructor () {
		this.sortOptions = [];
		this.sortNames = [];
		this.sortBy = null;
		this.sortDir = 1;
	}

	addSort(val, desc) {
		this.sortOptions.push(val);
		this.sortNames.push(desc);
	}

	SLsingleButton(target, opts) {

		var selectButton = addDiv("b1", "button", target);
		selectButton.innerHTML = "button";
		let item = this;
		selectButton.listItem = this;
		//selectButton.addEventListener("click", function () {item.SLsingleSelect(selectButton)});
		selectButton.selectedValue = 0;
		var renderFunction = function(x, y) {
				return item.showItem(x, y);
			}
		if (typeof opts !== "undefined") {
			if (opts.setVal) {
				console.log("set value");
				this.existingValue(selectButton, opts);
			}
			if (opts.renderFunction) {

				//selectButton.addEventListener("click", function () {item.SLsingleSelect(selectButton, opts.renderFunction)});
				renderFunction = opts.renderFunction;
			} else {

			}
		}
		//console.log("Make a button with this function");
		//console.log(renderFunction);
		selectButton.addEventListener("click", function () {
			//console.log("passing function " + renderFunction + " from button " + selectButton.innerHTML);
			item.SLsingleSelect(selectButton, renderFunction)
			});
		return selectButton;
	}

	SLsingleSelect(target, renderFunction) {
		//console.log("SLSELECT: " + target.innerHTML + " function is " + renderFunction)
		var showContain;
		if (document.getElementById("selectMenu")) showContain = document.getElementById("selectMenu");
		else showContain = addDiv("selectMenu", "selectMenu", "gmPnl");

		showContain.sortBar = addDiv("", "selectSort", showContain);

		let sortTarget = this;
		for (var i=0; i<this.sortOptions.length; i++) {
		var sortButton = addDiv("", "button", showContain.sortBar)
		sortButton.innerHTML = this.sortNames[i];
		var testVal = this.sortOptions[i];
		sortButton.addEventListener("click", function () {
				SLsortBy(sortTarget, testVal);
				sortTarget.SLshowList(target, showContain.content);
				});
		}


		showContain.content = addDiv("", "", showContain);
		showContain.content.style.float = "left";
		showContain.content.style.position = "relative";
		showContain.content.innerHTML = "";

		//console.log("pass this function");
		//console.log(renderFunction);
		this.SLshowList(target, showContain.content, renderFunction);
	}

	SLshowList(target, selectContainer, renderFunction) {
		//console.log(target);
		selectContainer.innerHTML = "";
		for (var i=0; i<this.listItems.length; i++) {
			if (this.parentList[this.listItems[i]] instanceof objectList) {

				let object = this.parentList[this.listItems[i]].typeIcon(selectContainer);
				let subtarg = this.parentList[this.listItems[i]];
				if (this.parentList[this.listItems[i]] != "undefined") object.addEventListener("click", function () {
					subtarg.SLsingleSelect(target, function() {})
					});
			} else {
			//let object = this.showItem(this.parentList[this.listItems[i]], selectContainer);
			//console.log(renderFunction);
			let object = renderFunction(this.parentList[this.listItems[i]], selectContainer, i);

			object.owner = this;
			object.objID = this.listItems[i];
			//console.log(this.listItems[i]);
			object.addEventListener("click", function () {

				console.log("set slected to " + object.objID + " which equals " + this.owner);
				object.parentNode.parentNode.remove();
				SlclearTarget(target);
				//this.owner.showSelected(object.objID, target);
				renderFunction(object.owner.parentList[object.objID], target);
				});
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


class saleList extends objectList {
	constructor(parentList, opts) {
		super();
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

		//console.log(this);
	}

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

}

class uList extends objectList {
	constructor(parentList, opts) {
		super();
		console.log(parentList);
		this.listItems = Object.keys(parentList);
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

		this.parentList = parentList;

	}

	existingValue(target, opts) {
		console.log("ulist existing");
		for (var i=0; i<this.parentList.length; i++) {
			if (this.parentList[i].objID == opts.setVal) {
					this.showSelected(i, target);
			}
		}

	}

	getValue(trg) {
		console.log("return a value of " + trg.selectedValue);
		console.log(trg);
		return "2,"+trg.selectedValue;
	}

	showItem(id, trg) {
		var objBox = id.renderSummary(trg);
		//console.log("Show the default uList item " + objBox)
		return objBox;
	}

	showSelected(id, trg) {
		SlclearTarget(trg);
		//trg.innerHTML = id;
		trg.listItem = this;
		trg.selectedValue = this.parentList[id].objID;
		console.log("select item " + id);
		console.log("set value to " + this.parentList[id].objID);
		this.parentList[id].renderSummary(trg);
	}

	typeIcon(trg) {
		let objBox = addDiv("", "rscContain", trg);
		objBox.innerHTML = "resources";

		return objBox;
	}
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
