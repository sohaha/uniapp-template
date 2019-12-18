<style>
</style>
<template>
  <div>
    <div class="view-title float-clear">
      <!--占位-->
    </div>
    <fieldset>
      <legend>{{title}}</legend>
      <aside :aria-label="title">
        <el-form
          size="mini"
          class="form-vertical"
          v-loading="formState"
          :model="ruleForm"
          :rules="rules"
          ref="ruleForm"
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
            <el-button size="mini" type="primary" @click="submitForm">提 交</el-button>
            <el-button size="mini" @click="resetForm">重 置</el-button>
          </div>
        </el-form>
      </aside>
    </fieldset>
  </div>
</template>
<script>
var ruleForm = { title: "", select: "", status: "1" }; // 表单初始数据
Spa.define(
  {
    mixins: [mixinLists, initTitle],
    data: function() {
      return {
        formState: false,
        ruleForm: JSON.parse(JSON.stringify(ruleForm))
      };
    },
    mounted: function() {},
    computed: {
      rules: function() {
        var rules = {
          title: [{ required: true, message: "请输入标题", trigger: "change" }],
          select: [{ required: true, message: "请选择", trigger: "change" }]
        };
        return rules;
      }
    },
    init: function(query, search) {},
    methods: {
      submitForm: function() {
        this.$refs["ruleForm"].validate(function(valid) {
          if (valid) {
            $this.formState = true;
            // 这里请求接口
            setTimeout(function() {
              console.log("submitForm ok");
              $this.formState = false;
            }, 3000);
          } else {
            return false;
          }
        });
      },
      resetForm: function() {
        this.$nextTick(function() {
          $this.$refs["ruleForm"].clearValidate();
        });
        this.ruleForm = JSON.parse(JSON.stringify(ruleForm));
      }
    }
  },
  [],
  "/index"
);
</script>
