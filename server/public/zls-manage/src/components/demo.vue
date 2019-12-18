<style>
.el-input + .el-input {
  margin-top : 10px;
}

</style>
<template>
  <fieldset>
    <legend>示例组件</legend>
    <aside>
      <el-input v-model="val1" placeholder size="small"></el-input>
      <el-input v-model="val2" placeholder size="small"></el-input>
    </aside>
  </fieldset>
</template>
<script>
Spa.define({
  props: {},
  data: function() {
    return {
      val1: "",
      val2: ""
    };
  },
  computed: {},
  watch: {
    val1: function(v) {
      this.val2 = v;
    }
  },
  init: function(query, search) {},
  methods: {}
});
</script>
