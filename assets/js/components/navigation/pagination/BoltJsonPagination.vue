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
                type: Array,
                default: () => [ ]
            },
            listClass: {
                type: Array,
                default: () => ['pagination']
            },
            listItemClass: {
                type: Array,
                default: () => []
            },
            anchorClass: {
                type: Array,
                default: () => []
            },
            activeClass: {
                type: Array,
                default: () => ['active']
            },
            activeText: {
                type: Array,
                default: () => ['(current)']
            },
            disabledClass: {
                type: Array,
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
            },

            maxVisibleButtons: {
                type: Number,
                required: false,
                default: 3
            },
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
