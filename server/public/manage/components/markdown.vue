<template>
  <div :id="id" class='text'></div>
</template>
<script>
let vditor;
export default load({
  css: ['//cdn.jsdelivr.net/npm/zls-manage/vditor/index.classic.css'],
  js: ['//cdn.jsdelivr.net/npm/zls-manage/vditor/index.min.js']
}).then(async () => {
  const {useRouter, useStore} = hook;
  const {toRef, ref, watch, computed, onMounted} = vue;

  return {
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
    setup(prop, ctx) {
      onMounted(() => {
        initVditor();
      })

      let hasChange = ref(false);
      let hasInit = ref(false);

      function setValue() {
        vditor.setValue(e);
      }

      function initVditor() {
        vditor = new window['Vditor'](prop.id, Object.assign({
          height: prop.height,
          width: prop.width,
          cache: false,
          toolbar: prop.toolbar,
          hint: {emoji: {'+1': '👍', '-1': '👎', 'heart': '❤️️', 'cold_sweat': '😰'}},
          input: function () {
            ctx.emit('input', vditor.getValue());
          },
          preview: {
            show: true, delay: 50, parse: function (e) {
              ctx.emit('html', e.innerHTML);
            }
          }
        }, prop.config));
        vditor.setValue(prop.value);
        vditor.focus();
        ctx.emit('init', vditor);
        window['a'] = vditor;
      }

      return {};
    }
  };
});
</script>

<style>
</style>
