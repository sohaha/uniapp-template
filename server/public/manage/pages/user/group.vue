<template>
  <div>
    <div class="view-title float-clear">
      <div class="view-title-right">
        <el-form @submit.prevent.stop.native inline class="tip-top">
          <el-form-item>
            <el-button type="primary" size="mini" @click="useAddRowStatus" icon="el-icon-plus" :disabled="isAddRow">添加
            </el-button>
            <el-button type="info" size="mini" @click="useGetLists" icon="el-icon-refresh">刷新</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <fieldset>
      <legend>{{ title }}</legend>
      <aside v-loading="false" class="group-table">
        <el-table :data="listData" style="width: 100%" size="mini">
          <el-table-column prop="id" label="ID" width="50"></el-table-column>
          <el-table-column show-overflow-tooltip label="角色名称" min-width="120">
            <template slot-scope="scope">
              <div v-if="scope.row._isEdit">
                <el-input v-model="scope.row.name" placeholder="角色名称" size="mini"></el-input>
              </div>
              <div v-else class="text-nowrap" :title="scope.row.name">{{ scope.row.name }}</div>
            </template>
          </el-table-column>
          <el-table-column show-overflow-tooltip label="角色简介" min-width="120">
            <template slot-scope="scope">
              <div v-if="scope.row._isEdit">
                <el-input v-model="scope.row.remark" placeholder="角色简介" size="mini"></el-input>
              </div>
              <div v-else class="text-nowrap" :title="scope.row.remark">{{ scope.row.remark }}</div>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="350">
            <template slot-scope="scope">
              <div class="btns-operating">
                <el-button :disabled="scope.row._isEdit" @click="useOpenRuleView(scope)" slot="reference" size="mini"
                           type="success" title="查看规则" icon="icon-person-done">角色规则
                </el-button>
                <el-button v-bind="useGetEditBtnAttrs(scope)" size="mini" @click="useEditRow(scope)">
                  {{ useGetEditBtnAttrs(scope).title }}
                </el-button>
                <el-button v-if="scope.row._isEdit" title="放 弃" @click="useQuitRow(scope)" size="mini"
                           icon="el-icon-close">放 弃
                </el-button>
                <template>
                  <el-popover placement="top" width="160" v-model="scope.row._isPopover">
                    <p>确定删除吗？</p>
                    <div>
                      <el-button size="mini" @click="scope.row._isPopover = false" type="info" plain>取 消</el-button>
                      <el-button type="danger" size="mini" @click="useDeleteRow(scope)" plain>确 定</el-button>
                    </div>
                    <el-button v-show="!scope.row._isEdit" slot="reference" size="mini" type="danger"
                               icon="el-icon-delete" title="删 除">删除
                    </el-button>
                  </el-popover>
                </template>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </aside>
    </fieldset>

    <!-- <el-dialog
      class="dialog-view"
      title="权限编辑"
      :visible.sync="viewDialogVisible"
      :close-on-press-escape="false"
      :close-on-click-modal="false"
      center
    >
      <components_rule-view  @submit=""></components_rule-view>
    </el-dialog>-->
  </div>
</template>
<script>
const {ref, reactive, computed, onMounted, watch} = vue;
const {useRouter, useStore, useCache, useTip, useLoading} = hook;
const {user: userApi, useRequestWith} = api;
const {useInitTitle} = util;

let dataFormat = {title: '', date: '', id: 0};
export default {
  components: {
    userView: VueRun('components/user-view.vue')
  },
  setup(prop, ctx) {
    const {root} = ctx;
    const {title} = useInitTitle(ctx);

    let listData = ref([]);

    const getLists = useRequestWith(userApi.groupLists, {
      manual: true, dataHandle(e) {
        e = e.map((e) => {
          e._isEdit = false;
          e._isPopover = false;

          return e;
        })
        useStore(ctx).commit('setGroups', e)

        return e;
      }
    });

    async function useGetLists() {
      isAddRow.value = false;
      const [data, err] = await getLists.run();
      if (err) {
        useTip().message('warning', err);
      } else {
        listData.value = data;
      }
    }

    onMounted(() => {
      useGetLists();
    })

    let tmpData = ref([]);
    let isAddRow = ref(false);
    let viewDialogVisible = ref(true);

    function useOpenRuleView(e) {
      useRouter(ctx).replace('/main/user/rules?key=' + e.row.id)
    }

    function useAddRowStatus() {
      isAddRow.value = true;
      listData.value.unshift(Object.assign({_isEdit: true, _isPopover: false, _isAdd: true}, dataFormat))
    }

    function useQuitRow(e) {
      let index = e.$index;
      if (!e.row._isAdd) {
        vue.set(listData.value, e.$index, Object.assign({}, (tmpData.value)[index]))
      } else {
        isAddRow.value = false;
        listData.value.splice(e.$index, 1);
      }
    }

    const deleteRow = useRequestWith(userApi.deleteGroup, {manual: true});

    async function useDeleteRow(e) {
      const [data, err] = await deleteRow.run();
      if (err) {
        useTip().message('warning', err);
      } else {
        console.log('data', data);
        listData.value.splice(e.$index, 1);
        root.$nextTick(() => {
          if (listData.length <= 0) useGetLists();
        })
      }
    }

    const addRow = useRequestWith(userApi.createGroup, {manual: true});

    async function useAddRow(e) {
      const [data, err] = await addRow.run(e.row);
      if (err) {
        useTip().message('warning', err);
      } else {
        e.row._isEdit = false;
        e.row._isAdd = false;
        isAddRow.value = false;
        vue.set(listData.value, e.$index, Object.assign({}, e.row, data));
      }
    }

    const editRow = useRequestWith(userApi.createGroup, {manual: true});

    async function useEditRow(e) {
      if (e.row._isAdd) {
        useAddRow(e);
        return;
      }
      if (e.row._isEdit) {
        const [data, err] = await editRow.run(e.row);
        if (err) {
          useTip().message('warning', err);
        } else {
          e.row._isEdit = false;
          vue.set(listData.value, e.$index, Object.assign({}, e.row, data));
        }
      } else {
        vue.set(tmpData.value, e.$index, Object.assign({}, e.row));
        e.row._isEdit = !e.row._isEdit;
      }
    }

    function useGetEditBtnAttrs(e) {
      return e.row._isEdit
          ? {
            title: '提 交',
            type: 'primary',
            icon: 'el-icon-check'
          }
          : {
            title: '编 辑',
            type: 'info',
            icon: 'el-icon-edit'
          };
    }

    return {
      title,
      listData,
      useGetLists,
      useEditRow,
      useDeleteRow,
      viewDialogVisible,
      isAddRow,
      useAddRowStatus,
      useGetEditBtnAttrs,
      useQuitRow,
      useOpenRuleView
    };
  }
};
</script>

<style scoped>
.group-table {
  padding-bottom: 30px;
}
</style>
