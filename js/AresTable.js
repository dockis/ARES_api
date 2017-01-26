var AresTable = function(){
}

AresTable.prototype.listData;
AresTable.prototype.detailData;
AresTable.prototype.sortColumn;
AresTable.prototype.sortOrder;

AresTable.prototype.setListData = function(data) {
    this.listData = data;
}

AresTable.prototype.setDetailData = function(data) {
    this.detailData = data;
}

AresTable.prototype.setSortBy = function(sort) {
    var sortBy = sort.split("-");
    this.sortColumn = sortBy[0];
    this.sortOrder = this.verifyOrder(sortBy[1]);
}

AresTable.prototype.reset = function() {
    this.listData = '';
    this.detailData = '';
    this.sortColumn = '';
    this.sortOrder = '';
}

AresTable.prototype.showDetailTable = function() {
    var detailTable = "<table>";
    detailTable += this.rWrap(this.cWrap("IČO")+this.cWrap(this.detailData.ico)); 
    if(typeof this.detailData.dic !== "undefined" && this.detailData.dic != null){
        detailTable += this.rWrap(this.cWrap("DIČ")+this.cWrap(this.detailData.dic)); 
    }    
    detailTable += this.rWrap(this.cWrap("firma")+this.cWrap(this.detailData.firma)); 
    var ulice = '';
    ulice += (this.emptyVar(this.detailData.ulice))? this.detailData.ulice+' ' : '';
    ulice += (this.emptyVar(this.detailData.cp1))? this.detailData.cp1 : '';
    ulice += (this.emptyVar(this.detailData.cp1) && this.emptyVar(this.detailData.cp2))? '/' : '';
    ulice += (this.emptyVar(this.detailData.cp2))? this.detailData.cp2 : '';
    if(ulice != ''){
        detailTable += this.rWrap(this.cWrap("ulice")+this.cWrap(ulice)); 
    }    
    detailTable += this.rWrap(this.cWrap("město")+this.cWrap(this.detailData.mesto)); 
    detailTable += this.rWrap(this.cWrap("psč")+this.cWrap(this.detailData.psc)); 
    detailTable += "</table>";
    return detailTable;
}

AresTable.prototype.emptyVar = function (variable) {
    return (typeof variable !== "undefined" && variable != null &&  variable != '');
}

AresTable.prototype.showListTable = function() {
    var listTable = "<table>";
    listTable += this.showHeader();
    for( index in this.listData) {
        listTable += "<tr>";
        listTable += this.cWrap(this.listData[index].ico);
        listTable += this.cWrap(this.listData[index].dic);
        listTable += this.cWrap(this.listData[index].firma);
        listTable += this.cWrap(this.listData[index].adresa);
        listTable += this.cWrap(this.showDetailButton(this.listData[index].ico));
        listTable += "</tr>";
    }
    listTable += "</table>";
    return listTable;
}

AresTable.prototype.showHeader = function() {
    var header = "<tr>";
    header += this.hcWrap("IČO"+this.showSortButton("ico", "acs")+this.showSortButton("ico", "desc"));
    header += this.hcWrap("DIČ"+this.showSortButton("dic", "acs")+this.showSortButton("dic", "desc"));
    header += this.hcWrap("firma"+this.showSortButton("firma", "acs")+this.showSortButton("firma", "desc"));
    header += this.hcWrap("adresa"+this.showSortButton("adresa", "acs")+this.showSortButton("adresa", "desc"));
    header += this.hcWrap('');
    header += "</tr>";
    return header;
}

AresTable.prototype.showDetailButton = function(ico) {
    var button;
    button = '<a href="javascript:void(0)" class="detail-button" id="ico-'+ico+'">detail</a>';
    return button;
}

AresTable.prototype.showSortButton = function(column, order) {
    order = this.verifyOrder(order);
    var icon = (order == 'asc')? "▲" : "▼";
    var button;
    if(column == this.sortColumn && order == this.sortOrder) {
        button = '<span>'+icon+'</span>';
    } else {
        button = '<a href="javascript:void(0)" class="sort-button" id="'+column+'-'+order+'">';
        button += icon;
        button += '</a>';
    }
    return button;
}

AresTable.prototype.sortByColumn = function(column, order) {
    order = this.verifyOrder(order);
    if(order == 'asc'){
        return function(x, y) {
            return ((x[column] === y[column]) ? 0 : ((x[column] > y[column]) ? 1 : -1));
        };
    } else {
        return function(x, y) {
            return ((x[column] === y[column]) ? 0 : ((x[column] > y[column]) ? -1 : 1));
        };
    }
};

AresTable.prototype.sortData = function() {
    this.listData.sort(this.sortByColumn(this.sortColumn, this.sortOrder));
}



AresTable.prototype.verifyOrder = function(order) {
    return order = (order == 'asc' || order == 'desc')? order : 'asc';
}
AresTable.prototype.hcWrap = function(cell) {
    return "<th>"+cell+"</th>";
}
AresTable.prototype.cWrap = function(cell) {
    return "<td>"+cell+"</td>";
}
AresTable.prototype.rWrap = function(row) {
    return "<tr>"+row+"</tr>";
}

