<template>
  <div>
    <div class="view-title float-clear">
      <div class="view-title-right">
        <el-button type="primary" size="mini" @click="useAddForm">添加文本框</el-button>
      </div>
    </div>
    <fieldset>
      <legend>{{ title }}</legend>
      <aside :aria-label="title">
        <form-create class="form-create-mini" v-model="formApi" :rule="formRule" :option="formOption"></form-create>
      </aside>
    </fieldset>
    <div class="panel">
      更多自定义配置请参考 <a href="http://form-create.com/v2/element-ui/global.html#row" target="_blank">form-create 文档</a>。
    </div>
  </div>
</template>
<script>
// 优先载入需要 js
export default load({js: [VueRun.lib('/form-create/form-create.js')]}).then(async () => {
  const {ref, reactive, computed, onMounted, watch, onBeforeUnmount} = vue;
  const {useRouter, useStore, useCache, useTip, useLoading, useConfirm} = hook;
  const {user: userApi, useRequest} = api;
  const {useInitTitle} = util;

  let initRuleForm = {title: "", select: "", status: "1"}; // 表单初始数据

  return {
    name: 'demoForm2',
    components: {},
    setup(prop, ctx) {
      const {title} = useInitTitle(ctx);

      let formApi = ref({});
      let formRule = ref([
        {
          type: 'input',
          field: 'text',
          title: '文本：',
          value: '',
          info: '一个文本',
          props: {
            prefixIcon: 'el-icon-edit',
            placeholder: '文本...',
            autofocus: true,
            type: 'password',
            showPassword: true,
            clearable: true
          },
          children: [],
          validate: [
            {required: true, message: '请输入文本', trigger: 'input'}
          ]
        },
        {
          type: 'datePicker',
          field: 'created_at',
          title: '时间：',
          props: {
            placeholder: '时间...'
          }
        },
        {
          type: 'select',
          field: 'cid',
          title: '分类：',
          value: ['104', '105'],
          props: {
            placeholder: '分类...',
            // multiple: true
          },
          options: [
            {value: '104', label: '生态', disabled: false},
            {value: '105', label: '天然', disabled: false}
          ],
          validate: [{required: true, message: '请选择', trigger: 'blur'}],
        },
        {
          type: 'switch',
          title: '开启：',
          field: 'status',
          className: 'className',
          value: 1,
          props: {
            activeValue: 1,
            inactiveValue: 0
          }
        },
        {
          type: 'radio',
          title: '类型：',
          field: 'is_postage',
          value: '0',
          options: [
            {value: '0', label: '开发', disabled: false},
            {value: '1', label: '测试', disabled: false},
          ],
        }
      ]);
      let formOption = ref({
        info: {type: 'popover'},
        form: {
          className: 'hi',
          hideRequiredAsterisk: true,
          labelWidth: '100px',
          showMessage: true,
          size: 'mini'
        },
        submitBtn: {
          size: 'mini',
          loading: false,
          disabled: false,
          icon: '',
          innerText: '提 交',
          width: 'auto'
        },
        resetBtn: {
          icon: '',
          innerText: '重 置',
          size: 'mini',
          width: 'auto',
          show: true
        },
        onSubmit: (formData) => {
          // 这里请求接口
          console.log(JSON.stringify(formData));
        }
      });

      function useAddForm() {
        formRule.value.push({
          type: 'input',
          field: 'text' + +new Date(),
          title: '文本：',
          props: {placeholder: '文本...'},
          validate: [{required: true, message: '请输入文本', trigger: 'blur'}]
        });
      }

      return {
        title,
        useAddForm,
        formRule,
        formOption,
        formApi
      };
    }
  };
});
</script>

<style scoped>
</style>
