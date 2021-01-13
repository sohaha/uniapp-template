<style scoped>
.avatar-uploader .el-upload {
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.avatar-uploader .el-upload:hover {
  border-color: #409eff;
}

.avatar-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 120px;
  height: 120px;
  line-height: 120px;
  text-align: center;
}

.avatar {
  width: 120px;
  height: 120px;
  display: block;
}

.el-form-item__content .el-select {
  width: 100%;
}

.el-form-item__content {
  -webkit-box-flex: 1;
  -ms-flex: 1 1 0%;
  flex: 1 1 0%
}
</style>
<template>
  <el-form size="mini" class="user-view" v-loading="formState" :model="ruleForm" :rules="rules" ref="ruleFormRef" label-width="80px">
    <el-form-item label="Email" prop="email">
      <el-input v-model="ruleForm.email"></el-input>
    </el-form-item>
    <el-form-item label="用户名" prop="username">
      <el-input v-model="ruleForm.username" :disabled="hasEdit"></el-input>
    </el-form-item>
    <el-form-item label="用户密码" prop="password" v-if="hasEditPass">
      <el-input :placeholder="passwordPlaceholder" v-model="ruleForm.password" type="password"></el-input>
    </el-form-item>
    <el-form-item label="确定密码" prop="password2" v-if="hasEditPass">
      <el-input v-model="ruleForm.password2" type="password"></el-input>
    </el-form-item>
    <el-form-item label="用户身份" prop="group_id" v-if="hasGroup">
      <el-select v-model="ruleForm.group_id" placeholder="请选择角色" multiple>
        <el-option v-for="(v,k) in groups" :key="k" :label="v.name" :value="v.id"></el-option>
      </el-select>
    </el-form-item>
    <el-form-item label="用户状态" prop="status">
      <el-radio-group v-model="ruleForm.status">
        <el-radio label="1">开启</el-radio>
        <el-radio label="2">禁止</el-radio>
      </el-radio-group>
    </el-form-item>
    <el-form-item label="用户头像" prop="avatar">
      <el-upload class="avatar-uploader" :action="uploadAvatarUrl" :show-file-list="false" :headers="uploadAvatarHeaders" :on-success="handleAvatarSuccess" :before-upload="beforeAvatarUpload">
        <!--        <img v-if="ruleForm.avatar" :src="imgHost + ruleForm.avatar" class="avatar">-->
        <el-image v-if="ruleForm.avatar" fit="fill" class="avatar" :src="ruleForm.avatar||$store.state.defaultData.avatar">
          <div slot="error" class="image-slot">
            <img :src="baseUrl + ruleForm.avatar" alt='用户头像'>
          </div>
        </el-image>
        <i v-else class="el-icon-plus avatar-uploader-icon"></i>
      </el-upload>
    </el-form-item>
    <el-form-item label="用户简介" prop="remark">
      <el-input type="textarea" v-model="ruleForm.remark"></el-input>
    </el-form-item>
    <div class="center">
      <el-button type="primary" @click="submitForm">{{ btnText }}</el-button>
      <el-button @click="resetForm">重 置</el-button>
    </div>
  </el-form>
</template>
<script>
const { useRouter, useStore, useTip } = hook;
const { reactive, toRef, ref, watch, computed, onMounted } = vue;
const { user: userApi, useRequest } = api;

let initRuleForm = {
  id: '',
  username: '',
  password: '',
  password2: '',
  status: '1',
  avatar: '',
  remark: '',
  email: '',
  group_id: []
};

