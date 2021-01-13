<template>
  <div>
    <div class="view-title float-clear"></div>
    <fieldset>
      <legend>{{ title }}</legend>
      <aside>
        <el-form size="mini" label-position="top" :rules="rules" ref="formRef" :model="form" label-width="90px">
          <el-form-item label="IP白名单">
            <el-input v-model="form.ipWhitelist" size="mini" placeholder="IP白名单，多个使用;分隔"/>
          </el-form-item>
          <el-form-item label="其他开关">
            <el-tooltip content="程序出错时候会直接在页面打印出异常信息，正式环境下建议关闭。" transition="el-zoom-in-bottom" placement="top-start">
              <el-switch v-model="form.debug" active-text="开发模式"/>
            </el-tooltip>
            <el-tooltip content="开启维护模式之后除了白名单内Ip，其他用户无法访问。" transition="el-zoom-in-bottom" placement="top-start">
              <el-switch @change='useChangeMaintainMode' v-model="form.maintainMode" active-text="维护模式"/>
            </el-tooltip>
          </el-form-item>
          <el-form-item label="登录模式">
            <el-tooltip content="开启单用户登录后一个账号不能同时登录多个页面(除了当前管理员其他用户只保留最后一次登录信息)" transition="el-zoom-in-bottom"
                        placement="top-start">
              <el-switch v-model="form.loginMode" active-text="单用户登录"/><!--multi-user login多用户登录-->
            </el-tooltip>
          </el-form-item>
          <el-form-item>
            <el-button size="mini" type="primary" @click="useSubmit">提 交</el-button>
          </el-form-item>
        </el-form>
      </aside>
    </fieldset>
  </div>
</template>
<script>

const {ref, reactive, computed, onMounted, watch, onBeforeUnmount} = vue;
const {useRouter, useStore, useCache, useTip, useLoading, useConfirm} = hook;
const {user: userApi, useRequestWith} = api;
const {useInitTitle} = util;

let initForm = {ipWhitelist: '', maintainMode: null, debug: false};

export default {
  components: {},
  setup(prop, ctx) {
    const {title} = useInitTitle(ctx);

    onMounted(() => {
      useGetConfig();
    })

    let formRef = ref(null);
    let form = ref(Object.assign({}, initForm));
    let rules = ref({});

    function useChangeMaintainMode(val) {
      if (val === true) {
        useConfirm().warning('警告', '是否真的要开启维护模式', '', function () {
          form.value.maintainMode = false;
        });
      }
    }

    const getConfig = useRequestWith(userApi.getSystemConfig, {manual: true});

    async function useGetConfig() {
      const [data, err] = await getConfig.run();
      if (err) {
        useTip().message('warning', err);
      } else {
        form.value = Object.assign({}, initForm, data);
      }
    }

    const submit = useRequestWith(userApi.setSystemConfig, {manual: true});

    function useSubmit() {
      formRef.value.validate(async (valid) => {
        if (valid) {
          const [, err] = await submit.run(form.value);
          if (err) {
            useTip().message('warning', err);
          } else {
            useTip().message('success', '更新成功');
          }
        } else {
          return false;
        }
      })
    }

    return {
      title,
      rules,
      form,
      formRef,
      useChangeMaintainMode,
      useSubmit
    };
  }
};
</script>

<style scoped>
.el-switch + .el-switch {
  margin-left: 10px;
}
</style>
