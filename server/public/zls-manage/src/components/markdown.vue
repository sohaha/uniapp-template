<style>
</style>
<template>
  <div :id="id" class='text'></div>
</template>
<script>
  var loadCss = ['//cdn.jsdelivr.net/npm/zls-manage/vditor/index.classic.css'];
  var loadJs = ['//cdn.jsdelivr.net/npm/zls-manage/vditor/index.min.js'];
  var vditor;
  Spa.define({
    props: {
      id: {
        type: String,
        default: function () {
          return 'vue-vditor-';// + +new Date() + ((Math.random() * 1000).toFixed(0) + '');
        }
      },
      config: {
        type: Object,
        required: false,
        default: function () {
          return {};
        }
      },
      value: {
        type: String,
        default: ''
      },
      width: {
        type: [Number, String],
        required: false,
        default: 'auto'
      },
      height: {
        type: Number,
        required: false,
        default: 360
      },
      imagesUploadUrl: {
        type: String,
        default: ''
      },
      toolbar: {
        type: Array,
        required: false,
        default: function () {
          return ['headings', 'bold', 'italic', 'strike', '|', 'line', 'quote', 'list', 'ordered-list', 'check', 'code', 'inline-code', 'undo', 'redo', 'link', 'emoji', 'table', 'preview', 'fullscreen'];//, 'upload'
        }
      }
    },
    data() {
      return {
        hasChange: false,
        hasInit: false,
      };
    },
    created: function () {
      that = this;
    },
    mounted: function () {
      that.initVditor();
    },
    computed: {},
    watch: {},
    init: function (query, search) {
    },
    methods: {
      setValue: function (e) {
        vditor.setValue(e);
      },
      initVditor: function () {
        vditor = new window['Vditor'](that.id, Object.assign({
          height: that.height,
          width: that.width,
          cache: false,
          toolbar: that.toolbar,
          hint: { emoji: { '+1': '👍', '-1': '👎', 'heart': '❤️️', 'cold_sweat': '😰' } },
          input: function () {
            that.$emit('input', vditor.getValue());
          },
          preview: {
            show: true, delay: 50, parse: function (e) {
              that.$emit('html', e.innerHTML);
            }
          }
        }, that.config));
        vditor.setValue(that.value);
        vditor.focus();
        that.$emit('init', vditor);
        window['a'] = vditor;
      }
    },
  });
</script>
