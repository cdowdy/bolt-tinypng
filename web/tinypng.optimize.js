$(document).ready(function () {

    var tinyPNGOptimize = $(".tinyPNG-optimize");


    tinyPNGOptimize.on({

        click: function (event) {
            var target = event.target;
            var img = target.getAttribute('data-imagePath');
            // var configData = $(target).parent().prev().find('.prime-cache-dropdown').val();
            // var singleData = $(target).parent().prev().find('.prime-cache-single-input').val();
            // var presetData = target.getAttribute('data-presets');

            prime(target, img);
            // if (target.classList.contains('prime-config')) {
            //     prime(target, img, configData, 'config');
            // }
            //
            // if (target.classList.contains('prime-presets')) {
            //     prime(target, img, presetData, 'presets');
            // }
            //
            // if (target.classList.contains('prime-single')) {
            //     if( singleData === "" ) {
            //         $(target).parent().prev().find(".prime-single-error").removeAttr("hidden");
            //         return false;
            //     } else {
            //         console.log(singleData);
            //         prime(target, img, singleData, 'single');
            //     }
            //
            // }
        },

        // change: function (event) {
        //     var target = event.target;
        //     var code = event.keyCode || event.which;
        //
        //     if ($(target).hasClass('prime-cache-single-input') && $(".prime-cache-single-input").length > 0 ) {
        //         $(target).parent().next().attr("hidden", "hidden");
        //     }
        // },


        // keypress: function (event) {
        //     var target = event.target;
        //     var code = event.keyCode || event.which;
        //     var buttonToClick = $(target).parent().parent().next().find("BUTTON");
        //     var img = $(target).parent().parent().next().find("BUTTON").data("bthumb-name");
        //     var modType = $(target).val();
        //     var presetData = $(target).data('presets');
        //
        //     if ( code === 13 ) {
        //         event.preventDefault();
        //
        //         if (buttonToClick.hasClass('prime-config')) {
        //             $(target).on('click', prime(buttonToClick, img, modType, 'config' ) );
        //         }
        //         if ($(target).hasClass('prime-presets') ) {
        //             $(target).on('click', prime('.prime-presets', img, presetData, 'presets' ) );
        //         }
        //
        //         if (buttonToClick.hasClass('prime-single') ) {
        //
        //             if ($(target).val() === "") {
        //                 $(target).parent().next().removeAttr("hidden");
        //                 return false;
        //             } else {
        //                 $(target).on('click', prime(buttonToClick, img, modType, 'single') );
        //             }
        //
        //         }
        //
        //     }
        // }

    });


    var prime = function (target, img ) {
        $.ajax({
            type: "POST",
            url: $( ".dashboardlisting").data("bolt-path") + 'extend/tinypng/optimize',
            data: {
                'image' : img
            },

            beforeSend: function () {
                console.log(url);
                disableButtons();

            },

            success: function () {
                enableButtons();
            },

            error: function (xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            }
        });
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
