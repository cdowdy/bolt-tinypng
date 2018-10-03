<template>
    <table class="dashboardlisting table-hover"
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
           <template v-for="directory in sortedDirectories" >
               <template v-if="directory.subdirectory.length < 1">
                   <tr role="row">
                       <td>
                           <a :href="directory.route ">
                               {{ directory.path }}
                           </a>
                       </td>
                       <td></td>
                   </tr>
               </template>
               <template v-else>
                   <tr role="row"
                       :rowspan="directory.subdirectory.length">
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
               </template>
           </template>
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