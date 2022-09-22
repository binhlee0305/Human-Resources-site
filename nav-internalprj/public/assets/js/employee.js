function addCheckbox(val) {
    if (checkboxVal == "")
        checkboxVal += val;
    else
        checkboxVal += "|" + val;
}

function subCheckbox(val) {
    let start = checkboxVal.indexOf(val);
    console.log(start)
    if (start == 0) {
        if (checkboxVal == val)
            checkboxVal = ""
        else
            checkboxVal = checkboxVal.replace(val + "|", "");
    } else {
        checkboxVal = checkboxVal.replace("|" + val, "");
    }
}

function filterCheckbox(i) {
    if (checkboxVal == "") {
        $('#advanced_table').DataTable().column(i).search("").draw();
    } else {
        let regex =  checkboxVal ; 
        console.log(regex)
        $('#advanced_table').DataTable().column(i).search(
            regex, true, false
        ).draw();
    }
}

validate = () => {
    let id = $("#id").val();
    let name = $("#name").val();
    let username = $("#username").val();
    let password = $("#password").val();
    let status = $("#status").val();
    let id_level = $("#id_level").val();
    let privillege = $("#privillege").val();
    let gender = $("#gender").val();
    let type = $("#id_type").val();

    if (id == '' || name == '' || username == '' || password == '' || status == '' || id_level == '' || privillege == '' || gender == '' || type == '') {
        alert("Please enter fully information !!!");
        return false;
    }

    return true;
}



checkUser = (username) => {
    let checkUsername = true;
    $.ajax({
        type: 'get',
        url: 'employee/checkUser/' + username,
        async: false,
        success: function(data) {
            checkUsername = (data == "Valid") ?  true :  false;
            loading(false);   
        },
        error: function(jqXHR, textStatus, errorThrown) {
            if (jqXHR.status == 403) {
                alert(errorThrown);
            } else {
                alert("Username was existed;");
            }
            checkUsername = false;
        }
    });
    return checkUsername;
}

$(document).ready(function() {
    
    //Checkbox filter
    $('input.checkbox_filter').change(function() {
        let val = $(this).val();
        console.log("checked: "+ val)
        if (this.checked) {
            $(this).prop('checked', true);
            addCheckbox(val);
            filterCheckbox(5);
        } else {
            $(this).prop('checked', false);
            subCheckbox(val);
            filterCheckbox(5);
        }
    });

    //Clear Search
    $("#btn_clear_search").on('click', function() {
        //clear search global
        $("#global_filter").val("");
        filterGlobal();

        //clear search column
        $(".column_filter").each(function() {
            $(this).val("");
            filterColumn($(this).attr('data-column'));
        });

        //clear filter
        $('input:checkbox').prop('checked', false);
        $('#advanced_table').DataTable().column(5).search("").draw();
    });

    $(".forms-employee").on("submit", function(event) {
        event.preventDefault();
        let formData = new FormData(this);
        if (validate()) {
            let username = $("#username").val();
            if (checkUser(username)) {
                $.ajax({
                    type: 'post',
                    url: 'employee/add',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        if(res == "Faild"){
                            alert("Employee code or username already existed") 
                         }else{
                             $("#exampleModalLong").modal('hide');
                             loading(false);
                             alert("Add employee successfully")
                             window.location.replace("employee");
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
                })
            } else {
                console.log("check fail");
                alert('Username already existed!')
            }
        }
    });

    // Delete item events
    $("#advanced_table").on('click', 'tbody tr .btn_delete', function(event) {
        event.preventDefault();
        let element = $(this).parents('tr');
        let id = $(this).data("id");
        showInfoMessage("Confirm Delete", "Do you want to delete this employee?", function() {
            hideInfoMessage();
            //Logic to delete the item
            loading(true);
            $.ajax({
                type: 'get',
                url: 'employee/delete/' + id,
                success: function(res) {
                    advanced_table.row(element).remove().draw();
                    loading(false);
                },
                error: function(err) {
                    loading(false);
                    showInfoMessage("Notification", "Can't delete!");
                }
            });
            //remove click event
            $("#message-dialog #confirm-modal").off("click");
        });
    });

})