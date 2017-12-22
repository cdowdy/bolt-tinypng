<template>
    <div class="btn-group tinypng-button-group">
        <bolt-button type="button"
                      @click="openModal = true "
                     :id="file.filename"
                     :disabled="disabled">
            Optimize &amp; Rename
        </bolt-button>
        <modal :title="'Optimize &amp; Rename ' +  file.filename"
               v-model="openModal"
               ref="modal">
            <p>
                This will optimize your image and save it under the name you choose
                below in the text input box.
            </p>
            <form class="form-horizontal"
                  :id="index + '-rename-form'"
                  method="POST"
                  :action="renamePath"
                  @submit.prevent="optimizeRename(index, file.imagePath, newName, dataToPreserve, $event) ">
                <div class="form-input-container" v-if="!attemptSubmit && !optimizing">
                    <div class="form-group"
                         :class="{ 'has-error' : attemptSubmit && missingNewName }">
                        <label :for="index + 'newname'"
                               class="col-sm-2 control-label">
                            Name
                        </label>
                        <div class="col-sm-10">
                            <input type="text"
                                   class="form-control new-name-input"
                                   :id="index + 'newname'"
                                   name="newname"
                                   v-model="newName"
                                   placeholder="Enter New Image Name"
                                    >
                            <p class="input-error" style="color: #a94442"
                               v-if="attemptSubmit && missingNewName ">
                                Please Enter A New Image Name
                            </p>
                        </div>
                    </div>
                    <p>Data to Preserve: </p>
                    <div class="radio">
                        <label>
                            <input type="radio"
                                   name="preserveOption"
                                   :id="index + '-rename-none'"
                                   value="none"
                                   v-model="dataToPreserve"
                                   autocomplete="off" checked>
                            None
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio"
                                   name="preserveOption"
                                   :id="index + '-rename-copy'"
                                   value="copyright"
                                   v-model="dataToPreserve"
                                   autocomplete="off">
                            Copyright
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio"
                                   name="preserveOption"
                                   :id="index + '-rename-creation'"
                                   value="creation"
                                   v-model="dataToPreserve"
                                   autocomplete="off">
                            Creation
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio"
                                   name="preserveOption"
                                   :id="index + '-rename-location'"
                                   value="location"
                                   v-model="dataToPreserve"
                                   autocomplete="off">
                            Location
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio"
                                   name="preserveOption"
                                   :id="index + '-rename-all'"
                                   value="all"
                                   v-model="dataToPreserve"
                                   autocomplete="off">
                            All Three
                        </label>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit"
                                    class="btn btn-default tnypng-submit"
                                    :disabled="attemptSubmit && missingNewName ">
                                Save &amp Optimize
                            </button>
                        </div>
                    </div>
                </div>
                <div class="tnypng-spinner-container" v-if="optimizing">
                    <svg class="bolt-spinner" viewBox="0 0 100 100">
                        <use xlink:href="#bolt-spinner"/>
                    </svg>
                    <p class="imageToOptimize">
                        {{ newName }} is being optimized...
                    </p>
                </div>
            </form>
            <div class="modal-footer" slot="footer">
                <button type="button"
                        class="btn btn-default"
                        @click="closeModal">
                    Cancel
                </button>
            </div>
        </modal>
    </div>
</template>

<script>
    // import ClickOutside from 'vue-click-outside';
    import BoltButton from '../Bolt/Button/BoltButton';

    import { Modal } from 'uiv';
    import axios from 'axios';

    export default {
        name: 'TinypngOptimizerename',

        components: {
            BoltButton, Modal
        },

        props: {
            file: {
                required: true
            },

            index: {
              required: true
            },

            disabled: {
                type: Boolean,
                default: false
            },


            renamePath: {
                required: true,
                type: String
            },

            isOptimizing: {
                default:  false
            },

        },

        data() {
            return {
                openModal: false,
                attemptSubmit: false,

                newName: '',
                noName: false,
                working: this.isOptimizing,
                dataToPreserve: 'none'
            }
        },

        methods: {
            optimizeRename: function (index, file, newName, dataToPreserve, event) {
                this.attemptSubmit = true;


                    // console.log(this.newName);
                if (this.newName) {

                    this.working = true;

                    axios.post(this.renamePath, {


                        image: file,
                        newName: newName,
                        preserve: dataToPreserve,

                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                        },
                        withCredentials: true,
                    })
                        .then( response => {

                            this.btnDisabled = false;
                            this.working = false;

                            this.closeModal();
                            this.$emit( 'tinypng-image-renamed', response );

                        })
                        .catch(function (error) {

                            console.log(error.response);
                        });
                } else {
                    event.preventDefault();
                }




            },

            closeModal: function () {
                this.openModal = false;
                this.attemptSubmit = false;
                this.newName = '';
            }
        },

        computed: {

            missingNewName() {
                    return this.newName === '';
            },

            optimizing() {
                return this.attemptSubmit && this.working;
            }
        }
    }
</script>