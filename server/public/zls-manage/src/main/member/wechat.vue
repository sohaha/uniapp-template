<style></style>
<template>
  <div>
    <div class="view-title float-clear">
      <div class="view-title-right">
        <el-form @submit.prevent.stop.native inline class="tip-top">
          <el-form-item>
            <el-button type="info" size="mini" @click="ml_reloadLists" icon="el-icon-refresh">刷新</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <fieldset>
      <legend>{{title}}</legend>
      <aside v-loading="ml_listsLoading">
        <el-table :data="ml_data" style="width: 100%" size="mini">
          <el-table-column label="用户头像" width="80">
            <template slot-scope="scope">
              <el-tooltip placement="right-end" effect="light" :visible-arrow="false">
                <div slot="content" class="list-avatar-tooltip">
                  <img :src="scope.row.avatar||$store.state.defaultData.avatar" alt='用户头像'>
                </div>
                <el-image fit="cover" class="list-avatar" :src="scope.row.avatar||$store.state.defaultData.avatar" />
              </el-tooltip>
            </template>
          </el-table-column>
          <el-table-column show-overflow-tooltip label="用户昵称" width="170">
            <template slot-scope="scope">
              <div>{{ scope.row.nickname || ' - ' }}</div>
            </template>
          </el-table-column>
          <el-table-column show-overflow-tooltip prop="openid" label="Openid"></el-table-column>
          <el-table-column show-overflow-tooltip prop="update_time" label="更新时间"></el-table-column>
          <el-table-column label="操作" width="100">
            <template slot-scope="scope">
              <el-button v-if='scope.row.status === 1' @click='banWxUser(scope,2)' type="success" size="mini" title='拉黑该用户'>拉黑</el-button>
              <el-button v-if='scope.row.status === 2' @click='banWxUser(scope,1)' type="info" size="mini" title='把该用户从小黑屋恢复'>恢复</el-button>
            </template>
          </el-table-column>
        </el-table>
        <div class="tip-page" v-if="!!ml_pagetotal">
          <el-pagination :current-page.sync="ml_page" @size-change="ml_sizeChange" @current-change="ml_currentChange" background layout="prev, pager, next, sizes, total" :total="ml_pagetotal" :page-size.sync="ml_pagesize" />
        </div>
      </aside>
    </fieldset>
  </div>
</template>
<script>
  Spa.define(
    {
      mixins: [mixinLists, initTitle],
      data: function () {
        return {};
      },
      computed: {},
      init: function (query, search) {
        $this.ml_pagesize = 10;
      },
      mounted: function () {},
      methods: {
        banWxUser: function (scope, status) {
          var id = scope.row.id;
          this.$api(apis.memberBanWxUser, { id: id, status: status }).then(function () {
            $this.ml_data[scope.$index].status = status;
          }).catch(function (e) {
            $this.$errNotify(e.toString());
          });
        },
        getLists: function () {
          var data = { page: this.ml_page, pagesize: this.ml_pagesize };
          if (this.ml_searchKey) {
            data['key'] = this.ml_searchKey;
          }
          $this.ml_listsLoading = true;
          this.$api('memberList', data)
          .then(function (v) {
            var data = (v.data.items || []).map(function (e) {
              e._isEdit = false;
              e._isPopover = false;
              return e;
            });
            var page = v.data.page;
            $this.ml_getLists(data, page);
          })
          .catch(function (e) {
            $this.$warMsg(e);
          })
          .finally(function () {
            $this.ml_listsLoading = false;
          });
        }
      }
    },
    [],
    '/index'
  );
</script>
