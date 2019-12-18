<template>
  <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="80px">
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
      <el-button type="primary" @click="submitForm">修 改</el-button>
      <el-button @click="resetForm">放 弃</el-button>
    </div>
  </el-form>
</template>
<script>
  var ruleForm = {
    pass: '',
    pass2: '',
    oldPass: '',
  };
  Spa.define({
    data: function () {
      return {
        ruleForm: Object.assign({}, ruleForm),
      };
    },
    props: {},
    computed: {
      userid: function () {
        return this.$store.getters.userid;
      },
      rules: function () {
        var validatePass = function (rule, value, callback) {
          if (value !== $this.ruleForm.pass) {
            callback(new Error('两次输入密码不一致!'));
          } else {
            callback();
          }
        };
        return {
          oldPass: [
            { required: true, message: '请输入旧密码', trigger: 'blur' },
          ],
          pass: [
            { required: true, message: '请输入新密码', trigger: 'blur' },
            // { min: 0, max: 200, message: '密码长度不对', trigger: 'blur' },
          ],
          pass2: [
            { required: true, message: '请再次输入密码', trigger: 'blur' },
            { validator: validatePass, trigger: 'blur' },
          ],
        };
      },
    },
    watch: {},
    methods: {
      submitForm: function () {
        var that = this;
        that.$refs['ruleForm'].validate(function (valid) {
          if (valid) {
            that.$api(apis.sysEditPassword, Object.assign({ userid: that.userid }, that.ruleForm))
                .then(function (e) {
                  that.resetForm();
                  that.$emit('success', e.data);
                  that.$sucMsg('密码修改成功');
                })
                .catch(function (msg) {
                  that.$warMsg(msg);
                });
          } else {
            return false;
          }
        });
      },
      resetForm: function () {
        var that = this;
        that.ruleForm = Object.assign({}, ruleForm);
        that.$nextTick(function () {
          that.$refs['ruleForm'].clearValidate();
          that.$emit('success');
        });
      },
    },
  });
</script>
