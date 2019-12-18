<style></style>
<template>
  <div>
    <div class="view-title float-clear"></div>
    <fieldset>
      <legend v-text="title"></legend>
      <aside class="center" aria-label="开发说明">
        <el-container>
          <el-aside width="auto">
            <el-tabs tab-position="left">
              <el-tab-pane label="1"></el-tab-pane>
              <el-tab-pane label="2"></el-tab-pane>
              <el-tab-pane label="3"></el-tab-pane>
              <el-tab-pane label="4"></el-tab-pane>
            </el-tabs>
          </el-aside>
          <el-main>11</el-main>
        </el-container>
      </aside>
    </fieldset>
  </div>
</template>
<script>
  Spa.define(
    {
      mixins: [mixinLists, initTitle],
      data: function () {
        return { title: '个人设置(开发中)' };
      },
      mounted: function () {},
      computed: {},
      init: function (query, search) {},
      methods: {}
    },
    ['/components/demo'],
    '/index'
  );
</script>
