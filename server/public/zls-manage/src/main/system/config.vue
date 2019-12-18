<style>
  .el-switch + .el-switch {
    margin-left: 10px;
  }

</style>
<template>
  <div>
    <div class="view-title float-clear"></div>
    <fieldset>
      <legend>{{title}}</legend>
      <aside>
        <el-form size="mini" label-position="top" :rules="rules" ref="form" :model="form" label-width="90px">
          <el-form-item label="IP白名单">
            <el-input v-model="form.ipWhitelist" size="mini" placeholder="IP白名单，多个使用;分隔" />
          </el-form-item>
          <el-form-item label="其他开关">
            <el-tooltip content="程序出错时候会直接在页面打印出异常信息，正式环境下建议关闭。" transition="el-zoom-in-bottom" placement="top-start">
              <el-switch v-model="form.debug" active-text="开发模式" />
            </el-tooltip>
            <el-tooltip content="开启维护模式之后除了白名单内Ip，其他用户无法访问。" transition="el-zoom-in-bottom" placement="top-start">
              <el-switch @change='changeMaintainMode' v-model="form.maintainMode" active-text="维护模式" />
            </el-tooltip>
          </el-form-item>
          <el-form-item>
            <el-button size="mini" type="primary" @click="submit">提 交</el-button>
          </el-form-item>
        </el-form>
      </aside>
    </fieldset>
  </div>
</template>
<script>
  var form = { ipWhitelist: '', maintainMode: null, debug: false };
  Spa.define(
    {
      mixins: [mixinLists, initTitle],
      data: function () {
        return {
          form: Object.assign({}, form),
          rules: {}
        };
      },
      beforeCreate: function () {
      },
      mounted: function () {
        this.getConfig();
      },
      computed: {},
      watch: {
        'form.maintainMode': {
          handler (val, oldVal) {},
          deep: true
        }
      },
      init: function (query, search) {},
      methods: {
        changeMaintainMode: function (val) {
          if (val === true) {
            app.$confirm('是否真的要开启维护模式', '警告', function (e) {
              if (e !== 'confirm') {
                $this.form.maintainMode = false;
              }
            }, { type: 'warning', });
          }
        },
        getConfig: function () {
          $this.$api('sysGetSystemConfig').then(function (e) {
            $this.form = Object.assign({}, form, e.data);
          });
        },
        submit: function () {
          $this.$refs['form'].validate(function (valid) {
            if (valid) {
              $this
              .$api('sysSetSystemConfig', $this.form)
              .then(function (e) {
                console.log(e);
                $this.$tipMsg('更新成功');
              })
              .catch(function (err) {
                $this.$warMsg(err);
              });
            } else {
              return false;
            }
          });
        }
      }
    },
    [],
    '/index'
  );
</script>
