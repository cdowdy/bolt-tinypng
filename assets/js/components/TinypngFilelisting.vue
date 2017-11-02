<template>
    <form action="">
    <table is="bolt-table" class="dashboardlisting">
        <thead slot="thead">
        <tr>
        <th>
            <input type="search" placeholder="Search Files" v-model="filteredFiles">
            Files To Optimize
        </th>
        <th>&nbsp;</th>
        <th>Size</th>
        <th>Optimize</th>
            <th>
                <button type="button" class="btn btn-sm btn-primary"
                        @click="toggleBatch" >
                    Batch Optimize
                </button>

            </th>
        </tr>
        <tr v-show="batchVisible" >
            <th colspan="4" >
                Preserve Options

                <label class="radio-inline tinypng-batch-options">
                    <input type="radio" id="preserveNone" name="preserveOptions" value="none" checked>
                    Default (none)
                </label>
                <label class="radio-inline tinypng-batch-options">
                    <input type="radio" id="preserveCopyright" name="preserveOptions" value="copyright">
                    Copyright
                </label>
                <label class="radio-inline tinypng-batch-options">
                    <input type="radio" id="preserveCreation" name="preserveOptions" value="creation">
                    Creation
                </label>
                <label class="radio-inline tinypng-batch-options">
                    <input type="radio" id="preserveLocation" name="preserveOptions" value="location">
                    Location
                </label>

                <label class="radio-inline tinypng-batch-options" for="selectAllBatch">
                    <input id="selectAllBatch" type="checkbox" value="all" v-model="selectAllBatch"> Select All
                </label>
            </th>
            <th>
                <button type="button"
                        class="btn btn-primary btn-sm">
                    Do Optimization
                </button>
            </th>
        </tr>
        </thead>
        <tbody slot="table-body">
        <template v-if="fileList && Object.keys(fileList).length >= 1">
            <tr is="tinypng-file"
                v-for="(file, index) in filterFiles"
                :key="file.filename"
                class="tinyPNG-buttons"
                :imagePath="file.imagePath"
                :filename="file.filename"
                :located="file.located"
                :filesize=" file.filesize"
                :image-width="file.imageWidth"
                :image-height="file.imageHeight">

                <template slot="extra-table-data">
                    <td>
                        <split-button :disabled=" isDisabled ">
                            <bolt-button slot="button" type="button"
                                         :disabled="isDisabled "
                                         @click="optimizeImage(file.imagePath, index, 'none', $event)">
                                Optimize Image
                            </bolt-button>
                            <li>
                                <a @click.prevent="optimizeImage(file.imagePath, index, 'copyright', $event)"
                                   class="tnypng-copy" href="#"
                                   :data-imagePath="file.imagePath "
                                   data-optiparam="copyright"
                                   :data-tinypngpath="tinypngPath">Preserve Copyright
                                </a>
                            </li>
                            <li>
                                <a @click.prevent="optimizeImage(file.imagePath, index, 'creation', $event)"
                                   class="tnypng-create" href="#"
                                   :data-imagePath="file.imagePath "
                                   data-optiparam="creation"
                                   :data-tinypngpath="tinypngPath">Preserve Creation</a>
                            </li>
                            <li>
                                <a @click.prevent="optimizeImage(file.imagePath, index, 'location', $event)"
                                   class="tnypng-location" href="#"
                                   :data-imagePath="file.imagePath "
                                   data-optiparam="location"
                                   :data-tinypngpath="tinypngPath">Preserve Location</a>
                            </li>
                            <li>
                                <a @click.prevent="optimizeImage(file.imagePath, index, 'all', $event)"
                                   class="tnypng-allthree"
                                   href="#"
                                   :data-imagePath="file.imagePath "
                                   data-optiparam="all"
                                   :data-tinypngpath="tinypngPath">Preserve All Three (3)</a>
                            </li>
                        </split-button>


                        <bolt-button type="button" :disabled=" isDisabled ">
                            Optimize &amp; Rename
                        </bolt-button>
                        <bolt-button :disabled=" isDisabled "
                                     type="button"
                                     variant="danger"
                                     @click="deleteImage(file.imagePath, index, $event )">
                            <i class="fa fa-trash" aria-hidden="true" slot="buttonIcon"></i>
                            Delete Image
                        </bolt-button>
                    </td>
                    <td>
                        <label :for="file.filename" v-show="batchVisible">
                            <input :name="file.filename" type="checkbox" :value="file.imagePath"
                                   v-model="batchAllSelected">
                        </label>
                    </td>
                </template>
            </tr>
        </template>
        <template v-else>
            <tr>
                <td>NO PNG/JPG Images In This Directory</td>
            </tr>
        </template>
        </tbody>
    </table>
    </form>

