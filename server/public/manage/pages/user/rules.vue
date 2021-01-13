<template>
  <div v-loading='false || loadGroup'>
    <div class="view-title float-clear">
      <div class="view-title-right">
        <el-form @submit.prevent.stop.native inline class="tip-top">
          <el-form-item>
            <el-select clearable v-model="gid" size="mini" placeholder="选择角色查看规则">
              <el-option v-for="item in groups" :key="item.id" :label="item.name" :value="item.id"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item v-if="!gid">
            <el-button type="primary" size="mini" @click="useAddRowStatus" icon="el-icon-plus" :disabled="isAddRow">添加
            </el-button>
          </el-form-item>
          <el-form-item>
            <el-button type="info" size="mini" @click="useReloadLists" icon="el-icon-refresh">刷新</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <div class="tip-area" v-if="!gid">温馨提示: 如不熟悉请不要随意更改权限；路由不区分大小写</div>
    <div class="tip-area" v-else>温馨提示: 如不熟悉请不要随意更改路由权限；优先级“禁止”最高->标识码->路由通行</div>
    <fieldset v-if="gid">
      <legend>角色信息</legend>
      <aside v-loading="loadGroup">
        <div class="tip-area">当前角色下共有{{ ginfo.user_count }}名用户，生效规则共 {{ rulesCount }} 条。</div>
        <div>
          <div>
            <span>{{ ginfo.name }}</span>
          </div>
          <div>
            <span class="text-grey">简介：{{ ginfo.remark || ' -- ' }}</span>
          </div>
        </div>
        <!-- <el-input v-model="gid"> </el-input> -->
      </aside>
    </fieldset>
    <fieldset>
      <legend>{{ title }}</legend>
      <aside class="rules-table">
        <el-table :data="listData" size="mini" :default-sort="!!gid?{prop: 'sort', order: 'descending'}:{}">
          <!-- <el-table-column :sortable="!!gid" show-overflow-tooltip :label="gid?'排序':'#'" width="80" prop="sort">
            <template slot-scope="scope">
              <div v-if="gid" title="数字越大优先级越高">
                <el-input type="number" @change="useChangeSort" :data-index="scope.$index" @focus="useSortFocus" v-model="scope.row.sort" size="mini"></el-input>
              </div>
               <div v-else>{{ scope.row.id || ' - ' }}</div>
            </template>
          </el-table-column>-->
          <el-table-column label="规则名称" width="160">
            <template slot-scope="scope">
              <div v-if="scope.row._isEdit">
                <el-input v-model="scope.row.title" placeholder="请填写规则名称" size="mini"></el-input>
              </div>
              <div v-else class='text-nowrap' :title='scope.row.title'>{{ scope.row.title || ' - ' }}</div>
            </template>
          </el-table-column>
          <el-table-column label="标识" min-width="160">
            <template slot-scope="scope">
              <div v-if="scope.row._isEdit">
                <el-input v-if="scope.row.type===1" size="mini" type="textarea" autosize placeholder="请填写路由规则"
                          v-model="scope.row.mark">
                </el-input>
                <el-input v-else v-model="scope.row.mark" size="mini" type="textarea" autosize placeholder="请填写标识码，唯一"
                          size="mini"></el-input>
              </div>
              <el-link @click="useEditRow(scope)" :underline="false" :type="scope.row.type===1?'primary':'success'"
                       v-else class='text-nowrap white-space' :title='scope.row.mark'>{{ scope.row.mark || ' - ' }}
              </el-link>
            </template>
          </el-table-column>
          <el-table-column label="类型" width="130">
            <template slot-scope="scope">
              <div v-if="scope.row._isEdit && !scope.row.id">
                <el-select v-model="scope.row.type" placeholder="请选择" size="mini">
                  <el-option :key='k' v-for='(v,k) in types' :label='v' :value='k'></el-option>
                </el-select>
              </div>
              <el-tag :disable-transitions='true' size='mini' :type="scope.row.type===1?'primary':'success'" v-else>
                {{ useRuleType(scope.row.type) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column :show-overflow-tooltip='false' label="备注" min-width="150">
            <template slot-scope="scope">
              <div v-if="scope.row._isEdit">
                <el-input v-model="scope.row.remark" placeholder="请填写规则备注" size="mini"></el-input>
              </div>
              <div v-else class='text-nowrap white-space' :title='scope.row.remark'>{{
                  scope.row.remark || ' - '
                }}
              </div>
            </template>
          </el-table-column>
          <el-table-column label="操作" :width="gid?290:212">
            <!-- eslint-disable-next-line vue/no-unused-vars -->
            <template slot="header" slot-scope="scope">
              <el-form @submit.prevent.stop.native inline class="table-header-form" :inline="true">
                <el-form-item class="form-item-search" :style="searchKey?'width:'+(gid?190:110)+'px':''">
                  <el-input v-model="searchKey" size="mini" clearable placeholder="输入名称/标识搜索"
                            suffix-icon="icon-corner-down-left-out" @keyup.enter.native="useSearchRow"
                            @clear="useReloadLists"></el-input>
                </el-form-item>
                <el-form-item v-show="!!searchKey">
                  <el-button size="mini" icon="icon-search" type @click="useSearchRow">搜索</el-button>
                </el-form-item>
              </el-form>
            </template>
            <template slot-scope="scope">
              <div>
                <div v-if="gid">
                  <el-radio-group class="status" size="mini" v-model="scope.row.gstatus"
                                  @click.stop.native="useClickChangeStatus(scope,$event)">
                    <el-radio-button :label="1">{{ scope.row.type !== 2 ? '通 行' : '拥 有' }}</el-radio-button>
                    <el-radio-button :label="3">未使用</el-radio-button>
                    <el-radio-button :label="2" v-show='scope.row.type!==2' :disabled='scope.row.type===2'
                                     title='禁止拥有该规则的用户访问/标识码不支持'>禁 止
                    </el-radio-button>
                  </el-radio-group>
                </div>
                <div v-else>
                  <div class="btns-operating">
                    <el-button v-bind="useGetEditBtnAttrs(scope)" size="mini" @click="useEditRow(scope)"
                               :loading="scope.row._loading">{{ useGetEditBtnAttrs(scope).title }}
                    </el-button>
                    <el-button v-if="scope.row._isEdit" title="放 弃" @click="useQuitRow(scope)" size="mini"
                               :loading="scope.row._loading" icon="el-icon-close">放 弃
                    </el-button>
                    <template>
                      <el-popover placement="top" width="160" v-model="scope.row._isPopover">
                        <p>确定删除吗？</p>
                        <div>
                          <el-button size="mini" @click="scope.row._isPopover = false" type="info" plain>取 消</el-button>
                          <el-button type="danger" size="mini" @click="useDeleteRow(scope)" plain>确 定</el-button>
                        </div>
                        <el-button v-show="!scope.row._isEdit" slot="reference" size="mini" type="danger"
                                   icon="el-icon-delete" title="删 除">删 除
                        </el-button>
                      </el-popover>
                    </template>
                  </div>
                </div>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </aside>
    </fieldset>
    <fieldset v-if="gid">
      <legend>菜单设置</legend>
      <aside :aria-label="title">
        <el-tree
            ref="menuRef"
            v-loading="Menuloading"
            :data="menuData"
            show-checkbox
            node-key="id"
            :default-expanded-keys="[]"
            :default-checked-keys="showKeepMenuArr"
            :props="{ children: 'child',label: 'title'}"
            :default-expand-all="true"
        >
          <div class="custom-tree-node" slot-scope="{ node, data }">
            ({{ data.id }})
            <span><i :class="data.icon"></i>{{ node.label }}</span>
            <span v-show='!data.child'> ( {{ data.index }} )</span>
          </div>
        </el-tree>
        <div class="btn-box">
          <el-button
              type="primary"
              size="mini"
              icon="el-icon-check"
              @click="keepRoleMenu"
          >保 存
          </el-button
          >
        </div>
      </aside>
    </fieldset>
  </div>
</template>
<script>
const {ref, reactive, computed, onMounted, watch} = vue;
const {useRouter, useStore, useCache, useTip, useLoading} = hook;
const {user: userApi, sys: sysApi, useRequest, useRequestWith} = api;
const {useInitTitle} = util;

let dataFormat = {
  title: '',
  remark: '',
  id: 0,
  router: '',
  sort: 0,
  gstatus: 3,
  type: '',
  _loading: false
};
let initTypes = {
  '1': 'API路由',
  '2': '标识码'
};
let viewTitle = '权限设置';

export default {
  components: {},
  setup(prop, ctx) {
    const {root} = ctx;
    const {title} = useInitTitle(ctx);

    let searchKey = ref('');
    let listData = ref([]);
    let gid = ref(parseInt(useRouter(ctx).route.query.key) || '');

    let ginfo = ref({user_count: 0});
    let isAddRow = ref(false);
    let tmpIndex = ref(null);
    let tmpData = ref([]);
    let loadGroup = ref(false);
    let types = ref(initTypes);

    let menuRef = ref(null);
    let menuData = ref([]);
    let Menuloading = ref(true);
    let keepMenuArr = ref([]);
    let showKeepMenuArr = ref([]);

    const deleteRow = useRequestWith(userApi.deleteRule, {manual: true});
    const addRow = useRequestWith(userApi.addRule, {manual: true});
    const editRow = useRequestWith(userApi.editRule, {manual: true});
    const updateUserRuleStatus = useRequestWith(userApi.updateUserRuleStatus, {manual: true});
    const getGroup = useRequestWith(userApi.groupInfo, {manual: true});
    const getRules = useRequestWith(userApi.ruleLists, {manual: true});
    const updateGroupMenu = useRequestWith(sysApi.sysUpdateGroupMenu, {manual: true});
    const userMenu = useRequestWith(sysApi.sysUserMenu, {manual: true});

    if (gid.value) {
      useGetGroup();
      getMenuList(gid.value)
    } else {
      useGetRules()
      getMenuList()
    }
    // menuData.value = useStore(ctx).getters.menus;
    title.value = gid.value ? '角色权限' : '权限设置';
    watch(gid, (val) => {
      if (val) {
        title.value = '角色权限';
        useGetGroup();
      } else {
        title.value = '权限设置';
      }
      getMenuList(val)
    })

    const groups = computed(() => {
      return useStore(ctx).getters.groups;
    })

    const rulesCount = computed(() => {
      return banRuleIds.value.length + ruleIds.value.length;
    })

    const banRuleIds = computed(() => {
      return ginfo.value.ban_rule_ids || [];
    })

    const ruleIds = computed(() => {
      return ginfo.value.rule_ids || [];
    })

    function useRuleType(v) {
      return (types.value)[v] || '--';
    }

    function useChangeSort(v) {
      let row = (listData.value)[tmpIndex.value];
      useUpdateUserRuleStatus(row.id, row.gstatus, row.sort);
      tmpIndex.value = null;
    }

    function useSortFocus(e) {
      tmpIndex.value = e.target.dataset.index;
    }

    async function useDeleteRow(e) {
      const [, err] = await deleteRow.run(e.row);
      if (err) {
        useTip().message('warning', err);
      } else {
        listData.value.splice(e.$index, 1);
        root.$nextTick(() => {
          if (listData.length <= 0) getMenuList();
        })
      }
    }

    async function useAddRow(e) {
      e.row._loading = true;
      const [data, err] = await addRow.run(e.row);
      if (err) {
        useTip().message('warning', err);
      } else {
        e.row._isEdit = false;
        e.row._isAdd = false;
        e.row._loading = false;
        isAddRow.value = false;
        vue.set(listData.value, e.$index, Object.assign({}, e.row, data))
      }
      e.row._loading = false;
    }

    function useAddRowStatus() {
      isAddRow.value = true;
      listData.value.unshift(
          Object.assign(
              {_isEdit: true, _isPopover: false, _isAdd: true},
              dataFormat
          )
      );
    }

    async function useEditRow(e) {
      if (gid.value) return;
      if (e.row._isAdd) {
        await useAddRow(e);
        return;
      }
      if (e.row._isEdit) {
        const [, err] = await editRow.run(e.row);
        if (err) {
          useTip().message('warning', err);
        } else {
          let val = {data: {}};
          e.row._isEdit = false;
          vue.set(listData.value, e.$index, Object.assign({}, e.row, val.data))
        }
      } else {
        vue.set(tmpData.value, e.$index, Object.assign({}, e.row));
        e.row._isEdit = !e.row._isEdit;
      }
    }

    function useQuitRow(e) {
      let index = e.$index;
      if (!e.row._isAdd) {
        vue.set(listData.value, e.$index, Object.assign({}, (tmpData.value)[index]));
      } else {
        isAddRow.value = false;
        listData.value.splice(e.$index, 1);
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

    function useClickChangeStatus(e, l) {
      if (l.target.nodeName === 'INPUT') {
        setTimeout(() => {
          tmpIndex.value = e.$index;
          useUpdateUserRuleStatus(e.row.id, e.row.gstatus, e.row.sort);
        });
      }
    }

    async function useUpdateUserRuleStatus(id, status, sort) {
      const [, err] = await updateUserRuleStatus.run({
        gid: gid.value,
        id: id,
        status: status,
        sort: sort
      });
      if (err) {
        useTip().message('warning', err);
      } else {
        switch (status) {
          case 1:
            window['arrayAdd'](ginfo.value.rule_ids, id);
            window['arrayReduce'](ginfo.value.ban_rule_ids, id);
            break;
          case 2:
            window['arrayReduce'](ginfo.value.rule_ids, id);
            window['arrayAdd'](ginfo.value.ban_rule_ids, id);
            break;
          case 3:
            window['arrayReduce'](ginfo.value.rule_ids, id);
            window['arrayReduce'](ginfo.value.ban_rule_ids, id);
            break;
        }
      }
    }

    async function useGetGroup() {
      loadGroup.value = true;

      const [data, err] = await getGroup.run({id: gid.value});
      if (err) {
        useTip().message('warning', err);
        gid.value = 0;
      } else {
        ginfo.value = data;
        useGetRules();
      }
      loadGroup.value = false;
    }

    async function useGetRules() {
      let param = {};
      if (searchKey.value) {
        param['key'] = searchKey.value;
      }
      tmpData.value = [];

      const [data, err] = await getRules.run(param);
      if (err) {
        useTip().message('warning', err);
      } else {
        let res = data;
        res.map((e) => {
          if (gid.value) {
            e.gstatus = ruleIds.value.indexOf(e.id) >= 0 ? 1 : banRuleIds.value.indexOf(e.id) >= 0 ? 2 : 3;
          }
          e.__loading = false;
          e._isEdit = false;
          e._isPopover = false;
          return e;
        })
        listData.value = JSON.parse(JSON.stringify(res));
      }
    }

    function useReloadLists() {
      searchKey.value = '';
      loadGroup.value = false;
      useGetRules();
    }

    function useSearchRow() {
      useGetRules();
    }

    async function keepRoleMenu(groupid, menu) {
      let role = gid.value;
      let useKeepMenuArr = menuRef.value.getCheckedKeys().concat(menuRef.value.getHalfCheckedKeys()).join(",");

      const [, err] = await updateGroupMenu.run({groupid: role, menu: useKeepMenuArr});
      if (err) {
        useTip().message('warning', err);
      } else {
        useTip().message('success', '保存角色菜单成功');
      }
      getMenuList(role);
    }

    async function getMenuList(roleId = 0) {
      const [data, err] = await userMenu.run({groupid: roleId});
      if (err) {
        useTip().message('warning', err);
      } else {
        menuData.value = data;
        getShowMenu(data);
      }
      Menuloading.value = false;
    }

    function getShowMenu(data) {
      showKeepMenuArr.value = [];
      for (let i in data) {
        if (data[i].child) {
          for (let ii in data[i].child) {
            if (data[i].child[ii].is_show) {
              showKeepMenuArr.value.push(data[i].child[ii].id);
            }
          }
        } else {
          if (data[i].is_show) {
            showKeepMenuArr.value.push(data[i].id);
          }
        }
      }
    }

    return {
      title,
      searchKey,
      listData,
      isAddRow,
      loadGroup,
      gid,
      groups,
      useAddRowStatus,
      useReloadLists,
      menuRef,
      menuData,
      Menuloading,
      showKeepMenuArr,
      keepRoleMenu,
      useSearchRow,
      useRuleType,
      useGetEditBtnAttrs,
      rulesCount,
      banRuleIds,
      ruleIds,
      useQuitRow,
      ginfo,
      types,
      useEditRow,
      useDeleteRow,
      useClickChangeStatus
    };
  }
};
</script>

<style scoped>
.status .is-active .el-radio-button__inner {
  box-shadow: -1px 0 0 0;
}

.status .el-radio-button:first-child.is-active .el-radio-button__inner {
  background-color: #68c13e;
  border-color: #68c13e;
}

.status .el-radio-button:last-child.is-active .el-radio-button__inner {
  background-color: #f44336;
  border-color: #f44336;
}

.rules-table {
  padding-bottom: 30px;
}

.table-header-form .el-form-item__content {
  padding: 0;
  line-height: 0;
}

.table-header-form .el-form-item {
  padding: 0;
  margin: 0;
}

.table-header-form .el-form-item + .el-form-item {
  margin-left: 4px;
}

.table-header-form .el-input {
  padding: 0;
  line-height: 0;
}

.table-header-form, .el-radio-group.status {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
}

.form-item-search .el-form-item__content {
  width: 100%;
}

.form-item-search {
  width: 290px;
}

.el-radio-button--mini .el-radio-button__inner {
  display: block;
}

.el-radio-group.status .el-radio-button {
  -webkit-box-flex: 1;
  -ms-flex: auto;
  flex: auto;
}

.el-input--mini .el-textarea__inner {
  padding: 4px 15px;
}

.white-space {
  white-space: pre-line;
}
</style>
