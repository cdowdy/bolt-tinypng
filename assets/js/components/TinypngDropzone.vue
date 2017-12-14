<template>
    <form :action="url" :id="id" method="post" class="dropzone tnypng-dropzone form-inline" enctype="multipart/form-data">
        <slot>

        </slot>
    </form>
</template>

<script>
import Dropzone from 'dropzone';
Dropzone.autoDiscover = false;
export default {

    name: 'tinypng-dropzone',
    props: {

        id: {
            type: String,
            required: true
        },

        url: {
            type: String,
            required: true
        },

        // The name that will be used to transfer the file
        paraName: {
            type: String,
            default: 'tnypng_file'
        },

        acceptedFiles: {
            type: String,
            default: 'image/png,image/jpeg'
        },

        defaultMessage: {
            type: String,
            default: 'Drag and Drop Files Here to Upload Or Click to Open your File Finder'
        },

        thumbnailHeight: {
            type: Number,
            default: null
        },
        thumbnailWidth: {
            type: Number,
            default: 80
        },

        addRemoveLinks: {
            type: Boolean,
            default: true
        },

        previewTemplate: {
            type: Function,
            default: (options) => {
                return `
                    <div class="tnypng-dropzone-preview dz-file-preview form-group">
                        <div class="dz-details">
                            <img class="tnypng-dropzone-img" data-dz-thumbnail/>
                            <div class="tnypng-dropzone-img-name">
                                <span data-dz-name></span>
                            </div>
                            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                            </div>
                        </div>

                        <div class="dz-error-message"><span data-dz-errormessage></span></div>
                    </div>
                `;
            }
        }

    },


    mounted() {
        let vm = this;

        let dzOptions = {
            paramName: this.paramName, // The name that will be used to transfer the file
            // maxFilesize: 2, // MB
            acceptedFiles: this.acceptedFiles,
            thumbnailWidth: this.thumbnailWidth,
            thumbnailHeight: this.thumbnailHeight,
            previewTemplate: this.previewTemplate(this),
            addRemoveLinks: this.addRemoveLinks,
            dictDefaultMessage: this.defaultMessage,
            accept: function (file, done) {
                done()
            }
        };

        let dropzoneElement = document.getElementById( this.id );
        this.Dropzone = new Dropzone( dropzoneElement, dzOptions );

        this.Dropzone.on("totaluploadprogress", function( progress) {
            let progressBar = document.querySelector(".progress-bar");
            if (progressBar) {
                document.querySelector(".progress-bar").style.width = progress + "%";
            }

        });

        this.Dropzone.on("success", function (file, response, xhr) {
            file.previewElement.querySelector('.progress').style.display = 'none';
            file.previewElement.querySelector(".progress-bar").style.opacity = "0";

            // emit an event so we can catch it later in our component for
            // the file listings
            vm.$emit('tnydropzone-success', file, response, xhr )
        });

        this.Dropzone.on("complete",  (file, progress) => {
            file.previewElement.querySelector('.progress').style.display = 'none';
            file.previewElement.querySelector(".progress-bar").style.opacity = "0";
//


            vm.$emit( 'tnydropzone-complete', file, progress );
        });

        this.Dropzone.on("error", function (file, errorMessage, xhr) {


            let errorContainer = file.previewElement.querySelector('.dz-error-message');

            if (xhr && xhr.status === 413) {
                errorMessage = "Server Error: " + xhr.status + ' ' + xhr.statusText;
            }

            errorContainer.innerText = errorMessage;
            errorContainer.classList.add("alert");
            errorContainer.classList.add("alert-danger");

            file.previewElement.querySelector('.progress').style.display = 'none';
            file.previewElement.querySelector(".progress-bar").style.opacity = "0";
        });
    },
    beforeDestroy () {
        this.Dropzone.disable();
        document.querySelectorAll('input.dz-hidden-input').forEach(function (elem) {
            elem.parentNode.removeChild(elem)
        })
    }
}
</script>