</template>

<script>
    import BoltTable from './Bolt/Tables/BoltTable.vue';
    import TinypngFile from './TinypngFile';
    import BoltButton from './Bolt/Button/BoltButton';
    import BoltButtongroup from './Bolt/Button/BoltButtonGroup';
    import SplitButton from './Bolt/Button/SplitButton';
    import axios from 'axios';

    export default {

        name: 'TinypngFilelist',

        components: {
            BoltTable, TinypngFile, BoltButton,
            BoltButtongroup, SplitButton
        },

        props: {
            files: {
                default: [],
                required: true
            },
            tinypngPath: {
                type: String,
                required: true
            },

            tinypngDeletePath: {
                type: String,
                required: true
            }
        },

        data() {
            return {
                filteredFiles: '',
                fileList: this.files,

                batchVisible: false,
                batchAllSelected: [],
                btnDisabled: false,
            }
        },


        methods: {
            toggleBatch: function () {
                this.batchVisible = !this.batchVisible;
            },
            
            deleteImage: function (imageToDelete, index, event) {
                let fileList = this.files;

                let vm = this;
                let button = event.target;

                button.setAttribute('disabled', true );
                axios.post(this.tinypngDeletePath, {

                    image: imageToDelete,

                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    withCredentials: true,
                })
                    .then( response => {
                        console.log( response );
                        this.fileList.splice(index, 1);
                        button.setAttribute('disabled', false );
                    })
                    .catch(function (error) {
                        console.log(imageToDelete);
                        console.log(error.response);
                    });
            },

            optimizeImage: function (imageToOptimize, index, toPreserve, event) {
                let fileList = this.files;

                let vm = this;
                let button = event.target;
                this.btnDisabled = !this.btnDisabled;
                //
                axios.post(this.tinypngPath, {


                    image: imageToOptimize,
                    preserve: toPreserve,

                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    withCredentials: true,
                })
                    .then( response => {

                        this.btnDisabled = false;

                        response.data.forEach(object => {

                            this.fileList.splice(index, 1, {
                                filename: object.optimizedImage,
                                filesize: object.optimizedSize,
                                imageHeight: object.imageHeight,
                                imageWidth: object.imageWidth,
                                imagePath: object.imagePath,
                                located: object.located,
                            });
                        });
                        this.$emit( 'tinypng-image-optimized', response.data );


                    })
                    .catch(function (error) {
                        console.log(imageToOptimize);
                        console.log(error.response);
                    });
            }

            

            // push new file to our file list
            // listen for this event from tinypngdropzone.vue 'tnydropzone-success'
            // vm.$emit('tnydropzone-success', file, response, xhr )
            // pushUploadedFile: function( file, response, xhr ) {
            //     console.log('file: ' + file );
            //     console.log( response );
            // }
        },

        computed: {


            filterFiles() {
                let filter = new RegExp( this.filteredFiles, 'i' );

                return this.fileList.filter( el => el.filename.match(filter));
            },

            selectAllBatch: {
                // get all the batchall selected values
                get: function () {
                    return this.fileList ? this.batchAllSelected.length === this.fileList.length : false;
                },
                set: function (value) {
                    var batchAllSelected = [];

                    if (value) {
                        this.fileList.forEach(function (file) {
                            batchAllSelected.push(file.imagePath);
                        });
                    }

                    this.batchAllSelected = batchAllSelected;
                }
            },

            isDisabled() {
                return this.batchVisible || this.btnDisabled;
            }
        },

        // mounted: function () {
        //     this.$nextTick(function () {
        //
        //         console.log(Object.keys(this.fileList).length );
        //     })
        // }
    }
</script>