import Vue from 'vue';
import TinypngApp from './TinypngApp';

// for iOS devices since vue-click-outside needs it to function on those devices
if ('ontouchstart' in document.documentElement) {
    document.body.style.cursor = 'pointer';
}

new Vue({
    el: '#tinypngApp',
    delimiters: ['${', '}'],
    components: {
        TinypngApp
    }
});
