<template>
  <div>
    <div class="view-title float-clear no">
      <div class="view-title-right"></div>
    </div>
    <el-tabs class="reply-tabs" v-model="activeName" @tab-click="useHandleClick">
      <el-tab-pane :key="k" v-for="(v,k) in tabs" :label="v" :name="k"></el-tab-pane>
    </el-tabs>
    <div class="panel">{{ tabs[activeName] }}</div>
  </div>
</template>
<script>
const {ref, reactive, computed, onMounted, watch, onBeforeUnmount} = vue;
const {useRouter, useStore, useCache, useTip, useLoading, useConfirm} = hook;
const {user: userApi, useRequest} = api;
const {useInitTitle} = util;

export default {
  components: {},
  setup(prop, ctx) {
    const {title} = useInitTitle(ctx);

    let activeName = ref('key3');
    let tabs = ref({
      key1: "标签页1",
      key2: "标签页2",
      key3: "标签页3"
    });

    function useHandleClick(tab, event) {
      console.log(tab, event);
    }

    return {
      title,
      activeName,
      tabs,
      useHandleClick
    };
  }
};
</script>

<style scoped>
</style>
