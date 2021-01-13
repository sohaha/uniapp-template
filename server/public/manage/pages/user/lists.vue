<template>
  <div>
    <div class="view-title float-clear">
      <span>{{ title }}</span>
      <div class="view-title-right float-clear">
        <el-form @submit.prevent.stop.native inline class="tip-top">
          <el-form-item>
            <el-input clearable @keyup.enter.stop.prevent.native="listChange" v-model="listKey"
                      placeholder="用户昵称" size="mini">
              <el-button @click="listChange" type="success" slot="append" size="mini"
                         icon="el-icon-search"></el-button>
            </el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" size="mini" @click="useCreate" icon="el-icon-plus">添加</el-button>
            <el-button type="info" size="mini" @click="listChange" icon="el-icon-refresh">刷新</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <fieldset>
      <legend>{{ title }}</legend>
      <aside v-loading="listLoading">
        <el-table :data="listData" style="width: 100%" size="mini">
          <el-table-column label="头像" width="80">
            <template slot-scope="scope">
              <el-tooltip placement="right-end" effect="light" :visible-arrow="false">
                <div slot="content" class="list-avatar-tooltip">
                  <img :src="scope.row.avatar||$store.state.defaultData.avatar" alt='用户头像'>
                </div>
                <template v-if="scope.row.avatar">
                  <el-image fit="fill" class="list-avatar" :src="scope.row.avatar+ '?t=' + new Date().getTime()">
                    <div slot="error" class="image-slot">
                      <img :src="baseUrl + scope.row.avatar + '?t=' + new Date().getTime()" alt='用户头像'>
                    </div>
                  </el-image>
                </template>
                <template v-else>
                  <el-image fit="cover" class="list-avatar" :src="$store.state.defaultData.avatar"></el-image>
                </template>
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
              <el-tag v-for="(v,k) in scope.row.group_name" type="info" size="mini" :key='k'>{{ v }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="200">
            <template slot-scope="scope">
              <div class="btns-operating">
                <el-button type="info" size="mini" @click="useEditRow(scope)" icon="el-icon-edit" title="编辑用户">编 辑
                </el-button>
                <el-popover placement="top" width="160" v-model="scope.row.popover">
                  <p>
                    确定删除？ <br> {{ scope.row.username }} </p>
                  <div>
                    <el-button size="mini" @click="scope.row.popover = false" type="info" plain>取 消</el-button>
                    <el-button type="danger" size="mini" @click="useDeleteRow(scope)" plain>确 定</el-button>
                  </div>
                  <el-button :disabled="isMe(scope.row.username)" slot="reference" size="mini" type="danger"
                             icon="el-icon-delete" title="删除用户">删 除
                  </el-button>
                </el-popover>
              </div>
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
    <el-dialog class="dialog-view" :title="viewDialogtitle" :visible.sync="viewDialogVisible"
               :close-on-press-escape="false" :close-on-click-modal="false" center>
      <user-view :info="info" @submit="useUserSubmitSucceed"></user-view>
    </el-dialog>
  </div>
</template>
<script>
const {ref, reactive, computed, onMounted, watch} = vue;
const {useRouter, useStore, useCache, useTip, useLoading} = hook;
const {user: userApi, useRequestWith, useRequestPage} = api;
const {useInitTitle, getInfo} = util;
export default {
  components: {
    userView: VueRun('components/user-view.vue')
  },
  setup(prop, ctx) {
    const {title} = useInitTitle(ctx);

    let listKey = ref('');
    const lists = useRequestPage(userApi.list, {page: 1, pagesize: 10, key: listKey}, {
      dataHandle(e) {
        e.items = e.items.map((e) => {
          e.group_name = [];
          for (let k in e.groups) {
            if (e.groups.hasOwnProperty(k)) {
              e.group_name.push(e.groups[k]);
            }
          }
          e.popover = false;

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

    const baseUrl = computed(() => {
      return config.baseURL;
    })

    let info = ref({});
    let viewDialogVisible = ref(false);
    const viewDialogtitle = computed(() => {
      return !!info.value.id ? '编辑用户' : '添加用户';
    })

    function isMe(name) {
      return name === useStore(ctx).getters.nickname;
    }

    function useCreate() {
      viewDialogVisible.value = true;
      info.value = {};
    }

    function useEditRow(e) {
      info.value = e.row;
      viewDialogVisible.value = !viewDialogVisible.value;
    }

    const deleteRow = useRequestWith(userApi.deleteUser, {manual: true});

    async function useDeleteRow(v) {
      const [, err] = await deleteRow.run({id: v.row.id});
      if (err) {
        useTip().message('warning', err);
      } else {
        lists.items.value.splice(v.$index, 1);
        if (lists.items.value.length <= 0) {
          lists.change();
        }
      }

      v.row.popover = false;
    }

    function useUserSubmitSucceed(id) {
      viewDialogVisible.value = false;
      lists.change();
      if (+id === +useStore(ctx).state.user.id) {
        getInfo();
      }
    }

    return {
      title,
      listKey,
      ...listRes,
      baseUrl,
      isMe,
      useCreate,
      useEditRow,
      useDeleteRow,
      useUserSubmitSucceed,
      viewDialogtitle,
      viewDialogVisible,
      info
    };
  }
};
</script>

<style scoped>
span.el-tag.el-tag--mini + span.el-tag.el-tag--mini {
  margin-left: 5px;
}
</style>
