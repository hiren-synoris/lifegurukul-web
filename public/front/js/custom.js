(function ($) {
    // "use strict";
    $(".price_modal").on("click", function (e) {
        var url = $(this).attr("data-url");
        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            beforeSend: function () {
                $("#loader_section").show();
            },
            success: function (result) {
                $("#loader_section").hide();
                if (result.status == "success") {
                    $("#PlanModal").html(result.content).modal("show");
                }
            },
        });
    });
    // $('.cool').on('click', function(e) {
    //     $('#PlanModal').modal('show');
    // });

    $(".open_coupon").on("click", function (e) {
        $("#my_coupon").modal("show");
    });

    $("#buttonsearch").click(function () {
        $("#searchform")[0].reset();
        $("#searchbox").val("");
        $("#formsearch").slideToggle("fast", function () {
            $("#content").toggleClass("moremargin");
        });
        $("#searchbox").focus();
        $(".openclosesearch").toggle();
    });

    // Stick Sidebar

    if ($(window).width() > 767) {
        if ($(".theiaStickySidebar").length > 0) {
            $(".theiaStickySidebar").theiaStickySidebar({
                // Settings
                additionalMarginTop: 70,
            });
        }
    }

    if ($(".toggle-password").length > 0) {
        $(document).on("click", ".toggle-password", function () {
            $(this).toggleClass("feather-eye feather-eye-off");
            var input = $(".pass-input");
            if (input.attr("type") == "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });
    }

    $("#plan_status").trigger("click");

    if ($(window).width() <= 991) {
        var Sidemenu = function () {
            this.$menuItem = $(".main-nav a");
        };

        function init() {
            var $this = Sidemenu;
            $(".main-nav a").on("click", function (e) {
                if ($(this).parent().hasClass("has-submenu")) {
                    e.preventDefault();
                }
                if (!$(this).hasClass("submenu")) {
                    $("ul", $(this).parents("ul:first")).slideUp(350);
                    $("a", $(this).parents("ul:first")).removeClass("submenu");
                    $(this).next("ul").slideDown(350);
                    $(this).addClass("submenu");
                } else if ($(this).hasClass("submenu")) {
                    $(this).removeClass("submenu");
                    $(this).next("ul").slideUp(350);
                }
            });
        }

        // Sidebar Initiate

        init();
    }

    // Icon Btn

    $(".course-share .fa-heart").on("click", function (e) {
        e.preventDefault();
        $(this).toggleClass("color-active");
    });

    // Toggle

    if ($("#edit-rating").length > 0) {
        $("#edit-rating").on("click", function () {
            $(".publish-rate").toggle("1000");
            $(".stip-grp").toggle("1000");
        });
    }

    // JQuery counterUp

    if ($(".course-count .counterUp").length > 0) {
        // $('.course-count .counterUp, .course-inner-content h4 span, .rate-head span').counterUp({
        //     delay: 15,
        //     time: 1500,
        //     triggerOnce:true,
        //     onFinish: function() {
        //         alert('Counter finished!');
        //     }
        // })
        $(function () {
            function count($this) {
                var current = parseInt($this.html(), 10);
                current = current + 50;

                $this.html(++current);
                if (current > $this.data("count")) {
                    $this.html($this.data("count"));
                } else {
                    setTimeout(function () {
                        count($this);
                    }, 5);
                }
            }

            function isScrolledIntoView($elem) {
                var docViewTop = $(window).scrollTop();
                var docViewBottom = docViewTop + $(window).height();

                var elemTop = $elem.offset().top;
                var elemBottom = elemTop + $elem.height();

                return elemBottom <= docViewBottom && elemTop >= docViewTop;
            }

            $(window).on("scroll", function () {
                $(".counterUp").each(function () {
                    var $this = $(this);
                    if (
                        isScrolledIntoView($this) &&
                        !$this.hasClass("counted")
                    ) {
                        $this.addClass("counted");
                        $this.data("count", parseInt($this.html(), 10));
                        $this.html("0");
                        count($this);
                    }
                });
            });
            $(window).trigger("scroll");
        });
    }

    // Mobile menu sidebar overlay

    $(".header-fixed").append('<div class="sidebar-overlay"></div>');
    $(document).on("click", "#mobile_btn", function () {
        $("main-wrapper").toggleClass("slide-nav");
        $(".sidebar-overlay").toggleClass("opened");
        $("html").addClass("menu-opened");
        return false;
    });

    $(document).on("click", ".sidebar-overlay", function () {
        $("html").removeClass("menu-opened");
        $(this).removeClass("opened");
        $("main-wrapper").removeClass("slide-nav");
    });

    $(document).on("click", "#menu_close", function () {
        $("html").removeClass("menu-opened");
        $(".sidebar-overlay").removeClass("opened");
        $("main-wrapper").removeClass("slide-nav");
    });

    // Select 2

    if ($(".select").length > 0) {
        $(".select").select2({
            minimumResultsForSearch: -1,
            width: "100%",
        });
    }

    // tooltip

    $(document).ready(function () {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    //feather.replace()

    // Home popular mentor slider

    if ($(".owl-carousel.mentoring-course").length > 0) {
        var owl = $(".owl-carousel.mentoring-course");
        owl.owlCarousel({
            margin: 25,
            nav: false,
            nav: true,
            loop: false,
            responsive: {
                0: {
                    items: 1,
                },
                768: {
                    items: 3,
                },
                1170: {
                    items: 4,
                },
            },
        });
    }

    // Treand Course

    if ($(".owl-carousel.trending-course").length > 0) {
        var owl = $(".owl-carousel.trending-course");
        owl.owlCarousel({
            margin: 24,
            nav: false,
            nav: true,
            loop: false,
            responsive: {
                0: {
                    items: 1,
                },
                768: {
                    items: 2,
                },
                1170: {
                    items: 3,
                },
            },
        });
    }

    // Leading Companies

    if ($(".owl-carousel.lead-group-slider").length > 0) {
        var owl = $(".owl-carousel.lead-group-slider");
        owl.owlCarousel({
            margin: 24,
            nav: false,
            nav: true,
            loop: true,
            autoplay: true,
            autoplaySpeed: 2000,
            responsive: {
                0: {
                    items: 1,
                },
                768: {
                    items: 4,
                },
                1170: {
                    items: 6,
                },
            },
        });
    }

    // Feature Instructors

    if ($(".owl-carousel.instructors-course").length > 0) {
        var owl = $(".owl-carousel.instructors-course");
        owl.owlCarousel({
            margin: 24,
            nav: false,
            nav: true,
            loop: false,
            responsive: {
                0: {
                    items: 1,
                },
                768: {
                    items: 2,
                },
                1170: {
                    items: 4,
                },
            },
        });
    }

    // Latest Blogs

    if ($(".owl-carousel.blogs-slide").length > 0) {
        var owl = $(".owl-carousel.blogs-slide");
        owl.owlCarousel({
            margin: 24,
            nav: false,
            nav: true,
            loop: false,
            responsive: {
                0: {
                    items: 1,
                },
                768: {
                    items: 2,
                },
                1170: {
                    items: 4,
                },
            },
        });
    }

    // Login Slide

    if ($(".owl-carousel.login-slide").length > 0) {
        var owl = $(".owl-carousel.login-slide");
        owl.owlCarousel({
            margin: 24,
            nav: false,
            nav: true,
            loop: false,
            responsive: {
                0: {
                    items: 1,
                },
                768: {
                    items: 1,
                },
                1170: {
                    items: 1,
                },
            },
        });
    }

    // Slick testimonial three

    if ($(".mentor-testimonial.lazy").length > 0) {
        $(".mentor-testimonial.lazy").slick({
            lazyLoad: "ondemand",
            infinite: true,
        });
    }

    // Home header

    $(window).scroll(function () {
        var sticky = $(".header-page"),
            scroll = $(window).scrollTop();

        if (scroll >= 200) sticky.addClass("add-header-bg");
        else sticky.removeClass("add-header-bg");
    });

    // Timer countdown

    if ($(".countdown-container").length > 0) {
        const daysEl = document.getElementById("days");
        const hoursEl = document.getElementById("hours");
        const minsEl = document.getElementById("mins");

        const newYears = "1 Jan 2023";

        function countdown() {
            const newYearsDate = new Date(newYears);
            const currentDate = new Date();

            const totalSeconds = (newYearsDate - currentDate) / 1000;

            const days = Math.floor(totalSeconds / 3600 / 24);
            const hours = Math.floor(totalSeconds / 3600) % 24;
            const mins = Math.floor(totalSeconds / 60) % 60;

            daysEl.innerHTML = days;
            hoursEl.innerHTML = formatTime(hours);
            minsEl.innerHTML = formatTime(mins);
        }

        function formatTime(time) {
            return time < 10 ? `0${time}` : time;
        }

        // initial call
        countdown();

        setInterval(countdown, 1000);
    }

    // Circle Progress Bar

    function animateElements() {
        $(".circle-bar1").each(function () {
            var elementPos = $(this).offset().top;
            var topOfWindow = $(window).scrollTop();
            var percent = $(this).find(".circle-graph1").attr("data-percent");
            var animate = $(this).data("animate");
            if (
                elementPos < topOfWindow + $(window).height() - 30 &&
                !animate
            ) {
                $(this).data("animate", true);
                $(this)
                    .find(".circle-graph1")
                    .circleProgress({
                        value: percent / 100,
                        size: 400,
                        thickness: 40,
                        startAngle: -1.6,
                        fill: {
                            color: "#159f46",
                        },
                    });
            }
        });
    }

    if ($(".circle-bar").length > 0) {
        animateElements();
    }
    $(window).scroll(animateElements);

    //Otp verfication

    $(".digit-group")
        .find("input")
        .each(function () {
            $(this).attr("maxlength", 1);
            $(this).on("keyup", function (e) {
                var parent = $($(this).parent());

                if (e.keyCode === 8 || e.keyCode === 37) {
                    var prev = parent.find("input#" + $(this).data("previous"));

                    if (prev.length) {
                        $(prev).select();
                    }
                } else if (
                    (e.keyCode >= 48 && e.keyCode <= 57) ||
                    (e.keyCode >= 65 && e.keyCode <= 90) ||
                    (e.keyCode >= 96 && e.keyCode <= 105) ||
                    e.keyCode === 39
                ) {
                    var next = parent.find("input#" + $(this).data("next"));

                    if (next.length) {
                        $(next).select();
                    } else {
                        if (parent.data("autosubmit")) {
                            parent.submit();
                        }
                    }
                }
            });
        });

    $(".digit-group input").on("keyup", function () {
        var self = $(this);
        if (self.val() != "") {
            self.addClass("active");
        } else {
            self.removeClass("active");
        }
    });

    // Fade in scroll

    if ($(".main-wrapper .aos").length > 0) {
        AOS.init({
            duration: 1200,
            once: true,
        });
    }

    // Content div min height set

    function resizeInnerDiv() {
        var height = $(window).height();
        var header_height = $(".header").height();
        var footer_height = $(".footer").height();
        var setheight = height - header_height;
        var trueheight = setheight - footer_height;
        $(".content").css("min-height", trueheight);
    }

    if ($(".content").length > 0) {
        resizeInnerDiv();
    }

    $(window).resize(function () {
        if ($(".content").length > 0) {
            resizeInnerDiv();
        }
    });

    // Wizard

    $(document).ready(function () {
        let progressVal = 0;
        let businessType = 0;

        $(".next_btn").click(function () {
            $(this).parent().parent().parent().next().fadeIn("slow");
            $(this).parent().parent().parent().css({
                display: "none",
            });
            progressVal = progressVal + 1;
            $(".progress-active")
                .removeClass("progress-active")
                .addClass("progress-activated")
                .next()
                .addClass("progress-active");
        });

        $(".prev_btn").click(function () {
            $(this).parent().parent().parent().prev().fadeIn("slow");
            $(this).parent().parent().parent().css({
                display: "none",
            });
            progressVal = progressVal - 1;
            $(".progress-active")
                .removeClass("progress-active")
                .prev()
                .removeClass("progress-activated")
                .addClass("progress-active");
        });
    });

    // CK Editor

    if ($("#editor").length > 0) {
        ClassicEditor.create(document.querySelector("#editor"), {
            toolbar: {
                items: [
                    "heading",
                    "|",
                    "fontfamily",
                    "fontsize",
                    "|",
                    "alignment",
                    "|",
                    "fontColor",
                    "fontBackgroundColor",
                    "|",
                    "bold",
                    "italic",
                    "strikethrough",
                    "underline",
                    "subscript",
                    "superscript",
                    "|",
                    "link",
                    "|",
                    "outdent",
                    "indent",
                    "|",
                    "bulletedList",
                    "numberedList",
                    "todoList",
                    "|",
                    "code",
                    "codeBlock",
                    "|",
                    "insertTable",
                    "|",
                    "uploadImage",
                    "blockQuote",
                    "|",
                    "undo",
                    "redo",
                ],
                shouldNotGroupWhenFull: true,
            },
        })
            .then((editor) => {
                window.editor = editor;
            })
            .catch((err) => {
                console.error(err.stack);
            });
    }

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
    $(document).ready(function () {
        $.validator.addMethod(
            "alphadigits",
            function (value, element) {
                return (
                    this.optional(element) || /^[\sa-zA-Z0-9_]+$/i.test(value)
                );
            },
            "Please enter only letters, digits and underscore."
        );
        $("#newsletterForm").validate({
            rules: {
                email: {
                    required: true,
                    email: true,
                },
                name: {
                    required: true,
                    alphadigits: true,
                },
                agree: {
                    required: true,
                },
            },
            messages: {
                email: {
                    required: "Email is Required.",
                    email: "Please enter a valid email address.",
                },
                name: {
                    required: "Name is Required.",
                },
                agree: {
                    required: "You Must Aceept our terms and condition.",
                },
            },
            errorPlacement: function (error, element) {
                if (element.attr("name") == "agree") {
                    error.insertAfter(element.closest("div"));
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function (form) {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: $(form).serialize(),
                    url: form.action,
                    beforeSend: function () {
                        $("#loader_section").show();
                    },
                    success: function (response) {
                        $("#loader_section").hide();
                        $("#newsletter_msg")
                            .html(response.message)
                            .css("color", "green");
                        jQuery("#newsletterForm")[0].reset();
                    },
                });
            },
        });

        $("#searchform").validate({
            rules: {
                // s: {
                //     required: true
                // }
            },
            messages: {},
        });

        // $('#searchform').keypress(function( e ) {
        //     // console.log("5");
        //     if(e.which === 32)
        //       return false;
        //  });
    });
    $("#imageUpload").change(function (e) {
        var URL = $("#imageUpload").attr("data-url");
        var CSRF_TOKEN = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");
        var files = $("#imageUpload")[0].files;
        if (files.length > 0) {
            var fd = new FormData();
            // Append data
            fd.append("file", files[0]);
            const fsize = files[0].size;
            if (fsize >= 5000000) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "File too Big, please select a file less than 5mb",
                });
                //	toastr.error("File too Big, please select a file less than 200kb");
                return false;
            }

            fd.append("_token", CSRF_TOKEN);
            // AJAX request
            $.ajax({
                url: URL,
                method: "POST",
                data: fd,
                contentType: false,
                processData: false,
                dataType: "json",
                beforeSend: function () {
                    $("#loader_section").show();
                },
                success: function (response) {
                    $("#loader_section").hide();
                    // Hide error container
                    $("#err_file").removeClass("d-block");
                    $("#err_file").addClass("d-none");

                    if (response.success == 1) {
                        // Uploaded successfully
                        // File preview
                        $(".imagePreview").show();
                        if (
                            response.extension == "jpg" ||
                            response.extension == "jpeg" ||
                            response.extension == "png"
                        ) {
                            $(".imagePreview").attr("src", response.filepath);
                        }
                    }
                },
                error: function (response) {
                    $("#loader_section").hide();
                    console.log("error : " + JSON.stringify(response));
                },
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Please select a file.",
            });
            //alert("Please select a file.");
        }
    });

    // HomeMain Banner

    if ($(".owl-carousel.main-banner-section").length > 0) {
        var owl = $(".owl-carousel.main-banner-section ");
        owl.owlCarousel({
            margin: 0,
            nav: false,
            nav: true,
            loop: true,
            responsive: {
                0: {
                    items: 1,
                },
                768: {
                    items: 1,
                },
                1170: {
                    items: 1,
                },
            },
        });
    }

    $(document).ready(function () {
        $("#searchbox").on("keyup", function () {
            let empty = false;

            $("#searchbox").each(function () {
                empty = $(this).val().length < 3;
            });

            if (empty) $("#searchsubmit").attr("disabled", "disabled");
            else $("#searchsubmit").attr("disabled", false);
        });
    });

    // $(document).on('click', '.checkout-btn', function() {
    //     var elementData = $(this);
    //     var plan_id = elementData.get(0).hasAttribute('data-plan-id') ? elementData.attr('data-plan-id') : '';
    //     var plan_number = elementData.get(0).hasAttribute('data-plan-number') ? elementData.attr('data-plan-number') : '';
    //     var data_url = elementData.get(0).hasAttribute('data-url') ? elementData.attr('data-url') : '';
    //     var course_id = elementData.get(0).hasAttribute('data-course-id') ? elementData.attr('data-course-id') : '';
    //     if (plan_number == 3) { // if already Purchased
    //         window.location.href = $(this).attr(href);
    //         return false;
    //     }

    //     $.ajax({
    //         url: data_url,
    //         method: 'POST',
    //         data: {
    //             plan_id: plan_id,
    //             plan_number: plan_number,
    //             course_id: course_id
    //         },
    //         dataType: 'json',
    //         success: function(response) {
    //             if (response.code == 1) {
    //                 window.location.href = response.redirect;
    //             } else if (response.code == 2) {
    //                 Swal.fire({
    //                     icon: "success",
    //                     title: "Success",
    //                     text: "Course added successfully",
    //                 }).then(function(result) {
    //                     if (result.isConfirmed) {
    //                         window.location.href = response.redirect;
    //                     }
    //                 });
    //             }
    //         },
    //         error: function(response) {}
    //     });
    // })
})(jQuery);
