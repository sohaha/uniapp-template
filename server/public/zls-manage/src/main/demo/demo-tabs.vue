<style>
</style>
<template>
  <div>
    <div class="view-title float-clear no">
      <div class="view-title-right"></div>
    </div>
    <el-tabs class="reply-tabs" v-model="activeName" @tab-click="handleClick">
      <el-tab-pane :key="k" v-for="(v,k) in tabs" :label="v" :name="k"></el-tab-pane>
    </el-tabs>
    <div class="panel">{{tabs[activeName]}}</div>
  </div>
</template>
<script>
Spa.define(
  {
    mixins: [initTitle],
    data: function() {
      return {
        activeName: "key3",
        tabs: {
          key1: "标签页1",
          key2: "标签页2",
          key3: "标签页3"
        }
      };
    },
    mounted: function() {},
    computed: {},
    init: function(query, search) {},
    methods: {
      handleClick: function(tab, event) {
        console.log(tab, event);
      }
    }
  },
  [],
  "/index"
);
</script>
