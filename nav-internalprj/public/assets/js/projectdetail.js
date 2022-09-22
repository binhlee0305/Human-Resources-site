var colHeaders = ['EmpCode', 'EmpName', 'Type'].concat(listMonday);
var myTable = null;
var options = {
    data: effortProject,
    rowResize: true,
    columnDrag: true,
    colHeaders: colHeaders,
    minDimensions: [colHeaders.length, effortProject.length],
    allowInsertRow: false,
    allowInsertColumn: false,
    columnSorting: false,
    allowDeleteRow: false,
    //allowDeleteColumn: false,
    allowRenameColumn: false,
    tableOverflow: true,
    tableHeight: '500px',
    tableWidth: '1220px',
    columns: [
        { type: 'text', width: '100' },
        { type: 'text', width: '100' },
        { type: 'text', width: '50' },
    ],
    updateTable: function(instance, cell, col, row, val, label, cellName) {
        if (col == 0) {
            cell.classList.add('readonly');
            cell.style.color = "#000000";
            cell.style.backgroundColor = '#ffec5d';
            cell.style.fontweight = "bold";
        } else
        if (col == 1) {
            cell.classList.add('readonly');
            cell.style.color = "#000000";
            cell.style.backgroundColor = '#5df39c';
        } else
        if (col == 2) {
            cell.classList.add('readonly');
            cell.style.color = "#000000";
        } else
        if (col > 2) {
            // Get text
            cellData = parseInt(cell.innerText);
            if (cell.innerText == "") {
                cell.className = '';
                cell.style.backgroundColor = '#FFFFFF'; //white
            } else {
                cell.innerText = String(cellData) + "%";
                if (cellData >= 100) {
                    cell.className = '';
                    cell.style.backgroundColor = '#f75551'; //red
                } else if (cellData > 85 && cellData < 100) {
                    cell.className = '';
                    cell.style.backgroundColor = '#F9A941'; //ogange
                } else if (cellData > 65 && cellData <= 85) {
                    cell.className = '';
                    cell.style.backgroundColor = '#6aec3d'; //green
                } else if (cellData > 25 && cellData <= 65) {
                    cell.className = '';
                    cell.style.backgroundColor = '#3fe2ff'; //blue
                } else if (cellData > 0 && cellData <= 25) {
                    cell.className = '';
                    cell.style.backgroundColor = '#ccc'; //gray
                } else if (cellData <= 0) {
                    cell.className = '';
                    cell.style.backgroundColor = '#FFFFFF'; //white
                }
            }
        }
        updateEffort(this.data);
    },
};


$(document).ready(function() {

    $('.list').select2({
        width: '100%'
    });

    $('#status').select2({
        width: '100%'
    });

    $('#proj_member_type_b').select2({
        width: '100%',
    });
    $('#proj_member_type_b').val(proj_member_type_b);
    $('#proj_member_type_b').trigger('change');

    $('#proj_member_type_s').select2({
        width: '100%',
    });
    $('#proj_member_type_s').val(proj_member_type_s);
    $('#proj_member_type_s').trigger('change');

    $('#proj_member_type_n').select2({
        width: '100%',
    });
    $('#proj_member_type_n').val(proj_member_type_n);
    $('#proj_member_type_n').trigger('change');

    myTable = jexcel(document.getElementById('spreadsheet'),options);
    myTable.deleteColumn(colHeaders.length, 1);
    myTable.hideIndex();

    $("#projEffortBtn").click(function() {
        loading(true);
        var data = {
            "new_data": myTable.getJson(),
            "old_data": effortProject,
        };
        var url = project.id + "/projEffort";
        var _headers = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            data: data,
            url: url,
            contentType: 'json',
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': _headers
            },
            success: function(data, textStatus, jqXHR) {
                console.log('AJAX call successful.');
                console.log(data);
                loading(false);
                showInfoMessage("Notification", "Successful", function() {
                    location.reload();
                }, function() {
                    location.reload();
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {

                console.log('AJAX call failed.');
                console.log(textStatus + ': ' + errorThrown);
                loading(false);
                showInfoMessage("Notification", "Can't Update Effort", function() {
                    location.reload();
                }, function() {
                    location.reload();
                });
            }
        });

    });
});

function updateEffort(data) {
    var sumRowAssign = 0;
    var sumRowBill = 0;
    for (let index = 0; index < data.length; index++) {
        for (let k = 3; k < data[index].length; k++) {
            sumRowAssign += parseFloat(data[index][k]); 
        }
    }
    for (let index = 0; index < data.length; index++) {
        if(data[index][2] == "B"){
            for (let k = 3; k < data[index].length; k++) {
                sumRowBill += parseFloat(data[index][k]); 
            }
        }
    }
    var assigneeEffort = (sumRowAssign*5*0.01).toFixed(2);
    var billEffort =  (sumRowBill*5*0.01).toFixed(2);
    $("#assigned").text("Assigned: "+ assigneeEffort +" man.day");
    $("#billable").text("Billable: "+ billEffort +" man.day");
}