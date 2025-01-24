/***
 * This function is used for empty values for input field
 */
$(".resetData").on('click', function () {
    $('.empty-data').val('');
});

function nameConvertSlug() {
    console.log('hdhsjf');
    var name = document.getElementById("title");
    var nameValue = name.value;
    var Slug = convertToSlug(nameValue);
    var input = $("#slug");
    input.val("");
    input.val(input.val() + Slug);
}


function convertToSlug(Text) {
    return Text.toLowerCase()
        .replace(/[^\w ]+/g, '')
        .replace(/ +/g, '-');
}

function bulk_delete() {
    if ($("#main_checkbox").is(":checked") || $(".children_checkbox").filter(':checked').length > 0) {
        var url = $('#bd_frm').attr('action');

        bulk_delete_confirmation(url);

    }
}

function restore_all() {
    var selected_checkbox = "";
    if ($("#main_checkbox").is(":checked") || $(".children_checkbox").filter(':checked').length > 0) {
        $('.children_checkbox').map(function (key, value) {
            if ($(value).is(':checked')) {
                $("#restore_frm").append('<input name=selected_checkbox[' + $(value).data('id') + '] />');
            }
        });
        $('#restore_frm').submit();
    }
}

/** Hard delete data**/
function myHardDeleteFunction() {
    if ($("#main_checkbox").is(":checked") || $(".children_checkbox").filter(':checked').length > 0) {
        var url = $('#bd_hard_frm').attr('action');
        bulk_hard_delete_confirmation(url);

    }
    return false;
}

$(document).ready(function () {

    $('.float-number').keypress(function(event) {
        if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
            event.preventDefault();
        }
    });

    $(document).on("input", ".numeric", function() {
        this.value = this.value.replace(/\D/g,'');
    });

    $(document).on("click", ".children_checkbox", function () {
        $("#main_checkbox").prop('checked', false);
        if ($(this).is(":checked")) {
            $(this).prop('checked', true);
            let data_id = $(this).data('id');
            if(data_id !== undefined){
                // $("#bulk_delete_frm").append('<input name="bd["0"]"  />');
                $("#bulk_delete_frm").append('<input name="bd[' + data_id + ']" class="remove_name"  />');
                $("#bulk_hard_delete_frm").append('<input name="bd[' + data_id + ']" class="remove_name" />');

            }
        } else {
            $(this).prop('checked', false);
            let data_id = $(this).data('id');
            if(data_id !== undefined){
                $('input[name="bd[' + data_id + ']"]').val("");
                $('input[name="bd[' + data_id + ']"]').remove();
            }
        }
    });

    $(document).on("click", "#main_checkbox", function () {
        $(".children_checkbox").prop('checked', !$(".children_checkbox").prop("checked"));
        if ($(this).is(":checked")) {
            $("#bulk_delete_frm").append('<input name="bd[""]"  />');
            $(".children_checkbox").map(function (key, value) {
                let data_id = $(value).data('id');
                $("#bulk_delete_frm").append('<input name="bd[' + data_id + ']" class="remove_name" />');
                $("#bulk_hard_delete_frm").append('<input name="bd[' + data_id + ']" class="remove_name"  />');
            });
            $(".children_checkbox").prop("checked", true);
        } else {
            $("#bulk_delete_frm").find('input').not(":first").remove();
            $("#bulk_hard_delete_frm").find('input').not(":first").remove();
            $("#bulk_delete_frm").find('input').not(":first").val("");
            $("#bulk_hard_delete_frm").find('input').not(":first").val("");
            $(".children_checkbox").prop("checked", false);
        }
    });

    /* hide btn */
    $(document).on("click", ".only_active .children_checkbox", function () {
       // console.log($(".only_active .children_checkbox").filter(':checked').length);
        if ($(".only_active .children_checkbox").filter(':checked').length > 0) {
            $('#delete-btn, #bulk-add-btn').removeClass('d-none');

        } else {
            $('#delete-btn, #bulk-add-btn').addClass('d-none');
        }
    });
    $(document).on("click", ".only_active #main_checkbox", function () {
        if ($(this).is(":checked") && $(".only_active .children_checkbox").filter(':checked').length > 0) {
            $('#delete-btn, #bulk-add-btn').removeClass('d-none');
        } else {
            $('#delete-btn, #bulk-add-btn').addClass('d-none');
        }
    });


    // $(document).on("click","#main_checkbox",function(){
    //     $("delete-btn").removeClass("d-none");
    // })
    // alert($("input[name='delete-btn']").val());

    $(document).on("click", ".only_deleted .children_checkbox", function () {
      //  console.log($(".only_deleted .children_checkbox").filter(':checked').length);
        if ($(".only_deleted .children_checkbox").filter(':checked').length > 0) {
            $('#restore-btn').removeClass('d-none');
            $('#hard-delete-btn').removeClass('d-none');
        } else {
            $('#restore-btn').addClass('d-none');
            $('#hard-delete-btn').addClass('d-none');
        }
    });
    $(document).on("click", ".only_deleted #main_checkbox", function () {
       // console.log($(".only_deleted .children_checkbox").filter(':checked').length);
        if ($(this).is(":checked") && $(".only_deleted .children_checkbox").filter(':checked').length > 0) {
            $('#restore-btn').removeClass('d-none');
            $('#hard-delete-btn').removeClass('d-none');
        } else {
            $('#restore-btn').addClass('d-none');
            $('#hard-delete-btn').addClass('d-none');
        }
    });

    $('#swWeather').on('switchChange.bootstrapSwitch', function(event, state) {
        $('#delete-btn').addClass("d-none");
        $('#restore-btn').addClass("d-none");
        $('#hard-delete-btn').addClass("d-none");
        $(".children_checkbox").prop("checked", false);

    });
});
