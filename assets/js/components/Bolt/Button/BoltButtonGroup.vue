<template>
    <div class="btn-group" role="group" >
        <slot></slot>
    </div>
</template>

<script>

    export default {

        name: 'bolt-buttongroup',
        props: {
            arialabel: {
                type: String,
            },
        },
    }
</script>