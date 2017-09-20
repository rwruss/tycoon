class transaction {
  constructor(dat) {
    this.date = dat[0];
    this.card = dat[2];
    this.amount = dat[1]/100;
    this.category = dat[3];
    this.desc = dat[4];
  }

  tableLine (trg) {
    let newRow = addDiv("", "transRow", trg);

    newRow.date = addDiv("", "transRowDate", newRow);
    newRow.card = addDiv("", "transRowDate", newRow);
    newRow.amount = addDiv("", "transRowDate", newRow);
    newRow.category = addDiv("", "transRowDate", newRow);
    newRow.desc = addDiv("", "transRowDesc", newRow);

    let date = new Date(this.date*1000);

    newRow.date.innerHTML = (date.getMonth()+1) + " / " + date.getUTCDate() + " / " + date.getFullYear();
    newRow.card.innerHTML = this.card;
    newRow.amount.innerHTML = this.amount.toFixed(2);
    newRow.category.innerHTML = this.category;
    newRow.desc.innerHTML = this.desc;
  }
}
