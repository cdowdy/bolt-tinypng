<template>
    <modal :title="'Optimize &amp; Rename ' +  modalTitle ">
        <p>
            This will optimize your image and save it under the name you choose
            below in the text input box.
        </p>
        <form class="form-horizontal"
              :id="index + '-rename-form'"
              method="POST"
              :action="renamePath">
            <div class="form-input-container">
                <div class="form-group">
                    <label :for="index + 'newname'"
                           class="col-sm-2 control-label">
                        Name
                    </label>
                    <div class="col-sm-10">
                        <input type="text"
                               class="form-control new-name-input"
                               :id="index + 'newname'"
                               name="newname"
                               placeholder="Enter New Image Name">
                        <p class="input-error" style="color: #a94442" hidden>
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
                               data-optiparam="none"
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
                               data-optiparam="copyright"
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
                               data-optiparam="creation"
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
                               data-optiparam="location"
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
                               data-optiparam="all"
                               autocomplete="off">
                        All Three
                    </label>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit"
                                class="btn btn-default tnypng-submit">Save &amp
                            Optimize
                        </button>
                    </div>
                </div>
            </div>
            <div class="tnypng-spinner-container" hidden>
                <svg class="bolt-spinner" viewBox="0 0 100 100">
                    <use xlink:href="#bolt-spinner"/>
                </svg>
                <p class="imageToOptimize">

                </p>
            </div>
        </form>
        <div class="modal-footer" slot="footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">
                Cancel
            </button>
        </div>
    </modal>
</template>

<script>
    // import ClickOutside from 'vue-click-outside';
    import { Modal } from 'uiv';

    export default {
        name: 'TinypngRenamemodal',

        components: {
            Modal,
        },

        props: {

            modalTitle: {
                type: String,
                required: true,
            },

            renamePath: {
                required: true,
                type: String
            },

            index: {
                type: Number
            },


        },

        data() {

        },

        methods: {

        },

        directives: {

        }
    }
</script>