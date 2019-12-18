<style>
.page-sys-logs .el-form-item {
  max-width : 120px;
}

</style>
<template>
  <div class="page-sys-logs">
    <div class="view-title float-clear no">
      <span>{{title}}</span>
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
            <el-button type="info" size="mini" @click="ml_reloadLists" icon="el-icon-refresh">刷新</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <el-tabs v-model="activeName" @tab-click="handleClick">
      <el-tab-pane :key="k" v-for="(v,k) in tabs" :label="v" :name="k"></el-tab-pane>
    </el-tabs>
    <fieldset>
      <legend>{{tabTitle}}</legend>
      <div v-loading="ml_listsLoading">
        <el-table
          empty-text="暂无对应消息"
          @selection-change="handleSelectionChange"
          :data="ml_data"
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
                  @click="readSelection(scope.row.id)"
                >标记已读</el-button>
                <span v-else>--</span>
              </div>
            </template>
          </el-table-column>
        </el-table>
        <div class="tip-page" v-if="!!ml_pagetotal">
          <div class="panel-left" v-show="showColumnBtn">
            <el-button
              @click="readSelection()"
              size="mini"
              type="primary"
              icon="icon-inbox"
              title="标记已读"
            ></el-button>
          </div>
          <el-pagination
            :current-page.sync="ml_page"
            @size-change="ml_sizeChange"
            @current-change="ml_currentChange"
            background
            layout="prev, pager, next, sizes"
            :total="ml_pagetotal"
            :page-size.sync="ml_pagesize"
          ></el-pagination>
        </div>
      </div>
    </fieldset>
  </div>
</template>
<script>
Spa.define(
  {
    mixins: [mixinLists, initTitle],
    data: function() {
      return {
        activeName: "unreadMessage",
        tabs: {
          unreadMessage: "未读消息",
          allMessage: "全部消息"
        },
        logType: "",
        logTypes: [
          { label: "普通日志", value: 1, type: "" },
          { label: "警告日志", value: 2, type: "warning" },
          { label: "错误日志", value: 3, type: "danger" }
        ],
        selectIds: []
      };
    },
    watch: {
      logType: function() {
        this.ml_reloadLists();
      }
    },
    computed: {
      tabTitle: function() {
        return this.tabs[this["activeName"]];
      },
      showColumnBtn: function() {
        return this.selectIds.length > 0;
      }
    },
    init: function(query, search) {
      this.ml_reloadLists();
    },
    methods: {
      getTagAttrs: function(i) {
        for (let j = 0, length = this.logTypes.length; j < length; j++) {
          if (this.logTypes[j]["value"] === i) {
            return {
              title: this.logTypes[j].label,
              type: this.logTypes[j].type
            };
          }
        }
        return {};
      },
      readSelection: function(e) {
        var data = { ids: [] };
        if (!e) {
          data.ids = $this.selectIds;
        } else {
          data.ids = [e];
        }
        this.$api(apis.sysUpdateMessageStatus, data)
          .then(function(e) {
            window["SysGetUnreadMessageCount"] &&
              window["SysGetUnreadMessageCount"]();
            $this.ml_reloadLists();
          })
          .catch(function(e) {
            $this.$warMsg(e);
          });
      },
      handleSelectionChange: function(e) {
        this.selectIds = e.map(function(e) {
          return e.id;
        });
      },
      isUnreadMessageTab: function(row) {
        return row.status === 1;
      },
      handleClick: function(tab, event) {
        this.ml_page = 1;
        this.$nextTick(function() {
          $this.getLists();
        });
      },
      getLists: function() {
        if (this.ml_listsLoading) {
          return;
        }
        var $this = this,
          data = {
            page: this.ml_page,
            pagesize: this.ml_pagesize,
            unread: +(this.activeName === "unreadMessage"),
            type: this.logType
          };
        this.ml_listsLoading = true;
        this.$api(apis.sysUserLogs, data)
          .then(function(e) {
            var items = e.data.items.map(function(v) {
              v.username = !!v.username ? v.username : "游客";
              return v;
            });
            $this.ml_getLists(items, e.data.page);
          })
          .catch(function(e) {
            $this.$warMsg(e);
          })
          .finally(function() {
            // $this.$store.commit('setUnreadMessageCount', 0);
            $this.ml_listsLoading = false;
          });
      }
    }
  },
  [],
  "/index"
);
</script>
