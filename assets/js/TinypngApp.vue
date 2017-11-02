<template>
    <div>
        <div class="col-xs-12 col-md-5">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Compressions Made</h3>
                </div>
                <div class="panel-body">
                    <p>
                        You Have Made <b>{{ compressions }}</b> Compressions This Month
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-md-5">

                <alert type="success" v-for="alert in uploadedAlerts" :key="alert.key"
                       v-show="showNotification"
                       :dismissible="true"
                       @dismissed="showNotification = false"
                       :duration="duration">
                    {{ uploaded }} has been uploaded!
                </alert>


        </div>

        <div class="col-xs-12">
            <hr>
            <table class="dashboardlisting">
                <thead>
                <tr>
                    <th>Upload, Resize &amp; Optimize Images</th>
                    <th>Upload Settings</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td id="tnypng-upload-form-con      ">
                        <Tinypng-Dropzone  @tnydropzone-success="pushUploadedFile" :acceptedFiles="''"
                                id="tinypng_upload_form"
                                :url="dropzoneUrl"
                                >
                            <fieldset id="form_tinypng">
                                <div class="fallback">
                                    <div class="form-group">
                                        <label class="control-label required"
                                               for="form_tinypng_file">Upload an Image</label>
                                        <input type="file"
                                               id="form_tinypng_file"
                                               name="tnypng_file[]"
                                               required="required"
                                               class="tinypng-inputfile"
                                               accept="image/jpeg,image/png"
                                               multiple="multiple"/>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit"
                                                class="btn btn-primary tinypng-upload">Upload File</button>
                                    </div>
                                </div>
                            </fieldset>
                        </Tinypng-Dropzone>

                    </td>
                    <td>
                        <p><b>Upload Method:</b> {{ uploadMethod }}</p>
                        <p v-if="maxWidth">
                            <b>Width:</b> {{ maxWidth }}px
                        </p>
                        <p v-if="maxHeight">
                            <b>Height:</b> {{ maxHeight }}px
                        </p>

                        <!--{#<p><b>Metadata:</b> {{ saveData }}</p>#}-->
                    </td>
                </tr>
                </tbody>

            </table>

        </div><!-- /.col-xs-12 upload conatainer -->
        <div class="col-xs-12">

            <tabs >
                <tab title="Files" selected="true">
                    <tinypng-filelist
                            :files="fileList"
                            :tinypng-path="tinypngPath"
                            :tinypngDeletePath="tinypngDeletePath"
                             >
                    </tinypng-filelist>
                </tab>

                <tab title="Directories" >

                    <table class="dashboardlisting">
                        <thead>
                        <tr>
                            <th>Directories</th>
                            <th>Sub Directories</th>
                        </tr>
                        </thead>
                        <tbody is="tinypng-directory"
                               :directories="tinypngDirectories"
                               :pathAllImages="allImagesPath">
                        </tbody>
                    </table>

                    <tinypng-newdir :directory=" directory"
                                    :create-action-path="createPath "
                                    :delete-action-path="deletePath"
                                    :directories="tinypngDirectories"></tinypng-newdir>
                </tab>
            </tabs>
        </div>
    </div>
</template>

<script>
    import TinypngDropzone from './components/TinypngDropzone.vue';
    // import BoltTabs from './components/Bolt/BoltTabs.vue';
    // import BoltTabpanel from './components/Bolt/BoltTabsPanels';
    import TinypngFilelist from './components/TinypngFilelisting';
    import TinypngDirectory from './components/TinypngDirectoryList.vue';
    import TinypngSubdirectory from './components/TinypngSubdirectoryList';
    import TinypngNewdir from './components/TinypngCreateDirectory';

    import { Alert, Tab, Tabs } from 'uiv';

    export default {

        name: 'TinypngApp',
        components: {
            TinypngDropzone, TinypngFilelist,
            TinypngDirectory, TinypngSubdirectory, TinypngNewdir,
            Alert, Tabs, Tab
        },

        props: {
            compressionCount: {
                type: String
            },

            files: {
                required: true
            },
            maxWidth: {
                type: String,
                required: true
            },
            maxHeight: {
                type: String,
                required: true
            },

            dropzoneUrl: {
                type: String,
                required: true
            },
            uploadMethod: {
              type: String,
              required: true
            },

            tinypngPath: {
                type: String,
                required: true
            },
            tinypngDeletePath: {
                type: String,
                required: true
            },

            allImagesPath: {
                required: true
            },

            tinypngDirectories: {
                required: true,
            },

            createPath: {
                required: true,
            },

            deletePath:{
                required: true,
            },

            directory: {
                required: true
            },


            uploadedImage: {
                type: String
            }


        },

        data() {
            return {
                // when the compression count gets updated reflect that in our component
                compressions: this.compressionCount,
                uploadedAlerts: [],
                uploaded: this.uploadedImage,
                showNotification: false,
                duration: 5000,

                fileList: this.files,

                // the batch optimize data
                batchVisible: false,
                batchAllSelected: [],
            }
        },

        methods: {

            toggleBatch: function () {
                this.batchVisible = !this.batchVisible;
            },
            // push new file to our file list
            // listen for this event from tinypngdropzone.vue 'tnydropzone-success'
            // vm.$emit('tnydropzone-success', file, response, xhr )
            pushUploadedFile: function (file, response, xhr) {

                response.forEach(object => {

                    this.fileList.push({
                        filename: object.filename,
                        imageWidth: object.imageWidth,
                        imageHeight: object.imageHeight,
                        filesize: object.optimizedSize,
                        located: this.directory,
                        imagePath: object.name,
                    });
                    //
                    this.compressions = object.compressionCount;
                    this.uploadedAlerts.push({ key: object.filename } );
                    this.uploaded = object.filename;
                    this.showNotification = true;
                });
            },

            updateTinypngData: function (response) {

                console.log( response );

                response.forEach(object => {

                    // response.forEach(object => {
                    //     this.fileList.splice(index, 1, {
                    //         filename: object.optimizedImage,
                    //         filesize: object.optimizedSize,
                    //         imageHeight: object.imageHeight,
                    //         imageWidth: object.imageWidth,
                    //         located: this.directory,
                    //     });
                    // });

                    this.compressions = object.compressionCount;

                });

            },

            // deleteFile: function( index ) {
            //     this.$delete(this.fileList, index);
            // }
        },

    }
</script>