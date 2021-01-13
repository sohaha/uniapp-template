<style>
.el-select-dropdown__item {
  display: flex;
  justify-content: space-between;
}
</style>
<template>
  <el-select v-model="value" :placeholder="placeholder" size="small" @change="setData(value)" :disabled="isDisabled">
    <el-option
        size="small"
        v-for="item in options"
        :key="item.value"
        :label="item.label"
        :value="item.value">
    </el-option>
  </el-select>
</template>
<script>
const {useRouter, useStore, useTip} = hook;
const {reactive, toRef, ref, watch, computed, onMounted} = vue;
const {user: userApi, useRequest} = api;

export default {
  name: 'menuSelect',
  props: {},
  setup(prop, ctx) {
    const {root} = ctx;

    let options = ref([]);
    let value = ref('');
    let placeholder = ref('请选择');
    let isDisabled = ref(false);

    function setData(value) {
      let data = options.value;
      for (let i = 0; i < data.length; i++) {
        if (data[i].value === value) {
          ctx.emit('change', value, data[i]);
          return;
        }
      }
    }

    return {
      options,
      value,
      placeholder,
      isDisabled,
      setData,
    };
  }
};
</script>
