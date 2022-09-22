function addCheckbox(val) {
    if (checkboxVal == "")
        checkboxVal += val;
    else
        checkboxVal += "|" + val;
}

function subCheckbox(val) {
    let start = checkboxVal.indexOf(val);
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
        let regex = "^(?:" + checkboxVal + ")$";
        $('#advanced_table').DataTable().column(i).search(
            regex, true, false
        ).draw();
    }

}
/**
 * Check id project
 * 
 * @return [bool], false if existed else true
 */
 function checkId(id) {
    let bool = true;
    $.ajax({
        type: 'get',
        url: 'project/checkId/' + id,
        dataType: 'json',
        async: false,
        success: function(data) {
            bool = (data.res == "Valid") ?  true :  false; 
            loading(false);
        },
        error: function(err) {
            if (jqXHR.status == 403) {
                alert(errorThrown);
            } else {
                alert("ProjectID already existed!!!");
            }
            bool = false;
        }
    });
    return bool;
}

/**
 * Validate after submit Add Project Form
 * 
 * @return [bool]
 */
validateForm = () => {
    let id = $('form #id').val();
    let name = $('form #name').val();
    let start_date = $('form #start_date').val();
    let end_date = $('form #end_date').val();
    let total_effort = $('form #total_effort').val();
    let id_client = $('form #id_client').val();
    let id_pm = $('form #id_pm').val();
    //let id_dev = $('form #id_dev').val();
    let id_b = $('form #proj_member_type_b').val();
    // let id_s = $('form #proj_member_type_s').val();
    // let id_n = $('form #proj_member_type_n').val(); 
    let status = $('form #status').val();

    if (id == '' || name == '' || status == '' || start_date == '' || end_date == '' || total_effort == '' || id_client == '' || id_pm == '' || id_b == ''  || (start_date > end_date)) {
        alert('Error!!!\nCheck empty information or Invalid date');
        return false;
    }

    // if (!checkId(id)) {
    //     return false;
    // }
    return true;
}

$(document).ready(function() {
    //Checkbox filter
    $('input.checkbox_filter').change(function() {
        let val = $(this).val();
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
    //Create item (Add project)
    $('.forms-sample').on('submit', function(event) {

        event.preventDefault();
        let formData = new FormData(this);
        let id = $('form #id').val();
    if(checkId(id) == true)
    {
        if (validateForm() == false) {
            return false;
        } else {
            $("#exampleModalLong").modal('hide');

            loading(true);

            $.ajax({
                type: 'post',
                url: 'project/add',
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                        //loading(false);
                        alert("Add new Project successfully");
                        window.location.replace("project");
                },
                error: function(err) {
                    loading(false);
                    if (jqXHR.status == 403) {
                        showInfoMessage(errorThrown, "You are not allowed");
                    } else {
                        showInfoMessage("Notification", "Failed to create Project");
                        $("#exampleModalLong").modal('show');
                    }
                }
            })
        }
    }else {
        alert("ProjectID already existed!!!");
    }
});

    // Delete item events
    $("#advanced_table").on('click', 'tbody tr .btn_delete', function(event) {
        event.preventDefault();

        let element = $(this).parents('tr');
        let id = $(this).data("id");

        showInfoMessage("Confirm Delete", "Do you want to delete?", function() {
            hideInfoMessage();
            //Logic to delete the item
            loading(true);
            $.ajax({
                type: 'get',
                url: 'project/delete/' + id,
                success: function(res) {
                    advanced_table.row(element).remove().draw();
                    alert("Delete successfully!");
                    //loading(false);
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

    //Clear search
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
    })
})