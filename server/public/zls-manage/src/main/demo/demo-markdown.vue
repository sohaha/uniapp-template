<style>
.panel .panel {
  margin-bottom: 10px;
}
</style>
<template>
  <div>
    <div class="view-title float-clear"></div>
    <fieldset>
      <legend>Markdown 示例</legend>
      <aside>
        <components_markdown
          ref="editor"
          v-model="content"
          @html="onHtml"
          :config="config"
          @init="initDone"
        ></components_markdown>
        <br>
        <div class="panel" v-show="hasInit">
          <div class="tip-area">文本内容</div>
          <code>{{content2n}}</code>
        </div>
        <div class="panel" v-show="hasInit">
          <div class="tip-area">富文本内容</div>
          <code>{{contentHtml}}</code>
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
Spa.define(
  {
    name: "Demo-View",
    mixins: [initTitle],
    data: function() {
      return {
        content: "> 一个 **markdown** *编辑器*",
        contentHtml: "",
        config: {
          height: 300
        }, // 自定义
        hasInit: false
      };
    },
    mounted: function() {},
    computed: {
      content2n: function() {
        return $this.content.replace(/\n/g, "\\n");
      }
    },
    init: function(query, search) {},
    methods: {
      initDone: function(e) {
        console.log("编辑器初始化完成");
        $this.hasInit = true;
      },
      onHtml: function(e) {
        $this.contentHtml = e;
      },
      setContent: function(e) {
        // 手动设置编辑器内容
        $this.$refs["editor"].setValue(e);
      }
    }
  },
  ["/components/markdown"],
  "/index"
);
</script>
