<template>
  <div class="page-sys-logs">
    <div class="view-title float-clear no">
      <span>{{ title }}</span>
      <div class="view-title-right">
        <el-form @submit.prevent.stop.native inline class="tip-top">
          <el-form-item>
            <el-select clearable size="mini" v-model="logType" placeholder="请选择状态">
              <el-option
                  v-for="item in logTypes"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="info" size="mini" @click="listChange" icon="el-icon-refresh">刷新</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <el-tabs v-model="activeName" @tab-click="handleClick">
      <el-tab-pane :key="k" v-for="(v,k) in tabs" :label="v" :name="k"></el-tab-pane>
    </el-tabs>
    <fieldset>
      <legend>{{ tabTitle }}</legend>
      <div v-loading="listLoading">
        <el-table
            empty-text="暂无对应消息"
            @selection-change="handleSelectionChange"
            :data="listData"
            style="width: 100%"
            size="mini"
        >
          <el-table-column :selectable="isUnreadMessageTab" type="selection" width="55"></el-table-column>
          <el-table-column prop="create_time" label="日期" width="150"></el-table-column>
          <el-table-column show-overflow-tooltip prop="username" label="触发用户" width="100"></el-table-column>
          <el-table-column label="日志" min-width="220">
            <template slot-scope="scope">
              <div class="text-nowrap" :title="scope.row.content">{{ scope.row.content }}</div>
            </template>
          </el-table-column>
          <el-table-column label="类型">
            <template slot-scope="scope">
              <el-tag
                  size="mini"
                  v-bind="getTagAttrs(scope.row.type)"
                  v-text="getTagAttrs(scope.row.type).title"
              ></el-tag>
            </template>
          </el-table-column>
          <el-table-column min-width="100" label="操作">
            <template slot-scope="scope">
              <div class="text-nowrap">
                <el-button
                    type="primary"
                    v-if="isUnreadMessageTab(scope.row)"
                    title="标记已读"
                    size="mini"
                    @click="useReadSelection(scope.row.id)"
                >标记已读
                </el-button>
                <span v-else>--</span>
              </div>
            </template>
          </el-table-column>
        </el-table>
        <div class="tip-page" v-if="!!listPages.total">
          <div class="panel-left" v-show="showColumnBtn">
            <el-button
                @click="useReadSelection()"
                size="mini"
                type="primary"
                icon="icon-inbox"
                title="标记已读"
            ></el-button>
          </div>
          <el-pagination
              :current-page.sync="listPages.curpage"
              @size-change="listChange"
              @current-change="listChange"
              background
              layout="prev, pager, next, sizes, total"
              :total="listPages.total"
              :page-size.sync="listPages.pagesize">
          </el-pagination>
        </div>
      </div>
    </fieldset>
  </div>
</template>
<script>
const {ref, reactive, computed, onMounted, watch} = vue;
const {useRouter, useStore, useCache, useTip, useLoading} = hook;
const {user: userApi, useRequestWith, useRequestPage} = api;
const {useInitTitle} = util;
export default {
  components: {},
  setup(prop, ctx) {
    const {root} = ctx;
    const {title} = useInitTitle(ctx);

    let activeName = ref("unreadMessage");
    let logType = ref("");
    let searchKey = ref({unread: +(activeName.value === "unreadMessage"), type: logType.value});
    const lists = useRequestPage(userApi.sysUserLogs, {key: searchKey}, {
      dataHandle(e) {
        e.items = e.items.map((e) => {
          e.username = !!e.username ? e.username : "游客";
          return e;
        })

        return e;
      }
    })
    let lastPage = 0;
    watch(lists.loading, (l) => {
      if (!l) {
        if (lists.error.value) {
          useTip().message('error', lists.error.value);
          lists.error.value = null;
          lists.pages.value.curpage = lastPage;
          return;
        }
        lastPage = lists.pages.value.curpage;
      }
    });
    const listRes = {
      listData: lists.items,
      listLoading: lists.loading,
      listPages: lists.pages,
      listChange: lists.change,
    };

    function useGetLists() {
      searchKey.value = {unread: +(activeName.value === "unreadMessage"), type: logType.value};
      lists.change();
    }

    const tabs = reactive({
      unreadMessage: "未读消息",
      allMessage: "全部消息"
    });

    const logTypes = reactive([
      {label: "普通日志", value: 1, type: ""},
      {label: "警告日志", value: 2, type: "warning"},
      {label: "错误日志", value: 3, type: "danger"}
    ]);

    watch(logType, (val) => {
      useGetLists();
    })

    const tabTitle = computed(() => {
      return tabs[activeName.value];
    })

    let selectIds = ref([]);
    const showColumnBtn = computed(() => {
      return selectIds.value.length > 0;
    })

    function getTagAttrs(i) {
      for (let j = 0, length = logTypes.length; j < length; j++) {
        if (logTypes[j]["value"] === i) {
          return {
            title: logTypes[j].label,
            type: logTypes[j].type,
          };
        }
      }
      return {};
    }

    const readSelection = useRequestWith(userApi.updateMessageStatus, {manual: true});

    async function useReadSelection(e) {
      let paramData = {ids: []};
      if (!e) {
        paramData.ids = selectIds.value;
      } else {
        paramData.ids = [e];
      }

      const [, err] = await readSelection.run(paramData);
      if (err) {
        useTip().message('warning', err);
      } else {
        window["SysGetUnreadMessageCount"] &&
        window["SysGetUnreadMessageCount"]();
        useGetLists();
      }
    }

    function handleSelectionChange(e) {
      selectIds.value = e.map((e) => {
        return e.id;
      })
    }

    function isUnreadMessageTab(row) {
      return row.status === 1;
    }

    function handleClick(tab, event) {
      root.$nextTick(() => {
        useGetLists();
      })
    }

    return {
      title,
      ...listRes,
      logType,
      logTypes,
      activeName,
      tabs,
      tabTitle,
      showColumnBtn,
      handleClick,
      handleSelectionChange,
      isUnreadMessageTab,
      getTagAttrs,
      useReadSelection
    };
  }
};
</script>

<style scoped>
.page-sys-logs .el-form-item {
  max-width: 120px;
}
</style>
