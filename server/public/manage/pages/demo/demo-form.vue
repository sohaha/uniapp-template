<template>
  <div>
    <div class="view-title float-clear">
      <!--占位-->
    </div>
    <fieldset>
      <legend>{{ title }}</legend>
      <aside :aria-label="title">
        <el-form
            size="mini"
            class="form-vertical"
            v-loading="formState"
            :model="ruleForm"
            :rules="rules"
            ref="ruleFormRef"
            label-width="80px"
        >
          <el-form-item label="标题：" prop="title">
            <el-input size="mini" placeholder="请输入标题" v-model="ruleForm.title" :disabled="false"></el-input>
          </el-form-item>
          <el-form-item label="选择：" prop="select">
            <el-select size="mini" v-model="ruleForm.select" placeholder="请选择">
              <el-option v-for="(v,k) in 5" :key="k" :label="v" :value="v"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="状态：" prop="status">
            <el-radio-group v-model="ruleForm.status">
              <el-radio label="1">开启</el-radio>
              <el-radio label="2">禁止</el-radio>
            </el-radio-group>
          </el-form-item>
          <div class="center">
            <el-button size="mini" type="primary" @click="useSubmitForm">提 交</el-button>
            <el-button size="mini" @click="useResetForm">重 置</el-button>
          </div>
        </el-form>
      </aside>
    </fieldset>
  </div>
</template>
<script>
const {ref, reactive, computed, onMounted, watch, onBeforeUnmount} = vue;
const {useRouter, useStore, useCache, useTip, useLoading, useConfirm} = hook;
const {user: userApi, useRequest} = api;
const {useInitTitle} = util;

let initRuleForm = {title: "", select: "", status: "1"}; // 表单初始数据

export default {
  components: {},
  setup(prop, ctx) {
    const {root} = ctx;
    const {title} = useInitTitle(ctx);

    let ruleFormRef = ref(null);
    let formState = ref(false);
    let ruleForm = ref(JSON.parse(JSON.stringify(initRuleForm)));

    const rules = computed(() => {
      return {
        title: [{required: true, message: "请输入标题", trigger: "change"}],
        select: [{required: true, message: "请选择", trigger: "change"}]
      };
    })

    function useSubmitForm() {
      ruleFormRef.value.validate((valid) => {
        if (valid) {
          formState.value = true;
          // 这里请求接口
          setTimeout(function () {
            console.log("useSubmitForm ok");
            formState.value = false;
          }, 3000);
        } else {
          return false;
        }
      });
    }

    function useResetForm() {
      root.$nextTick(() => {
        ruleFormRef.value.clearValidate();
      });
      ruleForm.value = JSON.parse(JSON.stringify(initRuleForm));
    }

    return {
      title,
      formState,
      ruleForm,
      rules,
      useSubmitForm,
      useResetForm,
      ruleFormRef
    };
  }
};
</script>

<style scoped>
</style>