export default {
  name: 'userView',
  props: {
    info: {
      type: Object,
      defaulft: {}
    }
  },
  setup (prop, ctx) {
    const { root } = ctx;
    const ruleFormRef = ref(null);

    let ruleForm = ref({
      id: '',
      username: '',
      password: '',
      password2: '',
      status: '1',
      avatar: '',
      remark: '',
      email: '',
      group_id: []
    });
    let imgHost = ref('');

    let formState = ref(false);

    const baseUrl = computed(() => {
      return config.baseURL;
    });

    const groups = computed(() => {
      return useStore(ctx).getters.groups;
    });

    const passwordPlaceholder = computed(() => {
      return useStore(ctx).getters.isSuper && hasEdit.value ? '留空则不修改密码' : ' ';
    });

    const rules = computed(() => {
      let rules = {
        status: [{ required: true, message: '请选择用户状态', trigger: 'change' }],
        remark: [
          {
            min: 0,
            max: 200,
            message: '用户备注长度不能超过 200 个字符',
            trigger: 'blur'
          }
        ],
        email: [
          { required: true, message: '请输入Email', trigger: 'blur' },
          {
            type: 'email',
            message: '请输入正确的邮箱地址',
            trigger: ['blur', 'change']
          }
        ]
      };
      rules['group_id'] = [{ required: true, message: '请选择用户角色', trigger: 'blur' }];
      if (!hasEdit.value) {
        let validatePass = function (rule, value, callback) {
          if (value !== ruleForm.value.password) {
            callback(new Error('两次输入密码不一致!'));
          } else {
            callback();
          }
        };
        rules['password'] = [
          { required: true, message: '请输入用户密码', trigger: 'blur' }
        ];
        rules['password2'] = [
          { required: true, message: '请再次输入用户密码', trigger: 'blur' },
          { validator: validatePass, trigger: 'blur' }
        ];
        rules['username'] = [
          { required: true, message: '请输入用户名', trigger: 'blur' },
          {
            min: 3,
            max: 25,
            message: '长度在 3 到 25 个字符',
            trigger: 'blur'
          }
        ];
      }
      return rules;
    });

    const uploadAvatarHeaders = computed(() => {
      return { token: useStore(ctx).getters.token };
    });

    const uploadAvatarUrl = computed(() => {
      return userApi.uploadAvatar();
    });

    const hasEdit = computed(() => {
      return !!prop.info.id;
    });

    const hasEditPass = computed(() => {
      // return !hasEdit.value || useStore(ctx).getters.isSuper;
      return useStore(ctx).getters.groupID === 1 || useStore(ctx).getters.isSuper;
    });

    const hasGroup = computed(() => {
      // return !hasEdit.value || useStore(ctx).getters.isSuper;
      return useStore(ctx).getters.groupID === 1 || useStore(ctx).getters.isSuper;
    });

    const btnText = computed(() => {
      return hasEdit.value ? '更 新' : '创 建';
    });

    watch(() => prop.info, (val) => {
      setData();
    }, { immediate: true });

    function setData () {
      root.$nextTick(() => {
        ruleFormRef.value.clearValidate();
      });
      let data = Object.assign({}, prop.info);
      if (data.id) {
        for (let k in data) {
          if (data.hasOwnProperty(k)) {
            if (k === 'status') {
              data[k] = '' + data[k];
            }
          }
        }
      }

      ruleForm.value = JSON.stringify(data) === '{}' ? Object.assign({}, initRuleForm) : Object.assign({}, initRuleForm, data);
    }

    function submitForm () {
      ruleFormRef.value.validate(function (valid) {
        if (valid) {
          formState.value = true;
          let api = hasEdit.value ? userApi.updateUser : userApi.createUser;
          const { loading, error, data, run } = useRequest(api(ruleForm.value));
          watch(data, (val) => {
            if (val.data && val.code < 400) {
              let id;
              if (!hasEdit.value) {
                id = val.data.id;
                resetForm();
              } else {
                id = ruleForm.value.id;
              }
              ctx.emit('submit', id);

              formState.value = false;
            } else {
              useTip().message('warning', val.msg);
              resetForm();
            }
          });
          watch(error, (err) => {
            useTip().message('warning', err);
          });
          watch([data, error], ([d, e]) => {
            formState.value = false;
          });
        } else {
          return false;
        }
      });
    }

    function resetForm () {
      setData();
    }

    function handleAvatarSuccess (e, file) {
      if (e.code === 200) {
        imgHost.value = e.data.host;
        ruleForm.value.avatar = e.data.path;
      } else {
        useTip().notify('warning', e.msg, '温馨提示');
      }
    }

    function beforeAvatarUpload (file) {
      let isType = file.type === 'image/jpeg' || file.type === 'image/png';
      let isLt2M = file.size / 1024 / 1024 < 2;

      if (!isType) {
        useTip().notify('warning', '上传头像图片只能是 JPG、PNG 格式!', '温馨提示');
      } else if (!isLt2M) {
        useTip().notify('warning', '上传头像图片大小不能超过 2MB!', '温馨提示');
      }
      return isType && isLt2M;
    }

    return {
      ruleFormRef,
      ruleForm,
      imgHost,
      baseUrl,
      formState,
      groups,
      passwordPlaceholder,
      rules,
      uploadAvatarHeaders,
      uploadAvatarUrl,
      hasEdit,
      hasEditPass,
      hasGroup,
      btnText,
      setData,
      submitForm,
      resetForm,
      handleAvatarSuccess,
      beforeAvatarUpload
    };
  }
};
</script>
