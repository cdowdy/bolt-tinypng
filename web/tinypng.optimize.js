$(document).ready(function () {

    // var tinyPNGOptimize = $(".tinyPNG-optimize");
    var tinyPNGOptimize = $(".tinyPNG-buttons");


    tinyPNGOptimize.on({

        click: function (event) {
            var target = event.target;
            var img = target.getAttribute('data-imagePath');
            var preserveData = target.getAttribute('data-optiparam');

            if (target.classList.contains('tinyPNG-optimize')) {
                event.preventDefault();
                // console.log("the image: " + img + " data to preserve: " + preserveData );
                optimize(img, preserveData);
            }

            if (target.classList.contains('tnypng-copy')) {
                event.preventDefault();
                // console.log("the image: " + img + " data to preserve: " + preserveData );
                optimize(img, preserveData);
            }

            if (target.classList.contains('tnypng-create')) {
                event.preventDefault();
                optimize(img, preserveData);
            }

            if (target.classList.contains('tnypng-location')) {
                event.preventDefault();
                optimize(img, preserveData);
            }

            if ( target.classList.contains('tnypng-allthree')) {
                event.preventDefault();
                optimize(img, preserveData);
            }

            // This is the modal window to optimize and rename an image
            if (target.classList.contains('tnypng-submit')) {
                var form = $(target).closest('.form-horizontal');
                var theName = form.find("input[type=text]").val();
                var preserveRadio = form.find("input[name=preserveOption]:checked").val();
                event.preventDefault();

                if (theName === '') {
                    form.find(".control-label").parent().addClass("has-error");
                    form.find(".control-label").attr("aria-invalid", true);
                    form.find("input[type=text]").siblings(".input-error").attr("hidden", false);
                } else {
                    optimizeRename(img, theName, preserveRadio);
                }
            }
        },

        change: function (event) {
            var target = event.target;
            var form = $(target).closest('.form-horizontal');

            if ($(target).hasClass('new-name-input') && $(".new-name-input").length > 0 ) {
                form.find(".control-label").parent().removeClass('has-error');
                form.find("input[type=text]").siblings(".input-error").attr("hidden", true);
            }
        }
    });


    var optimize = function ( img, preserve ) {
        $.ajax({
            type: "POST",
            url: $( ".dashboardlisting").data("bolt-path") + '/extend/tinypng/optimize',
            data: {
                'image' : img,
                'preserve' : preserve
            },

            beforeSend: function () {
                disableButtons();

            },

            success: function () {
                enableButtons();
            },

            error: function (xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
                $(".ajax-error").append(xhr.responseText);
            }
        });
    };

    var optimizeRename = function (img, newName, preserve ) {
        $.ajax({
            type: "POST",
            url: $( ".dashboardlisting").data("bolt-path") + '/extend/tinypng/optimize/rename',
            data: {
                'image' : img,
                'newName' : newName,
                'preserve' : preserve
            },

            beforeSend: function () {
                disableButtons();

            },

            success: function () {
                enableButtons();
            },

            error: function (xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
                $(".ajax-error").append(xhr.responseText);
            }
        });

    };

    var verifyNewName = function(newName) {
        if (newName === '') {
            $(newName).parent().prev("LABEL").addClass(".has-error");
            $(newName).parent().prev("LABEL").attr("aria-invalid", true);
        }
    };


    function showSpinner(target) {
        $(target).hide();
        $(target).before(spinner);
    }

    function removeSpinner(target) {
        $(target).show();
        $(target).siblings(spinner).detach();
    }

    function disableButtons() {
        // var buttons = $('.button');
        var buttons = document.querySelectorAll('.btn');

        for (var i = 0; i < buttons.length; ++i) {
            buttons[i].setAttribute("disabled", "disabled");
        }
    }

    function enableButtons() {
        var buttons = document.querySelectorAll('.btn');

        for (var i = 0; i < buttons.length; ++i) {
            buttons[i].removeAttribute("disabled");
        }
    }

});
