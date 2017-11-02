<template>
    <div class="btn-group" :class="{ open: isVisible } ">

      <slot name="button">
            <bolt-button type="button"
                         :disabled="disabled" @click="optimizeImage">
                Optimize Image
            </bolt-button>
      </slot>

      <slot name="toggle-button">
        <bolt-button type="button" extraClasses="dropdown-toggle"
                aria-haspopup="true"
                :aria-pressed=" isVisible ? 'true' : 'false' "
                :aria-expanded="isVisible ? 'true' : 'false' "
                @click="toggleDropdown"
                @keyup.esc="isVisible = false"
                :disabled="disabled"
                v-click-outside="closeDropdown">
            <span class="caret"></span>
            <span class="sr-only">{{ srText ? srText : 'Toggle Dropdown' }}</span>
        </bolt-button>
      </slot>

      <slot name="dropdown-menu">
          <ul class="dropdown-menu">
            <slot></slot>
          </ul>
      </slot>

    </div>
</template>

<script>
    import ClickOutside from 'vue-click-outside';
    import BoltButton from './BoltButton';

    export default {
        name: 'split-button',

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
            }
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

            optimizeImage: function (event) {
                this.$emit('tinypng-optimize');
            }
        },

        directives: {
            ClickOutside
        }
    }
</script>