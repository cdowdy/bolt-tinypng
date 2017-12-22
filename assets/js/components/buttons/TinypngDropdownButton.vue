<template>
    <div class="btn-group tinypng-button-group" :class="{ open: isVisible } ">

        <bolt-button type="button"
                     :disabled="disabled"
                     @click="optimizeImage(fileIndex, file.imagePath, 'none' )">
            Optimize Image
        </bolt-button>


        <bolt-button type="button"
                     extraClasses="dropdown-toggle"
                     aria-haspopup="true"
                     :aria-pressed=" isVisible ? 'true' : 'false' "
                     :aria-expanded="isVisible ? 'true' : 'false' "
                     @click="toggleDropdown"
                     @keyup.esc="isVisible = false"
                     :disabled="disabled"
                     v-click-outside="closeDropdown">
            <span class="caret"></span>
            <span class="sr-only">
                {{ srText ? srText : 'Toggle Dropdown' }}
            </span>
        </bolt-button>

        <ul class="dropdown-menu">
            <li>
                <a @click.prevent="optimizeImage(fileIndex, file.imagePath, 'copyright')"
                   class="tnypng-copy"
                   href="#">Preserve Copyright
                </a>
            </li>
            <li>
                <a @click.prevent="optimizeImage(fileIndex, file.imagePath, 'creation')"
                   class="tnypng-create"
                   href="#">Preserve Creation</a>
            </li>
            <li>
                <a @click.prevent="optimizeImage(fileIndex, file.imagePath, 'location')"
                   class="tnypng-location"
                   href="#">Preserve Location</a>
            </li>
            <li>
                <a @click.prevent="optimizeImage(fileIndex, file.imagePath, 'all')"
                   class="tnypng-allthree"
                   href="#">Preserve All Three (3)</a>
            </li>
        </ul>
    </div>
</template>

<script>
    import ClickOutside from 'vue-click-outside';
    import BoltButton from '../Bolt/Button/BoltButton';


    export default {
        name: 'TinypngDropdownbutton',

        components: {
            BoltButton,
        },

        props: {
            // the screen reader text used for the 'sr-only' class in the span underneath the 'caret'
            srText: {
                type: String,
                default: ''
            },

            disabled: {
                type: Boolean,
                default: false
            },

            file: {
                required: true
            },
            fileIndex: {
                type: Number
            },
        },

        data() {
            return {
                isVisible: false
            }
        },

        methods: {

            toggleDropdown: function () {

                if (this.disabled) {
                    return;
                }

                this.isVisible = !this.isVisible;
            },

            closeDropdown: function () {

                this.isVisible = false; // false

            },

            optimizeImage: function (fileIndex, file, toPreserve) {
                this.$emit('tinypng-optimize', fileIndex, file, toPreserve  );
            }
        },

        directives: {
            ClickOutside
        }
    }
</script>