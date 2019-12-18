<style></style>
<template>
  <div>
    <div class="view-title float-clear">
      <div class="view-title-right">
        <el-button type="primary" size="mini" @click="addForm">添加文本框</el-button>
      </div>
    </div>
    <fieldset>
      <legend>{{title}}</legend>
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
  var loadJs = [
      '//cdn.jsdelivr.net/npm/zls-manage/form-create/form-create.js' // 异步引入
    ],
    ruleForm = { title: '', select: '', status: '1' }; // 表单初始数据
  Spa.define(
    {
      mixins: [initTitle],
      data: function () {
        return {
          formApi: {},
          formRule: [
            // {
            //   type: 'div',
            //   children: ['自定义标签']
            // },
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
                { required: true, message: '请输入文本', trigger: 'input' }
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
                { value: '104', label: '生态', disabled: false },
                { value: '105', label: '天然', disabled: false }
              ],
              validate: [{ required: true, message: '请选择', trigger: 'blur' }],
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
            }, {
              type: 'radio',
              title: '类型：',
              field: 'is_postage',
              value: '0',
              options: [
                { value: '0', label: '开发', disabled: false },
                { value: '1', label: '测试', disabled: false },
              ],
            }
          ],
          formOption: {
            info: { type: 'popover' },
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
            onSubmit: function (formData) {
              // 这里请求接口
              console.log(JSON.stringify(formData));
            }
          }
        };
      },
      mounted: function () {
      },
      computed: {},
      init: function (query, search) {
      },
      methods: {
        addForm: function () {
          $this.formRule.push({
            type: 'input',
            field: 'text',
            title: '文本：',
            props: { placeholder: '文本...' },
            validate: [{ required: true, message: '请输入文本', trigger: 'blur' }]
          });
        }
      }
    },
    [],
    '/index'
  );
</script>
