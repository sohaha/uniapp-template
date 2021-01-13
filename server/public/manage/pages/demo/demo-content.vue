<template>
  <div>
    <div class="view-title float-clear"></div>
    <fieldset>
      <legend>富文本示例</legend>
      <aside>
        <tinymce ref="editorRef" v-model="content" :config="tinymceConfig"
                            @init="initDone"
                 :images-upload-handler="imagesUploadHandler"></tinymce>
        <br>
        <div class="panel" v-show="tinymceHasInit">
          <div class="tip-area">富文本内容</div>
          <code>{{ content }}</code>
        </div>
      </aside>
    </fieldset>
    <div class="panel">
      更多自定义配置请参考
      <a
          href="https://www.tiny.cloud/docs/advanced/events/#init"
          target="_blank"
      >tinymce 文档</a>。
    </div>
  </div>
</template>
<script>
const {ref, reactive, computed, onMounted, watch, onBeforeUnmount} = vue;
const {useRouter, useStore, useCache, useTip, useLoading, useConfirm} = hook;
const {user: userApi, useRequest} = api;
const {useInitTitle} = util;

export default {
  name: 'Demo-View',
  components: {
    tinymce: VueRun('components/tinymce.vue')
  },
  setup(prop, ctx) {
    const {title} = useInitTitle(ctx);

    let editorRef = ref(null);
    let content = ref('');
    let tinymceConfig = ref({});
    let tinymceHasInit = ref(false);

    function initDone(e) {
      console.log("编辑器初始化完成");
      tinymceHasInit.value = true;
      setContent(
          '<p>我是一个<span style="color: #2880b9;">富文本</span><span style="background-color: #bdc3c7; color: #ffffff;">编辑器</span></p>'
      );
    }

    function setContent(e) {
      editorRef.value.setContent(e)
    }

    function useImagesUploadHandler(blobInfo, succFun, failFun) {
      var xhr, formData;
      var file = blobInfo.blob();
      xhr = new XMLHttpRequest();
      xhr.withCredentials = false;
      xhr.open('POST', userApi.demoUpload());//后端返回格式{ location : "/demo/image/1.jpg" }
      xhr.onload = function () {
        var json;
        if (xhr.status !== 200) {
          failFun('HTTP Error: ' + xhr.status);
          return;
        }
        json = JSON.parse(xhr.responseText);
        if (!json || typeof json.location !== 'string') {
          failFun('Invalid JSON: ' + xhr.responseText);
          return;
        }
        succFun(json.location);
      }
      formData = new FormData();
      formData.append('file', file, file.name);
      xhr.send(formData);
    }

    return {
      title,
      tinymceConfig,
      initDone,
      content,
      tinymceHasInit,
      editorRef,
      imagesUploadHandler: useImagesUploadHandler
    };
  }
};
</script>

<style scoped>
.panel .panel {
  margin-bottom: 10px;
}
</style>
