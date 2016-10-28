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
		
		console.log(this.sortOptions);
	}

	SLsingleButton(target, opts) {

		var selectButton = addDiv("b1", "button", target);
		selectButton.innerHTML = "button";
		let item = this;
		selectButton.listItem = this;
		selectButton.addEventListener("click", function () {item.SLsingleSelect(selectButton)});
		selectButton.selectedValue = 0;
		if (typeof opts !== "undefined") {
			if (opts.setVal) {
				console.log("set existing");
				this.existingValue(selectButton, opts);
			}
		}
		console.log(selectButton);
		return selectButton;
	}

	SLsingleSelect(target) {
		var showContain;
		if (document.getElementById("selectMenu")) showContain = document.getElementById("selectMenu");
		else showContain = addDiv("selectMenu", "selectMenu", "gmPnl");
		
		showContain.sortBar = addDiv("", "button", showContain);
		showContain.sortBar.style.backgroundColor = "white";
		showContain.sortBar.style.position = "relateive";
		showContain.sortBar.style.float = "left";
		showContain.sortBar.style.width = "99%";
		
		
		let sortTarget = this;
		console.log(this.sortOptions);
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
		console.log(showContain);
		showContain.content.innerHTML = "";

		
		console.log(this);
		
		
		this.SLshowList(target, showContain.content);
	}
	
	SLshowList(target, selectContainer) {
		console.log(target);
		selectContainer.innerHTML = "";
		for (var i=0; i<this.listItems.length; i++) {
			if (this.parentList[this.listItems[i]] instanceof objectList) {
				console.log("list of lists");
				let object = this.parentList[this.listItems[i]].typeIcon(selectContainer);
				let subtarg = this.parentList[this.listItems[i]];
				if (this.parentList[this.listItems[i]] != "undefined") object.addEventListener("click", function () {
					subtarg.SLsingleSelect(target, function() {})
					});
			} else {
			console.log(this);
			let object = this.showItem(this.parentList[this.listItems[i]], selectContainer);
			object.owner = this;
			object.objID = this.listItems[i];
			object.addEventListener("click", function () {
				console.log("set slected to " + object.objID)
				object.parentNode.parentNode.remove();
				SlclearTarget(target);
				this.owner.showSelected(object.objID, target);
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


class resourceList extends objectList {
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
		newImg.src = "./rscImages/"+id+".png";
		newImg.alt = id;

		return objBox;
	}

	showSelected(id, trg) {
		SlclearTarget(trg);
		trg.selectedValue = id;
		trg.showBox = slideBox(trg,0);
		trg.showBox.unitSpace.innerHTML = id;
		if (this.slideDefault) setSlideQty(trg.showBox, this.slideDefault);
		else setSlideQty(trg.showBox, playerRsc[id]);
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
		this.listItems = Object.keys(parentList);
		if (typeof opts !== "undefined") {
			//if (opts.items.length > 0) this.listItems = opts.items;
			this.listItems = opts.items || this.listItems;
			this.prefix = opts[1] || 1;
		}
		this.parentList = parentList;
	}

	existingValue(target, opts) {
		console.log("ulist existing");
		this.showSelected(opts.setVal, target);
	}

	getValue(trg) {
		//console.log(trg);
		return "2,"+trg.selectedValue;
	}

	showItem(id, trg) {
		//console.log(this.parentList);
		console.log(id);
		var objBox = id.renderSummary(trg);
		/*
		let objBox = addDiv("", "objContain", trg);
		let objContent = addDiv("", "objContent", objBox);
		objContent.innerHTML = id;
		*/
		return objBox;
	}

	showSelected(id, trg) {
		SlclearTarget(trg);
		//trg.innerHTML = id;
		trg.listItem = this;
		trg.selectedValue = this.parentList[id].objID;
		console.log(this.parentList[id]);
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
