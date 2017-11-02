<template>
    <p>
        <a :href="subDirectoryPath">
            {{ subdirectory }}
        </a>
    </p>
</template>

<script>
    export default {

        name: 'tinypng-subdirectory',

        props: {

            subdirectory: {

            },

            subDirectoryPath: {
                type: String,
            }
        },

    }
</script>