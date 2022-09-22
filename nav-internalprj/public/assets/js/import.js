$(document).ready(function() {
    uploadFile();
});

$(document).on("click", ".browse", function() {
    var file = $(this).parents().find(".file");
    file.trigger("click");
});
// Function import file
function uploadFile(){
    var input = document.getElementById('uploadFile');
    input.addEventListener('change',async function(){
        $(this)
        .parent()
        .find(".form-control")
        .val(
        $(this)
            .val()
            .replace(/C:\\fakepath\\/i, "")
        );
        var dataNewEmp = await uploadFileEmployee(input);
        importEmployee(dataNewEmp);
        importProject(input.files[0]);
    });
}

function importProject(data) {
    let formData = new FormData();
    formData.append("data", data);
    $.ajax({
        type: 'post',
        url: 'import/assignment',
        data: formData,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        contentType: false,
        processData: false,
        success: function(res) {
            if(res == "Faild"){
                alert("OKe") 
             }
            else if(res == "Existed"){
                alert("Data has been imported from before");
            }
            else{
                 alert("Data has been imported!")
             }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            loading(false);
            if (jqXHR.status == 403) {
                showInfoMessage(errorThrown, "You are not allowed");
            } else {
                showInfoMessage("Notification", "Failed to create Assignment");
                $("#exampleModalLong").modal('show');
            }
        }
    });
}
// Function get new employee from file.
async function uploadFileEmployee(input){
    var dataNewEmp = [];
    var data = await readXlsxFile(input.files[0])
    var i = 4;
    var dataEmp = [];
    for(var i = 3; i < data.length-1; i++){
        var dataRow = [];
        for(var j = 0; j<= 2 ; j++){
            if(data[i][j]){
                dataRow.push(data[i][j]);
            }
        }
        if(dataRow.length != 0){
            dataEmp.push(dataRow);
        }
    }
    dataEmp.forEach((emp)=>{
        var objEmp ={};
        objEmp.id = emp[0];
        objEmp.name = emp[1];
        objEmp.join_date = convert(emp[2]);
        dataNewEmp.push(objEmp);
    })
    return dataNewEmp
}

function importEmployee(data) {
    $.ajax({
        type: 'post',
        url: 'import/employee',
        data: JSON.stringify(data),
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        dataType: 'json',
        contentType: 'application/json',
        success: function(res) {
            if(res.status == "Faild"){
                alert("Oke") 
             }
            else if(res.status == "Existed"){
                alert("Existed");
            }
            else{
                console.log(res);
                if(!res.EmpExisted){
                    alert("Fine")
                }else{
                    header = ["Employee Code","Employee Name","Join Date"]
                    showDataInfo("List employee already exists",header,res.EmpExisted);
                }
             }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            loading(false);
            if (jqXHR.status == 403) {
                showInfoMessage(errorThrown, "You are not allowed");
            } else {
                showInfoMessage("Notification", "Failed to create Employee");
                $("#exampleModalLong").modal('show');
            }
        }
    });
}

function convert(str) {
    var date = new Date(str),
    mnth = ("0" + (date.getMonth() + 1)).slice(-2),
    day = ("0" + date.getDate()).slice(-2);
    return [date.getFullYear(), mnth, day].join("-");
}

function showDataInfo(title, header, data, close) {
    //add title
    $("#message-dialog-data .modal-header .modal-title-data").html(title);

    //add header table
    header.forEach((head) => {
        var tdHeader = document.createElement("td");
        tdHeader.innerHTML = head;
        $("#message-dialog-data .modal-body .header-table").append(tdHeader);
    })

    //add content
    data.forEach(function(row) {
        //create row
        var rowEmp = document.createElement("tr");
        //create col data
        var tdEmpCode = document.createElement("td");
        tdEmpCode.innerHTML = row.id;
        var tdEmpName = document.createElement("td");
        tdEmpName.innerHTML = row.name;
        var tdJoinDate = document.createElement("td");
        tdJoinDate.innerHTML = row.join_date;
        //append col into row
        rowEmp.appendChild(tdEmpCode)
        rowEmp.appendChild(tdEmpName)
        rowEmp.appendChild(tdJoinDate)
        //append row into table modal
        $("#message-dialog-data .modal-body .body-data").append(rowEmp);
    })

    //show modal dialog
    $("#message-dialog-data").modal('show');

    //pass close function to close action
    $("#message-dialog-data #close-modal").on("click", close);
}


function hideInfoMessageData() {
    $("#message-dialog-data").modal('hide');
}