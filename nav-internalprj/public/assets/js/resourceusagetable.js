var colHeaders = ['EmpCode','EmpName'].concat(listMonday);
console.log(dataTable);
var options = {
    data:dataTable,
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
    columns: [
        { type: 'text', width:'100'},
        { type: 'text', width:'150'},
    ],
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
        if (col >= 2) {
            // Get text
            cellData = parseInt(cell.innerText);
            if (cell.innerText == "") {
                cell.className = '';
                cell.style.backgroundColor = '#FFFFFF'; //white
            }
            else
            {
                cell.innerText = String(cellData) + "%";
                if(cell.innerText=="NaN%"){
                    cell.innerHTML  = "E";
                    cell.className = '';
                    cell.style.fontWeight = 'bold';
                    cell.style.backgroundColor = '#636b67'; //grey
                }
                else if (cellData>=100) {
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
            cell.classList.add('readonly');
            cell.style.color = "#000000";
        }
    },
};

var myTable = jexcel(document.getElementById('spreadsheet'), options);
$(document).ready(function() {
    $("#fromDate").val(arrayMondays[0]);
    $("#toDate").val(arrayMondays[arrayMondays.length-1]);
    myTable.hideIndex();
    myTable.setWidth(1, 200)
    console.log($('#spreadsheet').outerWidth(true));
})

$(document).ready(function() {
    $(".search-effortusage").on("submit", function(event) {
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
            $.ajax({
                type: 'post',
                async: true,
                url: 'home/searchEffortUsage',
                data: formData,
                contentType: false,
                processData: false,
                cache: false,
                dataType: "json",
                success: function(res) { 
                    document.getElementById('loading').style.display = 'none';
                    loading(false);
                    myTable.destroy();
                    var colHeader = ['EmpCode','EmpName'].concat(res.listMonday);    
                    myTable.deleteColumn(2,myTable.options.colHeaders.length);
                    options.data = res.dataTable;
                    options.colHeaders = colHeader;
                    newTable = jexcel(document.getElementById('spreadsheet'), options);
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
