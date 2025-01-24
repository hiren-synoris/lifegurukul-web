$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

//Youtube URL validation
$.validator.addMethod(
    "youtubeURL",
    function (value) {
        return /^(?:https?:\/\/)?(?:m\.|www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/.test(
            value
        );
    },
    "Please enter a valid Youtube Link."
);

//VImeo URL validation
$.validator.addMethod(
    "vimeoURL",
    function (value) {
        return /(?:https?:\/\/(?:www\.)?)?vimeo.com\/(?:channels\/|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/.test(
            value
        );
    },
    "Please enter a valid Vimeo Link."
);

//Less than or Equal validation
$.validator.addMethod(
    "lessThanEqual",
    function (value, element, param) {
        return (
            this.optional(element) ||
            parseInt(value) <= parseInt($(param).val())
        );
    },
    "Must be less than or equal to List price"
);

$.validator.addMethod(
    "greaterThanEqual",
    function (value, element, param) {
        return (
            this.optional(element) ||
            parseInt(value) >= parseInt($(param).val())
        );
    },
    "Must be greater than or equal to Final payable price"
);

$.validator.addMethod('filesize', function(value, element, param) {
    return this.optional(element) || (element.files[0].size <= param)
}, "File size is too big.");

function success(url) {
    if (url != "" || url != undefined) {
        Swal.fire({
            icon: "success",
            title: "success",
            text: "Chapter Item added successfully!",
        }).then(function () {
            window.location.href = url;
        });
    }
}

function fail() {
    Swal.fire({
        icon: "error",
        title: "error",
        text: "Blank Chapter can't be created",
    });
}

/**
 * Common function for delete method
 * @param Route (just pass proper route/URL)
 * @return SweetAlert notification if Delete, and after page has been reload/refresh
 */
function confirmDelete(url) {
    Swal.fire({
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover it!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, Delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: "DELETE",
                data: {},
                dataType: "JSON",
                beforeSend: function() {
                    $('#loader_section').show();
                },
                success: function (response) {
                    $('#loader_section').hide();
                    console.log(response);
                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: "Record deleted successfully!",
                    }).then(function () {
                        if (response.url == 1) {
                            location.reload();
                        } else if (response.url && response.url != 1) {
                            window.location = response.url;
                        } else {
                            location.reload();
                        }
                    });
                },
            });
        }
    });
}

function confirmDeleteNoReload(url) {
    Swal.fire({
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover it!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, Delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: "DELETE",
                data: {},
                dataType: "JSON",
                beforeSend: function() {
                    $('#loader_section').show();
                },
                success: function (response) {
                    $('#loader_section').hide();
                    if(response.content != '' || response.content != undefined){
                        $("#deviceUID_"+response.content).hide();
                    }
                },
            });
        }
    });
}

function copyUrl(url) {
    console.time('time1');
    var temp = $("<input>");
    $("body").append(temp);
    temp.val(url).select();
    document.execCommand("copy");
    alert("URL copied!");
    temp.remove();
    console.timeEnd('time1');
}
