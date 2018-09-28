<template>
    <table class="dashboardlisting"
           role="grid"
           aria-labelledby="directoryCaption"
           aria-readonly="true"
           id="tinypng-dirlist-table"
    >
        <caption id="directoryCaption">
            Image Directories <span v-if="captionVisible">Sorted By Directory: {{ activeSortDirection }}</span>
        </caption>
        <thead>
        <tr role="row">
            <th scope="col"
                role="columnheader"
                :aria-sort=" activeSortDirection ? activeSortDirection : 'none' "
                class="tinypng-table-header"
                @click="sortBy('path')"
            >
                <span role="button"
                      tabindex="0"
                      class="tinypng-table-header--button" >
                    Directory
                    <i class="fa"
                       :class=" sortedIcons ">
                    </i>
                </span>
            </th>
            <th scope="col"
                role="columnheader"
            >
                Subdirectory
            </th>
        </tr>
        </thead>
        <tbody>
            <tr v-for="directory in sortedDirectories" role="row">
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
    </table>
</template>

<script>

  import  orderBy from 'lodash/orderBy';


    export default {

        name: 'TinypngDirectorylist',

        props: {
            directories: {

            },
            directory: {

            },
            pathAllImages: {
                type: String,
            },
            subDirectoryPath: {
                type: String,
            },
        },

        data() {

            return {
                captionVisible: false,
                sortDirection: 'asc',

                sort: {
                    sortBy: 'path',
                    activeDirection: '',
                },
                sortIcons: {
                    asc: false,
                    desc: false
                },

                directoriesList: this.directories,
                currentSort: 'path',
            }
        },

        methods: {
            sortBy: function (sort) {
                if (sort === this.sort.sortBy) {
                    this.sort.activeDirection = this.sort.activeDirection === 'ascending' ? 'descending' : 'ascending';
                    this.captionVisible = true;
                }

                this.sort.sortBy = sort;
            }
        },


        computed: {
            hasSubDirectory() {
                return Object.keys(this.directoriesList).length > 0;
            },

            activeSortDirection(){
                return this.sort.activeDirection;
            },

            sortedDirectories() {

                let direction = 'asc';

                if (this.sort.activeDirection === 'descending' ) {
                    direction = 'desc';
                }

                return orderBy( this.directoriesList, [ this.sort.sortBy ], [ direction ] );

                // if ( this.sort.activeDirection === 'ascending' ) {
                //
                //     this.sortIcons.asc = true;
                //     this.sortIcons.desc = false;
                //
                //     return this.directoriesList.sort( (a, b) => {
                //         let pathA = a.path.toLocaleLowerCase();
                //         let pathB = b.path.toLocaleLowerCase();
                //
                //         return pathA.localeCompare(pathB);
                //     })
                // }
                //
                // if ( this.sort.activeDirection === 'descending' ) {
                //
                //     this.sortIcons.asc = false;
                //     this.sortIcons.desc = true;
                //
                //
                //     return this.directoriesList.sort( (a, b) => {
                //         let pathA = a.path.toLocaleLowerCase();
                //         let pathB = b.path.toLocaleLowerCase();
                //
                //
                //         return pathB.localeCompare(pathA);
                //     })
                // }
                //
                // return this.directoriesList;
            },

            // should refactor this to use props or data instead of returning the
            // icon name in a string
            sortedIcons() {
                if ( this.sort.activeDirection === 'ascending' ) {
                    return 'fa-arrow-down';
                }
                if ( this.sort.activeDirection === 'descending' ) {
                    return 'fa-arrow-up';
                }
                return 'fa-arrows-v';
            }

        },
    }
</script>