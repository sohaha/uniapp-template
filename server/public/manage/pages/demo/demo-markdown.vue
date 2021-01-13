<template>
  <div>
    <div class="view-title float-clear"></div>
    <fieldset>
      <legend>Markdown 示例</legend>
      <aside>
        <markdown
            ref="editorRef"
            v-model="content"
            @html="onHtml"
            :config="config"
            @init="initDone"
        ></markdown>
        <br>
        <div class="panel" v-show="hasInit">
          <div class="tip-area">文本内容</div>
          <code>{{ content2n }}</code>
        </div>
        <div class="panel" v-show="hasInit">
          <div class="tip-area">富文本内容</div>
          <code>{{ contentHtml }}</code>
        </div>
      </aside>
    </fieldset>
    <div class="panel">
      更多自定义配置请参考
      <a
          href="https://hacpai.com/article/1549638745630?r=Vanessa"
          target="_blank"
      >Vditor 文档</a>。
    </div>
  </div>
</template>
<script>
const {ref, reactive, computed, onMounted, watch, onBeforeUnmount} = vue;
const {useRouter, useStore, useCache, useTip, useLoading, useConfirm} = hook;
const {user: userApi, useRequest} = api;
const {useInitTitle} = util;

export default {
  components: {
    markdown: VueRun('components/markdown.vue')
  },
  name: 'Demo-View',
  setup(prop, ctx) {
    const {title} = useInitTitle(ctx);
    onMounted(() => {
    })

    let editorRef = ref(null);
    let content = ref('> 一个 **markdown** *编辑器*');
    let contentHtml = ref('');
    let config = ref({height: 300});
    let hasInit = ref(false);

    const content2n = computed(() => {
      return content.value.replace(/\n/g, "\\n");
    })

    function initDone(e) {
      console.log("编辑器初始化完成");
      hasInit.value = true;
    }

    function onHtml(e) {
      contentHtml.value = e;
    }

    function setContent(e) {
      editorRef.value.setValue(e);
    }

    return {
      title,
      config,
      onHtml,
      initDone,
      content,
      hasInit,
      content2n,
      contentHtml
    };
  }
};
</script>

<style scoped>
.panel .panel {
  margin-bottom: 10px;
}
</style>
