<style>
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

  .user-view .el-form-item__content .el-select {
    width: 100%;
  }

</style>
<template>
  <el-form size="mini" class="user-view" v-loading="formState" :model="ruleForm" :rules="rules" ref="ruleForm" label-width="80px">
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
      <el-select v-model="ruleForm.group_id" placeholder="请选择角色">
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
        <img v-if="ruleForm.avatar" :src="ruleForm.avatar" class="avatar"> <i v-else class="el-icon-plus avatar-uploader-icon"></i>
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
  var ruleForm = {
      id: '',
      username: '',
      password: '',
      password2: '',
      status: '1',
      avatar: '',
      remark: '',
      email: '',
      group_id: ''
    },
    that;
  Spa.define({
    data: function () {
      return {
        ruleForm: JSON.parse(JSON.stringify(ruleForm)),
        formState: false
      };
    },
    props: {
      info: {
        type: Object,
        defaulft: {}
      }
    },
    created: function () {
      that = this;
    },
    computed: {
      groups: function () {
        return this.$store.getters.groups;
      },
      passwordPlaceholder: function () {
        return this.$store.getters.isSuper && this.hasEdit
          ? '留空则不修改密码'
          : ' ';
      },
      rules: function () {
        var rules = {
          status: [
            { required: true, message: '请选择用户状态', trigger: 'change' }
          ],
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
        rules['group_id'] = [
          { required: true, message: '请选择用户角色', trigger: 'blur' },
        ];
        if (!this.hasEdit) {
          var validatePass = function (rule, value, callback) {
            if (value !== that.ruleForm.password) {
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
      },
      uploadAvatarHeaders: function () {
        return { token: this.$store.getters.token };
      },
      uploadAvatarUrl: function () {
        return this.$api('sysUploadAvatar');
      },
      hasEdit: function () {
        return !!this.info.id;
      },
      hasEditPass: function () {
        return !this.hasEdit || this.$store.getters.isSuper;
      },
      hasGroup: function () {
        return !this.hasEdit || this.$store.getters.isSuper;
      },
      btnText: function () {
        return this.hasEdit ? '更 新' : '创 建';
      }
    },
    watch: {
      info: {
        handler: function (val, oldVal) {
          this.setData();
        },
        immediate: true
      }
    },
    init: function () {},
    methods: {
      setData: function () {
        this.$nextTick(function () {
          that.$refs['ruleForm'].clearValidate();
        });
        var data = Object.assign({}, this.info);
        if (data.id) {
          for (var k in data) {
            if (data.hasOwnProperty(k)) {
              if (k === 'status') {
                data[k] = '' + data[k];
              }
            }
          }
        }
        this.ruleForm =
          JSON.stringify(data) === '{}'
            ? Object.assign({}, ruleForm)
            : Object.assign({}, ruleForm, data);
      },
      submitForm: function () {
        this.$refs['ruleForm'].validate(function (valid) {
          var api = that.hasEdit
            ? that.$api(apis.sysUpdateUser, that.ruleForm)
            : that.$api(apis.sysCreateUser, that.ruleForm);
          if (valid) {
            that.formState = true;
            api
            .then(function (e) {
              var id;
              if (!that.hasEdit) {
                id = e.data.id;
                that.resetForm();
              } else {
                id = that.ruleForm.id;
              }
              that.$emit('submit', id);
            })
            .catch(function (msg) {
              that.$warMsg(msg);
              that.resetForm();
            })
            .finally(function () {
              that.formState = false;
            });
          } else {
            return false;
          }
        });
      },
      resetForm: function () {
        // this.$refs[formName].resetFields();
        this.setData();
      },
      handleAvatarSuccess: function (e, file) {
        if (e.code === 200) {
          this.$set(this.ruleForm, 'avatar', e.data.path);
        } else {
          this.$errMsg(e.msg);
        }
      },
      beforeAvatarUpload: function (file) {
        var isType = file.type === 'image/jpeg' || file.type === 'image/png';
        var isLt2M = file.size / 1024 / 1024 < 2;

        if (!isType) {
          this.$errMsg('上传头像图片只能是 JPG、PNG 格式!');
        } else if (!isLt2M) {
          this.$errMsg('上传头像图片大小不能超过 2MB!');
        }
        return isType && isLt2M;
      }
    }
  });
</script>
