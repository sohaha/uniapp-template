<style>
</style>
<template>
  <el-form :model="ruleForm" :rules="rules" ref="ruleFormRef" label-width="80px">
    <el-form-item label="原密码" prop="oldPass">
      <el-input v-model="ruleForm.oldPass" type='password'></el-input>
    </el-form-item>
    <el-form-item label="新密码" prop="pass">
      <el-input v-model="ruleForm.pass" type='password'></el-input>
    </el-form-item>
    <el-form-item label="确定密码" prop="pass2">
      <el-input v-model="ruleForm.pass2" type='password'></el-input>
    </el-form-item>
    <div class='center'>
      <el-button type="primary" @click="useSubmitForm">修 改</el-button>
      <el-button @click="resetForm">放 弃</el-button>
    </div>
  </el-form>
</template>
<script>
const {useRouter, useStore, useTip} = hook;
const {reactive, toRef, ref, watch, computed, onMounted} = vue;
const {user: userApi, useRequestWith} = api;

let initRuleForm = {
  pass: '',
  pass2: '',
  oldPass: '',
};

export default {
  name: 'editPassword',
  props: {},
  setup(prop, ctx) {
    const {root} = ctx;
    const ruleFormRef = ref(null);
    let ruleForm = ref(Object.assign({}, initRuleForm))

    const userid = computed(() => {
      return useStore(ctx).getters.userid;
    })

    const rules = computed(() => {
      let validatePass = (rule, value, callback) => {
        if (value !== ruleForm.value.pass) {
          callback(new Error('两次输入密码不一致!'));
        } else {
          callback();
        }
      };
      return {
        oldPass: [
          {required: true, message: '请输入旧密码', trigger: 'blur'},
        ],
        pass: [
          {required: true, message: '请输入新密码', trigger: 'blur'},
          // { min: 0, max: 200, message: '密码长度不对', trigger: 'blur' },
        ],
        pass2: [
          {required: true, message: '请再次输入密码', trigger: 'blur'},
          {validator: validatePass, trigger: 'blur'},
        ]
      }
    })

    const submitForm = useRequestWith(userApi.editPassword, {manual: true});

    function useSubmitForm() {
      ruleFormRef.value.validate(async (valid) => {
        if (valid) {
          const [data, err] = await submitForm.run(Object.assign({userid: userid.value}, ruleForm.value));
          if (err) {
            useTip().message('warning', err);
          } else {
            resetForm();
            ctx.emit('success', data);
            useTip().message('success', '密码修改成功');
          }
        } else {
          return false;
        }
      })
    }

    function resetForm() {
      ruleForm.value = Object.assign({}, initRuleForm);
      root.$nextTick(() => {
        ruleFormRef.value.clearValidate();
        ctx.emit('success');
      })
    }


    return {
      ruleFormRef,
      ruleForm,
      userid,
      rules,
      useSubmitForm,
      resetForm
    };
  }
};
</script>
