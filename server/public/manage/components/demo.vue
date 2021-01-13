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
const {useRouter, useStore} = hook;
const {toRef, ref, watch, computed} = vue;

export default {
  name: 'demo',
  props: {},
  setup(prop, ctx) {
    let val1 = ref('');
    let val2 = ref('');

    watch(val1, (val) => {
      val2.value = val;
    })

    return {
      val1,
      val2
    };
  }
};
</script>

<style>
.el-input + .el-input {
  margin-top: 10px;
}
</style>
