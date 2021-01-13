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
const {ref, reactive, computed, onMounted, watch, onBeforeUnmount} = vue;
const {useRouter, useStore, useCache, useTip, useLoading} = hook;
const {user: userApi, useRequestWith} = api;
const {useInitTitle} = util;

export default {
  components: {},
  setup(prop, ctx) {
    onMounted(() => {
    })

    let title = ref('个人设置(开发中)');

    return {
      title
    };
  }
};
</script>

<style scoped>
.page-user-menu .tree-placeholder {
  display: inline-block;
  width: 20px;
  line-height: 20px;
  height: 20px;
  text-align: center;
  margin-right: 3px;
}
</style>
