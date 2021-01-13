<template>
  <div class="logs-view">
    <div class="view-title float-clear">
      <!-- <span v-once>{{title}}</span> -->
      <div class="view-title-right">
        <el-form @submit.prevent.stop.native inline class="tip-top">
          <el-form-item>
            <el-select size="mini" v-model="currentType" placeholder="请选择类型">
              <el-option v-for="item in types" :key="item" :label="item" :value="item"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-select :disabled="ban" size="mini" v-model="current" placeholder="请选择日志文件">
              <el-option v-for="item in lists" :key="item" :label="item" :value="item"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label>
            <div class="btns-operating">
              <el-button type="info" size="mini" @click="useReloadDate" icon="el-icon-refresh">刷 新</el-button>
              <el-popover placement="top" width="160" v-model="stateTip">
                <p>
                  确定删除吗？
                  <br>（操作无法逆转）
                </p>
                <div>
                  <el-button size="mini" @click="stateTip = false" type="info" plain>取 消</el-button>
                  <el-button @click="useDeleteLog" type="danger" size="mini" plain>确 定</el-button>
                </div>
                <el-button
                    :disabled="stateDel"
                    slot="reference"
                    size="mini"
                    type="danger"
                    icon="el-icon-delete"
                    title="删 除"
                >删 除
                </el-button>
              </el-popover>
            </div>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <fieldset>
      <legend v-show="logName">{{ logName }}</legend>
      <aside>
        <div class="switch">
          <el-switch v-model="autoLoad" active-text="自动刷新"/>
        </div>
        <el-input type="textarea" ref="textareaRef" :placeholder="current?'':'请先选择日志类型与日志文件'" readonly
                  :autosize="{ minRows: 20, maxRows: 30}" v-model="content"/>
      </aside>
    </fieldset>
  </div>
</template>
<script>
const {ref, reactive, computed, onMounted, watch, onBeforeUnmount} = vue;
const {useRouter, useStore, useCache, useTip, useLoading} = hook;
const {user: userApi, useRequest, useRequestWith} = api;
const {useInitTitle} = util;

let timer;

export default {
  components: {},
  setup(prop, ctx) {
    const {root} = ctx;
    const {title} = useInitTitle(ctx);

    onMounted(() => {
      useGetDate();
    })

    onBeforeUnmount(() => {
      clearTimeout(timer);
    })

    const getDate = useRequestWith(userApi.logs, {manual: true});

    async function useGetDate() {
      const [data, err] = await getDate.run({
        name: current.value,
        type: currentType.value,
        currentLine: currentLine.value,
      });
      if (err) {
        useTip().message('warning', err);
      } else {
        lists.value = data.lists;
        if (currentLine.value) {
          content.value = content.value + data.content;
        } else {
          content.value = data.content;
        }
        types.value = data.types;
        currentLine.value = data.currentLine;
        useTimeout();
      }

      root.$nextTick(() => {
        if (textareaRef.value) {
          let textareaDom = textareaRef.value.$el.querySelector('textarea');
          textareaDom.scrollTop = scrollTop.scrollHeight;
        }
      })
    }

    let textareaRef = ref(null);
    let lists = ref([]);
    let types = ref([]);
    let currentType = ref(null);
    let current = ref(null);
    let content = ref('');
    let stateTip = ref(false);
    let ban = ref(true);
    let autoLoad = ref(true);
    let currentLine = ref(0);

    const logName = computed(() => {
      return currentType.value ? currentType.value + " - 日志查看" : "日志查看";
    })

    const stateDel = computed(() => {
      return !current.value;
    })

    watch(current, (val) => {
      if (val) {
        current.value = val;
        currentLine.value = 0;
        useGetDate();
      }
    })

    watch(currentType, (val, prevVal) => {
      if (val !== prevVal) {
        current.value = '';
        content.value = '';
        lists.value = [];
        stateTip.value = false;
        useGetDate();
      }
      if (ban.value && val) {
        ban.value = false;
      }
    })

    watch(autoLoad, () => {
      useTimeout();
    })

    function useTimeout() {
      clearTimeout(timer);
      if (autoLoad.value && current.value) {
        timer = setTimeout(() => {
          useReloadDate();
        }, 2000)

      }
    }

    function useReloadDate() {
      useGetDate();
    }

    const deleteLog = useRequestWith(userApi.logsDelete, {manual: true});

    async function useDeleteLog() {
      const [, err] = await deleteLog.run({
        name: current.value,
        type: currentType.value
      });
      if (err) {
        useTip().message('warning', err);
      } else {
        current.value = '';
        content.value = '';
        useReloadDate();
      }

      setTimeout(() => {
        stateTip.value = false;
      }, 100)
    }

    return {
      title,
      currentType,
      types,
      ban,
      current,
      lists,
      useReloadDate,
      stateTip,
      useDeleteLog,
      stateDel,
      logName,
      autoLoad,
      content
    };
  }
};
</script>

<style scoped>
.logs-view .panel {
  padding-top: 10px;
}

.logs-view .switch {
  margin-bottom: 10px;
  float: right;
}

.logs-view .el-textarea__inner {
  padding: 10px;
}
</style>
