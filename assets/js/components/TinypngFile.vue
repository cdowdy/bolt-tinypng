<template>
    <transition name="removed-file">
        <tr >
            <td>
                <i class="fa fa-fw fa-file-image-o"></i>
                <a :href="'/thumbs/1000x1000r/' + imagePath"
                   class="magnific"
                   :title="'Image: ' + filename ">
                    <strong>{{ filename }}</strong>
                </a>
                <br>
                <div v-if="located">
                    <strong>Location:</strong> {{ located }}
                </div>
                <div v-else>
                    <strong>Location:</strong> Files
                </div>
            </td>
            <td class="listthumb">
                <img :src="'/thumbs/54x40c/' + imagePath"
                     alt=""
                     width="54"
                     height="40">
            </td>
            <td class="hidden-xs hidden-sm">
                <strong class="imgFileSize">{{ filesize }}</strong>
                <div class="image-dimensions">{{ imageWidth }}<span class="times">×</span>{{ imageHeight }} px
                </div>
            </td>
            <slot name="extra-table-data">
                <td>&nbsp;</td>
            </slot>
        </tr>
    </transition>
</template>

<script>
    export default {

        name: 'TinypngFile',
        props: {
            imagePath: {
                required: true
            },

            filename: {
                required: true
            },

            located: {
                required: true
            },

            filesize: {
                required: true
            },

            imageWidth: {
                default: ''
            },

            imageHeight: {
                default: ''
            }
        },
    }
</script>