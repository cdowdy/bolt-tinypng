$(document).ready(function () {

    /**
     * our buttons we click on to listen for an event
     * @type {*}
     */
    var tinyPNGOptimize = $(".tinyPNG-buttons");

    /**
     *
     */
    tinyPNGOptimize.on({

        click: function (event) {
            var target = event.target;
            var img = target.getAttribute('data-imagePath');
            var ajaxPath = target.getAttribute('data-tinypngpath');
            var preserveData = target.getAttribute('data-optiparam');
            var fileSizeUpdate = $("#" + this.id + " .imgFileSize");


            if (target.classList.contains('tinyPNG-optimize')) {
                event.preventDefault();
                optimize(img, preserveData, ajaxPath, fileSizeUpdate );

            }

            if (target.classList.contains('tnypng-copy')) {
                event.preventDefault();
                optimize(img, preserveData, ajaxPath, fileSizeUpdate );
            }

            if (target.classList.contains('tnypng-create')) {
                event.preventDefault();
                optimize(img, preserveData, ajaxPath, fileSizeUpdate );
            }

            if (target.classList.contains('tnypng-location')) {
                event.preventDefault();
                optimize(img, preserveData, ajaxPath, fileSizeUpdate );
            }


            if (target.classList.contains('tnypng-allthree')) {
                event.preventDefault();
                optimize(img, preserveData, ajaxPath, fileSizeUpdate );

            }

            if (target.classList.contains('tinypng-delete')) {
                event.preventDefault();
                var containerID = $('#' + this.id);
                // containerID.addClass("removed-item");
                deleteImage(img, containerID, ajaxPath);

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
                    // form.find(".form-input-container").attr("hidden", true);
                    // form.find(".tnypng-spinner-container").attr("hidden", false);
                    // $(".renameModal").find('.imageToOptimize').text(img + ' is being optimized...');
                    optimizeRename(img, theName, preserveRadio, ajaxPath, fileSizeUpdate );
                    removeRenameSpiner(form, ".form-input-container", ".tnypng-spinner-container", img);
                }
            }
        },

        change: function (event) {
            var target = event.target;
            var form = $(target).closest('.form-horizontal');

            if ($(target).hasClass('new-name-input') && $(".new-name-input").length > 0) {
                form.find(".control-label").parent().removeClass('has-error');
                form.find("input[type=text]").siblings(".input-error").attr("hidden", true);
            }
        }
    });


    /**
     * optimize the image with tinypng api
     * @param img - the image to optimize
     * @param preserve - the data to preserve in the dropdown list - location, creation, copyright
     * @param path - the ajax route
     * @param fileSizeUpdate
     */
    var optimize = function (img, preserve, path, fileSizeUpdate ) {
        var workingModal = $("#working-modal");
        $.ajax({
            type: "POST",
            url: path,
            data: {
                'image': img,
                'preserve': preserve
            },
            dataType: "json",

            beforeSend: function () {
                workingModal.modal("toggle");
                workingModal.find('.imageToOptimize').text(img + ' is being optimized...');
                disableButtons();

            },

            success: function (data) {
                workingModal.modal("hide");
                enableButtons();
                postOptimizeUpdate(data, fileSizeUpdate);

            },

            error: function (xhr, desc, err) {
                enableButtons();
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
                $(".ajax-error").append(xhr.responseText);
            }
        });
    };

    /**
     * rename the image and save
     * @param img :: the actual image saved to the files directory
     * @param newName  :: the new name we would like to save it as
     * @param preserve :: the data we want to save - location, creation or copyright
     * @param path
     * @param fileSizeUpdate
     * @param path
     * @param fileSizeUpdate
     */
    var optimizeRename = function (img, newName, preserve, path, fileSizeUpdate) {
        $.ajax({
            type: "POST",
            url: path,
            data: {
                'image': img,
                'newName': newName,
                'preserve': preserve
            },
            dataType: 'json',

            beforeSend: function () {

                disableButtons();
            },

            success: function (data) {
                enableButtons();
                postOptimizeUpdate(data, fileSizeUpdate);
            },

            error: function (xhr, desc, err) {
                enableButtons();
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
                $(".ajax-error").append(xhr.responseText);
            }
        });

    };

    /**
     *
     * @param img
     * @param containerID
     * @param path
     */
    var deleteImage = function (img, containerID, path) {
        $.ajax({
            type: "POST",
            // url: $(".dashboardlisting").data("bolt-path") + '/extend/tinypng/optimize/delete',
            url: path,
            data: {
                'image': img,
            },
            dataType: 'json',

            beforeSend: function () {
                containerID.addClass("removed-item");
                disableButtons();
            },

            success: function (data) {
                enableButtons();
                containerID.remove();
            },

            error: function (xhr, desc, err) {
                enableButtons();
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
                $(".ajax-error").append(xhr.responseText);
            }
        });

    };

    // remove the spinner in the rename and optimize modal window after a successful ajax request
    /**
     *
     * @param formElement
     * @param formInputContainer
     * @param spinnerContainer
     * @param imageToOptimize
     */
    var removeRenameSpiner = function (formElement, formInputContainer, spinnerContainer, imageToOptimize) {
        var renameModal = $(".renameModal");

        formElement.find(formInputContainer).attr("hidden", true);
        formElement.find(spinnerContainer).attr("hidden", false);
        renameModal.find('.imageToOptimize').text(imageToOptimize + ' is being optimized...');
        $(document).ajaxSuccess(function () {
            formElement.find(formInputContainer).attr("hidden", false);
            formElement.find(spinnerContainer).attr("hidden", true);
            renameModal.modal("hide");
        });

    };


    /**
     * show the spinner before the ajax request is made
     * @param targetImage
     */
    var showSpinner = function (targetImage) {

        var workingModal = $("#working-modal");
        workingModal.modal("toggle");
        workingModal.find('.imageToOptimize').text(targetImage + ' is being optimized...');
    };


    /**
     * remove the modal after the ajax request has completed
     */
    var removeSpinner = function () {
        $("#working-modal").modal("toggle");
    };


    /**
     * disable the buttons so another request can't be made incase of an error
     */
    var disableButtons = function () {
        var buttons = document.querySelectorAll('.btn');

        for (var i = 0; i < buttons.length; ++i) {
            buttons[i].setAttribute("disabled", "disabled");
        }
    };


    /**
     * enable the buttons after a successful ajax request
     */
    var enableButtons = function () {
        var buttons = document.querySelectorAll('.btn');

        for (var i = 0; i < buttons.length; ++i) {
            buttons[i].removeAttribute("disabled");
        }
    };

    /**
     *
     * @param responseObject
     * @param fileSizeUpdate
     */
    var postOptimizeUpdate = function (responseObject, fileSizeUpdate) {

        $.each(responseObject, function (i, obj) {
            $("#compressionCount").text(obj.compressionCount);
            fileSizeUpdate.text(obj.optimizedSize);
        });
    };


    /**
     *
     * @type {{paramName: string, acceptedFiles: string, thumbnailWidth: number, thumbnailHeight: null, previewTemplate: string, addRemoveLinks: boolean, accept: Dropzone.options.tinypngUploadForm.accept, init: Dropzone.options.tinypngUploadForm.init}}
     */
    Dropzone.options.tinypngUploadForm = {
        paramName: "tnypng_file", // The name that will be used to transfer the file
        // maxFilesize: 2, // MB
        acceptedFiles: 'image/png,image/jpeg',
        thumbnailWidth: 80,
        thumbnailHeight: null,
        previewTemplate: document.getElementById('dropzone-preview').innerHTML,
        addRemoveLinks: true,
        accept: function (file, done) {
            done();
        },


        init: function () {
            $("#tinypng_upload_form").addClass("form-inline");

            this.on("complete", function (progress) {
                $(".progress").hide();
                document.querySelector(".progress-bar").style.opacity = "0";
            });

            this.on("success", function (file, response, xhr) {

                $.each(response, function (i, obj) {

                    $("#compressionCount").text(obj.compressionCount);

                    var template = $('#tinypng-upload-insert').html();
                    var $template = $(template);

                    $template.find('.magnific').attr('href', '/thumbs/1000x1000r/' + obj.name );
                    $template.find('.magnific').attr('title', 'Image: ' + obj.name );
                    $template.find('.tinypng-uploadeName').text( obj.name );
                    $template.find('.tinypng-listthumb').attr('src', '/thumbs/54x40c/' + obj.name );
                    $template.find('.imgFileSize').text(obj.optimizedSize );
                    // {{ key.imageWidth }}<span class="times">×</span>{{ key.imageHeight }} px
                    $("#tinypng-uploaded-files").prepend($template);
                });


            });

            this.on("error", function (file, errorMessage, xhr) {
                var errorContainer = $(file.previewElement).find('.dz-error-message');
                if (xhr && xhr.status === 413) {
                    // if (xhr.status === 413) {
                    errorMessage = "Server Error: " + xhr.status + ' ' + xhr.statusText;
                    // }
                }

                errorContainer.text(errorMessage).addClass("alert alert-danger");
            });
        }
    };

});






