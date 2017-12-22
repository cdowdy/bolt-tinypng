<template>
    <form action="">
        <table is="bolt-table"
               class="dashboardlisting table-hover">
            <thead slot="thead">
            <tr>
                <th>
                    <div class="form-group ">
                        <label for="fileSearch"
                               class="control-label">
                            Files To Optimize
                        </label>
                        <input type="search"
                               id="fileSearch"
                               class="form-control"
                               aria-describedby="fileSearchDescription"
                               placeholder="Search Files"
                               v-model="filteredFiles">
                        <span class="sr-only"
                              id="fileSearchDescription">Search for Files to optimize</span>
                    </div>
                </th>
                <th>&nbsp;</th>
                <th>Size</th>
                <th>Optimize</th>
                <th>
                    <button type="button"
                            class="btn btn-sm btn-primary"
                            @click="toggleBatch">
                        Batch Optimize
                    </button>
                </th>
            </tr>
            <tr v-show="batchVisible">
                <th id="preserveHeading">
                    Preserve Options
                </th>
                <th colspan="2">
                    <ul class="list-inline list-unstyled"
                        aria-labelledby="preserveHeading"
                        role="radiogroup">
                        <li>
                            <label class="radio-inline tinypng-batch-options">
                                <input type="radio"
                                       id="preserveNone"
                                       name="preserveOptions"
                                       value="none"
                                       v-model="batchPreserve"
                                       checked>
                                Default (none)
                            </label>
                        </li>
                        <li>
                            <label class="radio-inline tinypng-batch-options">
                                <input type="radio"
                                       id="preserveCopyright"
                                       name="preserveOptions"
                                       value="copyright"
                                       v-model="batchPreserve">
                                Copyright
                            </label>
                        </li>
                        <li>
                            <label class="radio-inline tinypng-batch-options">
                                <input type="radio"
                                       id="preserveCreation"
                                       name="preserveOptions"
                                       value="creation"
                                       v-model="batchPreserve">
                                Creation
                            </label>
                        </li>
                        <li>
                            <label class="radio-inline tinypng-batch-options">
                                <input type="radio"
                                       id="preserveLocation"
                                       name="preserveOptions"
                                       value="location"
                                       v-model="batchPreserve">
                                Location
                            </label>
                        </li>
                    </ul>
                </th>
                <th colspan="">
                    <label class="checkbox-inline tinypng-batch-options"
                           for="selectAllBatch">
                        <input id="selectAllBatch"
                               type="checkbox"
                               value="all"
                               v-model="selectAllBatch">
                        Select All
                    </label>

                </th>
                <th>
                    <button v-if="!batchWorking"
                            type="button"
                            class="btn btn-info btn-sm"
                            :disabled="batchDisabled"
                            @click="batchOptimize">
                        Do Optimization
                    </button>
                    <svg v-if="batchWorking"
                         class="bolt-spinner"
                         viewBox="0 0 100 100">
                        <use xlink:href="#bolt-spinner"/>
                    </svg>
                </th>
            </tr>
            </thead>
            <tbody slot="table-body">
            <template v-if="fileList && Object.keys(fileList).length >= 1">
                <tr is="tinypng-file"
                    v-for="(file, index) in filterFiles "
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
                            <tinypng-dropdown :disabled=" isDisabled "
                                              :file="file"
                                              :file-index="index"
                                              @tinypng-optimize="optimizeImage">
                            </tinypng-dropdown>

                            <tinypng-rename :disabled="isDisabled"
                                            :index="index"
                                            :file="file"
                                            :renamePath="tinypngRenamePath"
                                            @tinypng-image-renamed="renameOptimizeImage">
                            </tinypng-rename>

                            <bolt-button :disabled=" isDisabled "
                                         type="button"
                                         variant="danger"
                                         @click="deleteImage(file.imagePath, index )">
                                <i class="fa fa-trash"
                                   aria-hidden="true"
                                   slot="buttonIcon">
                                </i>
                                Delete Image
                            </bolt-button>
                        </td>
                        <td>
                            <label :for="file.filename"
                                   v-show="batchVisible">
                                <input :name="file.filename"
                                       type="checkbox"
                                       :id="index"
                                       :value="file.imagePath"
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
    import TinypngDropdown from './buttons/TinypngDropdownButton';
    import TinypngRename from './buttons/TinypngOptimizeRenameButton';
    import axios from 'axios';
    import {Modal} from 'uiv';

    export default {

        name: 'TinypngFilelist',

        components: {
            BoltTable, TinypngFile, BoltButton,
            BoltButtongroup, Modal, TinypngDropdown,
            TinypngRename,
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

            tinypngBatchOptimize: {
                type: String,
                required: true
            },

            tinypngDeletePath: {
                type: String,
                required: true
            },

            tinypngRenamePath: {
                type: String,
                required: true
            },
        },

        data() {
            return {
                filteredFiles: '',
                fileList: this.files,

                batchVisible: false,
                batchAllSelected: [],
                batchPreserve: 'none',
                batchWorking: false,


                btnDisabled: false,
            }
        },


        methods: {
            toggleBatch: function () {
                this.batchVisible = !this.batchVisible;
            },

            renameOptimizeImage: function (response) {

                response.data.forEach(object => {

                    this.fileList.push({
                        filename: object.filename,
                        filesize: object.optimizedSize,
                        imageHeight: object.imageHeight,
                        imageWidth: object.imageWidth,
                        imagePath: object.imagePath,
                        located: object.located,
                    });

                    this.$emit('tinypng-image-optimized', response.data);
                });
            },

            deleteImage: function (imageToDelete, index) {

                this.btnDisabled = !this.btnDisabled;

                axios.post(this.tinypngDeletePath, {

                    image: imageToDelete,

                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    withCredentials: true,
                })
                    .then(response => {
                        if (this.filteredFiles) {
                            let i = this.fileList.map(item => item.imagePath).indexOf(imageToDelete);
                            this.fileList.splice(i, 1);
                        } else {
                            this.fileList.splice(index, 1);
                        }

                        this.btnDisabled = false;

                    })
                    .catch(function (error) {
                        console.log(imageToDelete);
                        console.log(error);
                    });
            },

            optimizeImage: function (index, file, toPreserve) {

                this.btnDisabled = !this.btnDisabled;

                axios.post(this.tinypngPath, {

                    image: file,
                    preserve: toPreserve,

                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    withCredentials: true,
                })
                    .then(response => {

                        this.btnDisabled = false;

                        response.data.forEach(object => {

                            if (this.filteredFiles) {
                                let index = this.fileList.map(item => item.imagePath).indexOf(file);
                            }
                            this.fileList.splice(index, 1, {
                                filename: object.filename,
                                filesize: object.optimizedSize,
                                imageHeight: object.imageHeight,
                                imageWidth: object.imageWidth,
                                imagePath: object.imagePath,
                                located: object.located,
                            });

                        });
                        this.$emit('tinypng-image-optimized', response.data);


                    })
                    .catch(function (error) {
                        console.log(file);
                        console.log(error);
                    });
            },

            batchOptimize: function () {
                this.batchWorking = true;

                axios.post(this.tinypngBatchOptimize, {

                    images: this.batchAllSelected,
                    preserve: this.batchPreserve,

                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    withCredentials: true,
                })
                    .then(response => {

                        this.batchVisible = false;
                        this.batchWorking = false;
                        this.batchAllSelected = [];
                        this.fileList = response.data.fileList;
                        this.$emit('tinypng-batch-optimized', response.data.compressionCount);

                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },
        },

        computed: {


            filterFiles() {
                let filter = new RegExp(this.filteredFiles, 'i');

                return this.fileList.filter(el => el.filename.match(filter));
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
            },


            batchDisabled() {
                return Object.keys(this.batchAllSelected).length < 1;

            }
        },
    }
</script>