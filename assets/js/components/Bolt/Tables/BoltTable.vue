<template>
    <table :id="id || null" >
        <slot name="thead"></slot>

            <slot name="table-body"></slot>

    </table>
</template>

<script>
    export default {

        name: 'bolt-table',

        props: {
            id: {
                type: String,
                default: ''
            },
            caption: {
                type: String,
                default: null
            },
        },


    }
</script>