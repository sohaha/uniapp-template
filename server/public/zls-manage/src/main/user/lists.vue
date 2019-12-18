<style>
  span.el-tag.el-tag--mini + span.el-tag.el-tag--mini {
    margin-left: 5px;
  }
</style>
<template>
  <div>
    <div class="view-title float-clear">
      <span>{{title}}</span>
      <div class="view-title-right float-clear">
        <el-form @submit.prevent.stop.native inline class="tip-top">
          <el-form-item>
            <el-input clearable @keyup.enter.stop.prevent.native="ml_searchRow" v-model="ml_searchKey" placeholder="用户昵称" size="mini">
              <el-button @click="ml_searchRow" type="success" slot="append" size="mini" icon="el-icon-search"></el-button>
            </el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" size="mini" @click="create" icon="el-icon-plus">添加</el-button>
            <el-button type="info" size="mini" @click="ml_reloadLists" icon="el-icon-refresh">刷新</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <fieldset>
      <legend>{{title}}</legend>
      <aside v-loading="ml_listsLoading">
        <el-table :data="ml_data" style="width: 100%" size="mini">
          <el-table-column label="头像" width="80">
            <template slot-scope="scope">
              <el-tooltip placement="right-end" effect="light" :visible-arrow="false">
                <div slot="content" class="list-avatar-tooltip">
                  <img :src="scope.row.avatar||$store.state.defaultData.avatar" alt='用户头像'>
                </div>
                <el-image fit="cover" class="list-avatar" :src="scope.row.avatar||$store.state.defaultData.avatar"></el-image>
              </el-tooltip>
            </template>
          </el-table-column>
          <el-table-column label="用户名">
            <template slot-scope="scope">
              <div class="text-nowrap" :title="scope.row.username">{{ scope.row.username }}</div>
            </template>
          </el-table-column>
          <el-table-column show-overflow-tooltip label="邮箱" max-width="170">
            <template slot-scope="scope">
              <div>{{ scope.row.email || ' - ' }}</div>
            </template>
          </el-table-column>
          <el-table-column show-overflow-tooltip prop="update_time" label="更新时间" max-width="180"></el-table-column>
          <el-table-column label="状态" min-width="120">
            <template slot-scope="scope">
              <el-tag v-if="scope.row.status===1" size="mini" type="success">正常</el-tag>
              <el-tag v-else size="mini" type="danger">禁止</el-tag>
              <el-tag v-if="isMe(scope.row.username)" type="warning" size="mini">自己</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="角色" min-width="150">
            <template slot-scope="scope">
              <el-tag v-for="(v,k) in scope.row.group_name" type="info" size="mini" :key='k'>{{v}}</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="200">
            <template slot-scope="scope">
              <div class="btns-operating">
                <el-button type="info" size="mini" @click="editRow(scope)" icon="el-icon-edit" title="编辑用户">编 辑</el-button>
                <el-popover placement="top" width="160" v-model="scope.row.popover">
                  <p>
                    确定删除？ <br> {{ scope.row.username }} </p>
                  <div>
                    <el-button size="mini" @click="scope.row.popover = false" type="info" plain>取 消</el-button>
                    <el-button type="danger" size="mini" @click="deleteRow(scope)" plain>确 定</el-button>
                  </div>
                  <el-button :disabled="isMe(scope.row.username)" slot="reference" size="mini" type="danger" icon="el-icon-delete" title="删除用户">删 除</el-button>
                </el-popover>
              </div>
            </template>
          </el-table-column>
        </el-table>
        <div class="tip-page" v-if="!!ml_pagetotal">
          <el-pagination :current-page.sync="ml_page" @size-change="ml_sizeChange" @current-change="ml_currentChange" background layout="prev, pager, next, sizes" :total="ml_pagetotal" :page-size.sync="ml_pagesize"></el-pagination>
        </div>
      </aside>
    </fieldset>
    <el-dialog class="dialog-view" :title="viewDialogtitle" :visible.sync="viewDialogVisible" :close-on-press-escape="false" :close-on-click-modal="false" center>
      <components_user-view :info="info" @submit="userSubmitSucceed"></components_user-view>
    </el-dialog>
  </div>
</template>
<script>
  Spa.define(
    {
      mixins: [mixinLists, initTitle],
      data: function () {
        return {
          viewDialogVisible: false,
          info: {},
          search: '',
          listsLoading: true
        };
      },
      mounted: function () {},
      computed: {
        groups: function () {
          return this.$store.getters.groups;
        },
        viewDialogtitle: function () {
          return !!this.info.id ? '编辑用户' : '添加用户';
        }
      },
      init: function (query, search) {
        if (!!search[0]) {
          this.ml_listsLoading = true;
          this.ml_data = [];
          this.ml_searchKey = search[0];
          this.getLists();
        }
      },
      methods: {
        userSubmitSucceed: function (id) {
          this.viewDialogVisible = false;
          this.ml_reloadLists();
          if (+id === +this.$store.state.user.id) {
            this.$root.getInfo();
          }
        },
        create: function () {
          this.viewDialogVisible = true;
          this.info = {};
        },
        editRow: function (e) {
          this.info = e.row;
          this.viewDialogVisible = !this.viewDialogVisible;
        },
        deleteRow: function (v) {
          this.$api(apis.sysDeleteUser, { id: v.row.id })
          .then(function (e) {
            $this.ml_data.splice(v.$index, 1);
            if ($this.ml_data.length <= 0) {
              $this.ml_reloadLists();
            }
          })
          .catch(function (msg) {
            $this.$warMsg(msg);
          })
          .finally(function () {
            v.row.popover = false;
          });
        },
        isMe: function (name) {
          return name === this.$store.getters.nickname;
        },
        getLists: function () {
          var data = { page: this.ml_page, pagesize: this.ml_pagesize };
          if (this.ml_searchKey) {
            data['key'] = this.ml_searchKey;
          }
          $this.ml_listsLoading = true;
          $this.$api(apis.sysUserLists, data)
          .then(function (e) {
            var data = (e.data.items || []).map(function (e) {
              e.group_name = [];
              for (var k in e.groups) {
                if (e.groups.hasOwnProperty(k)) {
                  e.group_name.push(e.groups[k]);
                }
              }
              e.popover = false;
              return e;
            });
            var page = e.data.page;
            $this.ml_getLists(data, page);
          })
          .finally(function () {
            $this.ml_listsLoading = false;
          });
        }
      }
    },
    ['/components/user-view'],
    '/index'
  );
</script>
