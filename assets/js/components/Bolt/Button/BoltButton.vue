<template>
    <button :type="type"
            :class="[ 'btn', 'btn-' + variant, 'btn-' + buttonSize, extraClasses ]"
            :disabled="disabled"
            @click="handleClick">
        <slot name="buttonIcon"></slot>
        <slot></slot>
    </button>
</template>

<script>
    export default {

        name: 'BoltButton',
        props: {

            type: {
                default: 'submit',
                required: true
            },
            disabled: {
                type: Boolean,
                default: false
            },

            variant: {
                type: String,
                default: 'default'
            },

            buttonSize: {
                type: String,
                default: 'sm'
            },

            extraClasses: {
                type: String
            }
        },

        computed: {
            btnVariant() {
                return !this.variant || this.variant === `default` ? `btn-secondary` : `btn-${this.variant}`;
            },

            btnSize() {
                return !this.buttonSize || this.buttonSize === `default` ? `` : `btn-${this.buttonSize}`;
            }
        },

        methods: {
            handleClick (e) {
                this.$emit('click', e)
            }
        }
    }
</script>