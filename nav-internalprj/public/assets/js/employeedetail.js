var colHeaders = ['Project ID','Project Name','Type'].concat(listMonday);
// Get sum method
var sumCol = function(data){
    listProject = JSON.parse(JSON.stringify(data))
    sum = 0;
    var obj = listProject.reduce(function(sum, obj ) {
        return Object.keys(sum).reduce(function( rs ,key ) {
            rs[key] = sum[key]+obj[key]
            return rs
        },{})
    })
    obj.ProjID ='Total'
    obj.ProjName =''
    obj.Type =''
    data.push(obj)
    return data;
}
//Set Width Colunms
var setCSS = function(col){
    var styleCol = [];
    for(i=0;i<=col;i++){
        if(i<3){
            styleCol.push({type: 'text', width:'150'})
        }
        else{
            styleCol.push({type: 'text', width: '75'})
        }
    }
    return styleCol;
}
//Options Jexcel
var options = {
    data:sumCol(data),
    rowResize:true,
    columnDrag:true,
    colHeaders: colHeaders,
    allowInsertRow: true,
    allowInsertColumn: true,
    allowManualInsertColumn:true,
    columnSorting: false,
    allowDeleteRow: false,
    pagination:20,
    //allowDeleteColumn: false,
    allowRenameColumn: false,
    tableOverflow:true,
    tableHeight:'500px',
    tableWidth:'100%',
    columns: setCSS(colHeaders.length-1),
    updateTable:function(instance, cell, col, row, val, label, cellName) {
        if (col==0){
            cell.classList.add('readonly');
            cell.style.color = "#000000";
            cell.style.backgroundColor = '#ffec5d'; 
            cell.style.fontweight = "bold";   
        }
        else
        if (col==1){
            cell.classList.add('readonly');
            cell.style.color = "#000000";
            cell.style.backgroundColor = '#5df39c'; 
        }
        else
        if (col==2){
            cell.classList.add('readonly');
            cell.style.color = "#000000";
        }
        else 
        if (col > 2) {
            // Get text
            cellData = parseInt(cell.innerText);
            if (cell.innerText=="") {
                cell.className = '';
                cell.style.backgroundColor = '#FFFFFF'; //white
            }
            else
            {
                cell.innerText = String(cellData) + "%";
                if (cellData>=100) {
                    cell.className = '';
                    cell.style.backgroundColor = '#f75551'; //red
                }
                else if (cellData>85 && cellData<100) {
                    cell.className = '';
                    cell.style.backgroundColor = '#F9A941'; //ogange
                }
                else if (cellData>65 && cellData<=85) {
                    cell.className = '';
                    cell.style.backgroundColor = '#6aec3d';  //green
                }
                else if (cellData>25 && cellData<=65) {
                    cell.className = '';
                    cell.style.backgroundColor = '#3fe2ff'; //blue
                }
                else if (cellData>0 && cellData<=25) {
                    cell.className = '';
                    cell.style.backgroundColor = '#ccc'; //gray
                }
                else if (cellData<=0) {
                    cell.className = '';
                    cell.style.backgroundColor = '#FFFFFF'; //white
                }
            }
        }
          if (true){
            cell.classList.add('readonly');
            cell.style.color = "#000000"
        }
    },
};
var myTable = jexcel(document.getElementById('spreadsheet'), options);
$(document).ready(function() {
    $("#fromDate").val(arrayMondays[0]);
    $("#toDate").val(arrayMondays[arrayMondays.length-1]);
    myTable.setMerge('A'+data.length,3,1)
    myTable.setStyle('A'+data.length, 'font-weight', 'bold')
    myTable.hideIndex();
    myTable.setWidth(1, 200)
    console.log($('#spreadsheet').outerWidth(true));
});

$(document).ready(function() {
    $(".search-effort-employee").on("submit", function(event) {
        event.preventDefault();
        document.getElementById('loading').style.display = 'block';
        let formData = new FormData(this);
        let startDate = $("#fromDate").val();
        let endDate = $("#toDate").val(); 
        if (startDate > endDate) {
            alert("Start Date and End Date Invalid");
            document.getElementById('loading').style.display = 'none';
            return;
        }
        else {
            var id = $('.btn-confirmreset').attr('data-id');
            $.ajax({
                type: 'post',
                async: true,
                url: '/employee/'+id+'/searchEffortEmployee',
                data: formData,
                contentType: false,
                processData: false,
                cache: false,
                dataType: "json",
                success: function(res) {
                    document.getElementById('loading').style.display = 'none';
                    loading(false);
                    myTable.destroy();                    
                    var colHeader = ['EmpCode','EmpName','Type'].concat(res.listMonday);
                    myTable.deleteColumn(3,myTable.options.colHeaders.length-1);    
                    options.data = sumCol(res.dataTable);
                    console.log(options.data)
                    options.colHeaders = colHeader;
                    options.columns = setCSS(colHeader.length-1);
                    newTable = jexcel(document.getElementById('spreadsheet'), options);
                    newTable.setMerge('A'+data.length,3,1)
                    newTable.setStyle('A'+data.length, 'font-weight', 'bold')
                    newTable.hideIndex();
                    newTable.setWidth(1, 200)
                    myTable = newTable;
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    document.getElementById('loading').style.display = 'none';
                    loading(false);
                    if (jqXHR.status == 403) {
                        showInfoMessage(errorThrown, "You are not allowed");
                    } else {
                        showInfoMessage("Notification", "Failed to search");
                        $("#exampleModalLong").modal('show');
                    }
                }
            })
        }
    });
})
