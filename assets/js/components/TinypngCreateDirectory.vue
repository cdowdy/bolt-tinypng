<template>
<div>
    <div>
        <h2>Create a New Directory Here:</h2>

        <p>
            This will create a new 'sub' directory in <b v-if="directory === 'index' ">Files</b><b v-else> {{ directory }}</b>
        </p>
        <form class="form-inline"
              name="tinypng_new_dir"
              method="POST"
              :action="createActionPath">
            <div class="form-group">
                <label class="sr-only"
                       for="tinypngNewDirectory">Your New Directory Name</label>
                <input type="text"
                       class="form-control"
                       id="tinypngNewDirectory"
                       name="tinypngNewDirectory"
                       placeholder="New_DirectoryName" required>
            </div>
            <button type="submit"
                    class="btn btn-default">Create Directory</button>
        </form>
    </div>

    <div>
        <h2>Delete Directory</h2>
        <div class="alert alert-danger">
            <p style="color: #800000">
                This will remove/delete the directory chosen below. Everything contained in this directory will be deleted and non recoverable. Proceed with caution.
            </p>
        </div>

        <form class="form-inline"
              name="tinypng_delete_directory"
              method="POST"
              :action="deleteActionPath">
            <div class="form-group">
                <label class="sr-only"
                       for="tinypngDeleteDirectory">Delete A Directory</label>
                <input class="form-control"
                       id="tinypngDeleteDirectory"
                       name="tinypngDeleteDirectory"
                       placeholder="Delete A Directory"
                       list="betterthumbsDirectoryList">

                <datalist id="betterthumbsDirectoryList">
                    <template v-for="directory in directoriesList">
                        <option  :value="directory.path">
                            {{ directory.path  }}
                        </option>
                        <option v-for="dir in directory.subdirectory" v-if="dir.path" :value="dir.path " >
                           {{ dir.path }}
                        </option>
                    </template>
                </datalist>
                <p >

                </p>
            </div>
            <button type="submit"
                    class="btn btn-danger">Delete Directory</button>
        </form>
    </div>
</div>
</template>

<script>
    export default {

        name: 'tinypng-newdirectory',

        props: {
            directory: {
                type: String,
                required: true
            },

            createActionPath: {
                type: String,
                required: true
            },

            deleteActionPath: {
                type: String,
                required: true
            },

            directories: {
                required: true
            }
        },

        data() {
            return {
                directoriesList: this.directories
            }
        }
    }
</script>