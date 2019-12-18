<style>
.panel .panel {
  margin-bottom : 10px;
}

</style>
<template>
  <div>
    <div class="view-title float-clear"></div>
    <fieldset>
      <legend>富文本示例</legend>
      <aside>
        <components_tinymce ref="editor" v-model="content" :config="tinymceConfig" @init="initDone"></components_tinymce>
        <br>
        <div class="panel" v-show="tinymceHasInit">
          <div class="tip-area">富文本内容</div>
          <code>{{content}}</code>
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
Spa.define(
  {
    name: "Demo-View",
    mixins: [mixinLists, initTitle],
    data: function() {
      return {
        content: "",
        tinymceConfig: {}, // 自定义
        tinymceHasInit: false
      };
    },
    mounted: function() {},
    computed: {},
    init: function(query, search) {},
    methods: {
      initDone: function(e) {
        console.log("编辑器初始化完成");
        $this.tinymceHasInit = true;
        $this.setContent(
          '<p>我是一个<span style="color: #2880b9;">富文本</span><span style="background-color: #bdc3c7; color: #ffffff;">编辑器</span></p>'
        );
      },
      setContent: function(e) {
        // 手动设置编辑器内容
        $this.$refs["editor"].setContent(e);
      }
    }
  },
  ["/components/tinymce"],
  "/index"
);
</script>
