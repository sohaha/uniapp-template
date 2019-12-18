<style>
</style>
<template>
  <div :class="{fullscreen:fullscreen}" class="tinymce-container editor-container">
    <label> <textarea :id="tinymceId" class="tinymce-textarea"></textarea> </label>
  </div>
</template>
<script>
  var loadJs = [
    '//cdn.jsdelivr.net/npm/zls-manage/tinymce/tinymce.min.js'];
  var that;
  Spa.define({
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
    data() {
      return {
        hasChange: false,
        hasInit: false,
        tinymceId: this.id,
        fullscreen: false,
      };
    },
    watch: {
      value: function (val) {
        if (!this.hasChange && this.hasInit) {
          this.$nextTick(function () {
            window.tinymce.get(this.tinymceId)
                  .setContent(val || '');
          });

        }
      }
    },
    created: function () {
      that = this;
    },
    mounted: function () {
      this.initTinymce();
    },
    activated: function () {
      this.initTinymce();
    },
    deactivated: function () {
      this.destroyTinymce();
    },
    destroyed: function () {
      this.destroyTinymce();
    },
    methods: {
      initTinymce: function () {
        window.tinymce.init(Object.assign({
          language: 'zh_CN',
          selector: '#' + that.tinymceId,
          height: that.height,
          body_class: 'panel-body ',
          object_resizing: false,
          toolbar: that.toolbar,
          menubar: that.menubar,
          plugins: that.plugins,
          images_upload_url: that.imagesUploadUrl,
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
            if (that.value) {
              editor.setContent(that.value);
            }
            that.hasInit = true;
            that.$emit('init', editor);
            editor.on('NodeChange Change KeyUp SetContent', function () {
              that.hasChange = true;
              that.$emit('input', editor.getContent());
            });
          },
          setup: function (editor) {
            editor.on('FullscreenStateChanged', function (e) {
              that.fullscreen = e.state;
            });
          }
        }, that.config));
      },
      destroyTinymce: function () {
        var tinymce = window.tinymce.get(this.tinymceId);
        if (this.fullscreen) {
          tinymce.execCommand('mceFullScreen');
        }
        if (tinymce) {
          tinymce.destroy();
        }
      },
      setContent: function (value) {
        window.tinymce.get(this.tinymceId)
              .setContent(value);
      },
      getContent: function () {
        window.tinymce.get(this.tinymceId)
              .getContent();
      }
    }
  });
</script>
