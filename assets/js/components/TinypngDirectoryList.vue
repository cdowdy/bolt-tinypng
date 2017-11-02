<template>
    <tbody>
    <tr>
        <td>
            <a :href="pathAllImages">
                Files
            </a>
        </td>
        <td></td>
    </tr>
    <tr v-for="directory in directoriesList">
        <td>
            <a :href="directory.route ">
                {{ directory.path }}
            </a>
        </td>
        <td>
            <p v-for="dir in directory.subdirectory" v-if="dir.path">
                <a :href="dir.route">
                    {{ dir.path }}
                </a>
            </p>
        </td>
    </tr>
    </tbody>
</template>

<script>
    export default {

        name: 'TinypngDirectorylist',

        props: {
            directories: {

            },
            directory: {

            },
            // subdirectory: {
            //
            // },

            pathAllImages: {
                type: String,
            },
            subDirectoryPath: {
                type: String,
            }
        },

        data() {
            return {
                directoriesList: this.directories
            }
        },

        computed: {
            hasSubDirectory() {
                return Object.keys(this.directoriesList).length > 0;
            }
        },
        //
        // mounted() {
        //     this.isActive = this.selected;
        // }
    }
</script>