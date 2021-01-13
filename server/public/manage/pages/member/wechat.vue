<template>
  <div>
    <div class="view-title float-clear">
      <div class="view-title-right">
        <el-form @submit.prevent.stop.native inline class="tip-top">
          <el-form-item>
            <el-button type="info" size="mini" @click="listChange" icon="el-icon-refresh">刷新</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <fieldset>
      <legend>{{ title }}</legend>
      <aside v-loading="listLoading">
        <el-table :data="listData" style="width: 100%" size="mini">
          <el-table-column label="用户头像" width="80">
            <template slot-scope="scope">
              <el-tooltip placement="right-end" effect="light" :visible-arrow="false">
                <div slot="content" class="list-avatar-tooltip">
                  <img :src="scope.row.avatar||$store.state.defaultData.avatar" alt='用户头像'>
                </div>
                <el-image fit="cover" class="list-avatar" :src="scope.row.avatar||$store.state.defaultData.avatar"/>
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
              <el-button v-if='scope.row.status === 1' @click='useBanWxUser(scope,2)' type="success" size="mini"
                         title='拉黑该用户'>拉黑
              </el-button>
              <el-button v-if='scope.row.status === 2' @click='useBanWxUser(scope,1)' type="info" size="mini"
                         title='把该用户从小黑屋恢复'>恢复
              </el-button>
            </template>
          </el-table-column>
        </el-table>
        <div class="tip-page" v-if="!!listPages.total">
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
      </aside>
    </fieldset>
  </div>
</template>
<script>
const {ref, reactive, computed, onMounted, watch} = vue;
const {useRouter, useStore, useCache, useTip, useLoading} = hook;
const {member: memberApi, useRequestWith, useRequestPage} = api;
const {useInitTitle} = util;
export default {
  components: {},
  setup(prop, ctx) {
    const {title} = useInitTitle(ctx);

    const reqMemberBanWxUser = useRequestWith(memberApi.memberBanWxUser, {manual: true});

    async function useBanWxUser(scope, status) {
      let id = scope.row.id;
      const [, err] = await reqMemberBanWxUser.run({id: id, status: status});
      if (err) {
        useTip().message('warning', err);
      } else {
        lists.items.value[scope.$index].status = status;
      }
    }

    let listKey = ref('');
    const lists = useRequestPage(memberApi.memberList, {page: 1, pagesize: 10, key: listKey}, {
      dataHandle(e) {
        e.items = e.items.map((e) => {
          e._isEdit = false;
          e._isPopover = false;

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

    return {
      title,
      listKey,
      ...listRes,
      useBanWxUser
    };
  }
};
</script>

<style scoped>

</style>
