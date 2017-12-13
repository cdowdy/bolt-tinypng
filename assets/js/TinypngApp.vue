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

            <alert type="success" v-for="(index, alert) in uploadedAlerts" :key="alert.key"
                   v-show="showNotification"
                   :dismissible="true"
                   @dismissed="uploadedAlerts.splice(index, 1)"
                   :duration="duration">
                {{ index.key }} has been uploaded!
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
                        <Tinypng-Dropzone @tnydropzone-success="pushUploadedFile"
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
                                                class="btn btn-primary tinypng-upload">Upload File
                                        </button>
                                    </div>
                                </div>
                            </fieldset>
                        </Tinypng-Dropzone>

                    </td>
                    <td>
                        <p><b>Upload Method:</b> {{ uploadMethod }}</p>
                        <p v-if="uploadMaxWidth">
                            <b>Width:</b> {{ uploadMaxWidth }}px
                        </p>
                        <p v-if="uploadMaxHeight">
                            <b>Height:</b> {{ uploadMaxHeight }}px
                        </p>

                        <!--{#<p><b>Metadata:</b> {{ saveData }}</p>#}-->
                    </td>
                </tr>
                </tbody>

            </table>

        </div><!-- /.col-xs-12 upload conatainer -->
        <div class="col-xs-12">

            <tabs>
                <tab title="Files"
                     selected="true">
                    <tinypng-filelist
                            :files="fileList"
                            :tinypng-path="tinypngPath"
                            :tinypngDeletePath="tinypngDeletePath"
                            :tinypng-rename-path="tinypngRenamePath"
                            :tinypng-batch-optimize="tinypngBatchOptimize"
                            @tinypng-image-optimized="updateTinypngData"
                            @tinypng-batch-optimized="updateCompressionCount">
                    </tinypng-filelist>
                </tab>

                <tab title="Directories">

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

                    <tinypng-newdir :directory=" currentDirectory"
                                    :create-action-path="createDirectoryPath "
                                    :delete-action-path="deleteDirectoryPath"
                                    :directories="tinypngDirectories"></tinypng-newdir>
                </tab>
            </tabs>
        </div>
    </div>
</template>

<script>
    import TinypngDropzone from './components/TinypngDropzone.vue';
    import TinypngFilelist from './components/TinypngFilelisting';
    import TinypngDirectory from './components/TinypngDirectoryList.vue';
    import TinypngSubdirectory from './components/TinypngSubdirectoryList';
    import TinypngNewdir from './components/TinypngCreateDirectory';

    import {Alert, Tab, Tabs} from 'uiv';

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
            uploadMaxWidth: {
                type: String,
                required: true
            },
            uploadMaxHeight: {
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

            tinypngBatchOptimize: {
                type: String,
                required: true
            },
            tinypngDeletePath: {
                type: String,
                required: true
            },

            tinypngRenamePath: {
                required: true,
                type: String,
            },

            allImagesPath: {
                required: true
            },

            tinypngDirectories: {
                required: true,
            },

            createDirectoryPath: {
                required: true,
            },

            deleteDirectoryPath: {
                required: true,
            },


            currentDirectory: {
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
            pushUploadedFile: function (file, response, xhr) {

                response.forEach(object => {

                    this.fileList.push({
                        filename: object.filename,
                        filesize: object.optimizedSize,
                        imageHeight: object.imageHeight,
                        imageWidth: object.imageWidth,
                        imagePath: object.imagePath,
                        located: this.currentDirectory,
                    });

                    this.compressions = object.compressionCount;
                    this.uploadedAlerts.push({key: object.filename});
                    this.showNotification = true;
                });
            },

            updateTinypngData: function (response) {

                response.forEach(object => {
                    this.compressions = object.compressionCount;
                });
            },

            updateCompressionCount: function (response) {
                this.compressions = response;
            },
        },

    }
</script>