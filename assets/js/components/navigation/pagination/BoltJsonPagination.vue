<template>
    <nav role="navigation"
         :aria-label="ariaLabel"
         :class="[ navClass ]"
    >
        <ul :class="[ listClass ]">
            <li :class="[ listItemClass ]">

            </li>
        </ul>

    </nav>
</template>

<script>
    export default {
        name: "BoltJsonPagination",

        props: {
            ariaLabel: {
                type: String,
                required: true,
            },
            navClass: {
                type: array,
                default: () => [ 'pagination']
            },
            listClass: {
                type: array,
                default: () => []
            },
            listItemClass: {
                type: array,
                default: () => []
            },
            anchorClass: {
                type: array,
                default: () => []
            },
            activeClass: {
                type: array,
                default: () => ['active']
            },
            activeText: {
                type: array,
                default: () => ['(current)']
            },
            disabledClass: {
                type: array,
                default: () => ['disabled']
            },
            previousLabel: {
                type: String,
                default: 'Previous'
            },
            previousIcon: {
                type: String,
                default: '&laquo;'
            },
            nextLabel: {
                type: String,
                default: 'Next'
            },
            nextIcon: {
                type: String,
                default: '&raquo;'
            },

            listData: {
                required: true
            },

            size: {
                type: Number,
                required: false,
                default: 20
            }
        },

        data() {
            return {
                pageNumber: 0
            }
        },

        methods: {
            previousPage() {
                this.pageNumber++;
            },


            nextPage() {
                this.pageNumber--;
            }
        }
    }
</script>

<style scoped>

</style>