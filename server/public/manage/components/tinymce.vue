<style>
</style>
<template>
  <div :class="{fullscreen:fullscreen}" class="tinymce-container editor-container">
    <label> <textarea :id="tinymceId" class="tinymce-textarea"></textarea> </label>
  </div>
</template>
<script>
export default load({js: ['//cdn.jsdelivr.net/npm/zls-manage/tinymce/tinymce.min.js']}).then(async () => {
  const {useRouter, useStore} = hook;
  const {toRef, ref, watch, computed, onMounted} = vue;
  return {
    name: 'Tinymce',
    props: {
      id: {
        type: String,
        default: function () {
          return 'vue-tinymce-' + +new Date() + ((Math.random() * 1000).toFixed(0) + '');
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
      imagesUploadUrl: {
        type: String,
        default: ''
      },
      imagesUploadHandler: {
        type: Function,
        default: function () {
        }
      },
      toolbar: {
        type: Array,
        required: false,
        default: function () {
          return [
            'searchreplace bold italic underline strikethrough alignleft aligncenter alignright outdent indent  blockquote undo redo removeformat subscript superscript code codesample hr bullist numlist link image charmap pagebreak insertdatetime media table emoticons forecolor backcolor preview fullscreen'
          ];
        }
      },
      menubar: {
        type: [String, Boolean],
        default: false,//'file edit insert view format table'
      },
      plugins: {
        type: Array,
        default: function () {
          return ['image fullscreen preview code'];//'advlist anchor autolink autosave code codesample directionality emoticons fullscreen hr image imagetools insertdatetime link lists media nonbreaking noneditable pagebreak paste preview print save searchreplace spellchecker tabfocus table template textpattern visualblocks visualchars wordcount'
        }
      },
      height: {
        type: Number,
        required: false,
        default: 360
      }
    },
    setup(prop, ctx) {
      const {root} = ctx;
      let hasChange = ref(false);
      let hasInit = ref(false);
      let tinymceId = ref(prop.id);
      let fullscreen = ref(false);

      watch(() => prop.value, (val) => {
        if (!hasChange.value && hasInit.value) {
          root.$nextTick(() => {
            window.tinymce.get(tinymceId.value).setContent(val || '');
          })
        }
      })

      function initTinymce() {
        window.tinymce.init(Object.assign({
          language: 'zh_CN',
          selector: '#' + tinymceId.value,
          height: prop.height,
          body_class: 'panel-body ',
          object_resizing: false,
          toolbar: prop.toolbar,
          menubar: prop.menubar,
          plugins: prop.plugins,
          images_upload_url: prop.imagesUploadUrl,
          images_upload_handler: prop.imagesUploadHandler,
          end_container_on_empty_block: true,
          powerpaste_word_import: 'clean',
          code_dialog_height: 450,
          code_dialog_width: 1000,
          advlist_bullet_styles: 'square',
          advlist_number_styles: 'default',
          imagetools_cors_hosts: ['www.tinymce.com', 'codepen.io'],
          default_link_target: '_blank',
          link_title: false,
          nonbreaking_force_tab: true,
          init_instance_callback: function (editor) {
            if (prop.value) {
              editor.setContent(prop.value);
            }
            hasInit.value = true;
            ctx.emit('init', editor);
            editor.on('NodeChange Change KeyUp SetContent', function () {
              hasChange.value = true;
              ctx.emit('input', editor.getContent());
            });
          },
          setup: function (editor) {
            editor.on('FullscreenStateChanged', function (e) {
              fullscreen.value = e.state;
            });
          }
        }, prop.config));
      }

      function destroyTinymce() {
        let tinymce = window.tinymce.get(tinymceId.value);
        if (fullscreen.value) {
          tinymce.execCommand('mceFullScreen');
        }
        if (tinymce) {
          tinymce.destroy();
        }
      }

      function setContent(value) {
        window.tinymce.get(tinymceId.value).setContent(value);
      }

      function getContent() {
        window.tinymce.get(tinymceId.value).getContent();
      }

      return {
        fullscreen,
        tinymceId,
        initTinymce,
        destroyTinymce,
        setContent,
        getContent
      };
    },
    mounted() {
      this.initTinymce();
    },
    activated() {
      this.initTinymce();
    },
    deactivated() {
      this.destroyTinymce();
    },
    destroyed() {
      this.destroyTinymce();
    },
  };
});
</script>
