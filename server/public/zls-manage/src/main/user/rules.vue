<style>
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

</style>
<template>
  <div v-loading='ml_listsLoading || loadGroup'>
    <div class="view-title float-clear">
      <div class="view-title-right">
        <el-form @submit.prevent.stop.native inline class="tip-top">
          <el-form-item>
            <el-select clearable v-model="gid" size="mini" placeholder="选择角色查看规则">
              <el-option v-for="item in groups" :key="item.id" :label="item.name" :value="item.id"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item v-if="!gid">
            <el-button type="primary" size="mini" @click="addRowStatus" icon="el-icon-plus" :disabled="isAddRow">添加</el-button>
          </el-form-item>
          <el-form-item>
            <el-button type="info" size="mini" @click="ml_reloadLists" icon="el-icon-refresh">刷新</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <div class="tip-area" v-if="!gid">温馨提示: 如不熟悉请不要随意更改权限；路由不区分大小写</div>
    <div class="tip-area" v-else>温馨提示: 如不熟悉请不要随意更改路由权限；优先级“禁止”最高->标识码->路由通行</div>
    <fieldset v-if="gid">
      <legend>角色信息</legend>
      <aside v-loading="loadGroup">
        <div class="tip-area">当前角色下共有{{ginfo.user_count}}名用户，生效规则共 {{rulesCount}} 条。</div>
        <div>
          <div>
            <span>{{ginfo.name}}</span>
          </div>
          <div>
            <span class="text-grey">简介：{{ginfo.remark||' -- '}}</span>
          </div>
        </div>
        <!-- <el-input v-model="gid"> </el-input> -->
      </aside>
    </fieldset>
    <fieldset>
      <legend>{{title}}</legend>
      <aside class="rules-table">
        <el-table :data="ml_data" size="mini" :default-sort="!!gid?{prop: 'sort', order: 'descending'}:{}">
          <!-- <el-table-column :sortable="!!gid" show-overflow-tooltip :label="gid?'排序':'#'" width="80" prop="sort">
            <template slot-scope="scope">
              <div v-if="gid" title="数字越大优先级越高">
                <el-input type="number" @change="changeSort" :data-index="scope.$index" @focus="sortFocus" v-model="scope.row.sort" size="mini"></el-input>
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
                <el-input v-model="scope.row.mark" placeholder="请填写标识码，唯一" size="mini"></el-input>
              </div>
              <el-link @click="editRow(scope)" :underline="false" :type="scope.row.type===1?'primary':'success'" v-else class='text-nowrap' :title='scope.row.mark'>{{ scope.row.mark || ' - ' }}</el-link>
            </template>
          </el-table-column>
          <el-table-column label="类型" width="130">
            <template slot-scope="scope">
              <div v-if="scope.row._isEdit && !scope.row.id">
                <el-select v-model="scope.row.type" placeholder="请选择" size="mini">
                  <el-option :key='k' v-for='(v,k) in types' :label='v' :value='k'></el-option>
                </el-select>
              </div>
              <el-tag :disable-transitions='true' size='mini' :type="scope.row.type===1?'primary':'success'" v-else>{{ ruleType(scope.row.type) }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column :show-overflow-tooltip='false' label="备注" min-width="150">
            <template slot-scope="scope">
              <div v-if="scope.row._isEdit">
                <el-input v-model="scope.row.remark" placeholder="请填写规则备注" size="mini"></el-input>
              </div>
              <div v-else class='text-nowrap' :title='scope.row.remark'>{{ scope.row.remark || ' - ' }}</div>
            </template>
          </el-table-column>
          <el-table-column label="操作" :width="gid?290:212">
            <!-- eslint-disable-next-line vue/no-unused-vars -->
            <template slot="header" slot-scope="scope">
              <el-form @submit.prevent.stop.native inline class="table-header-form" :inline="true">
                <el-form-item class="form-item-search" :style="ml_searchKey?'width:'+(gid?190:110)+'px':''">
                  <el-input v-model="ml_searchKey" size="mini" clearable placeholder="输入名称/标识搜索" suffix-icon="icon-corner-down-left-out" @keyup.enter.native="searchRow" @clear="ml_reloadLists"></el-input>
                </el-form-item>
                <el-form-item v-show="!!ml_searchKey">
                  <el-button size="mini" icon="icon-search" type @click="searchRow">搜索</el-button>
                </el-form-item>
              </el-form>
            </template>
            <template slot-scope="scope">
              <div>
                <div v-if="gid">
                  <el-radio-group class="status" size="mini" v-model="scope.row.gstatus" @click.stop.native="clickChangeStatus(scope,$event)">
                    <el-radio-button :label="1">{{scope.row.type!==2?'通 行':'拥 有'}}</el-radio-button>
                    <el-radio-button :label="3">未使用</el-radio-button>
                    <el-radio-button :label="2" v-show='scope.row.type!==2' :disabled='scope.row.type===2' title='禁止拥有该规则的用户访问/标识码不支持'>禁 止</el-radio-button>
                  </el-radio-group>
                </div>
                <div v-else>
                  <div class="btns-operating">
                    <el-button v-bind="getEditBtnAttrs(scope)" size="mini" @click="editRow(scope)" :loading="scope.row._loading">{{getEditBtnAttrs(scope).title}}</el-button>
                    <el-button v-if="scope.row._isEdit" title="放 弃" @click="quitRow(scope)" size="mini" :loading="scope.row._loading" icon="el-icon-close">放 弃</el-button>
                    <template>
                      <el-popover placement="top" width="160" v-model="scope.row._isPopover">
                        <p>确定删除吗？</p>
                        <div>
                          <el-button size="mini" @click="scope.row._isPopover = false" type="info" plain>取 消</el-button>
                          <el-button type="danger" size="mini" @click="deleteRow(scope)" plain>确 定</el-button>
                        </div>
                        <el-button v-show="!scope.row._isEdit" slot="reference" size="mini" type="danger" icon="el-icon-delete" title="删 除">删 除</el-button>
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
    <fieldset>
      <legend>菜单设置(开发中)</legend>
      <aside :aria-label="title">
        <el-tree :data="menuData" show-checkbox node-key="id" :default-expanded-keys="[2, 3]" :default-checked-keys="[5]" :props="{ children: 'child',
          label: 'title'}">
          <div class="custom-tree-node" slot-scope="{ node, data }">
                 <span>
                    <i :class="data.icon"></i>{{ node.label }}
                 </span> <span v-show='!data.child'> ( {{ data.index }} )</span>
          </div>
        </el-tree>
      </aside>
    </fieldset>
  </div>
</template>
<script>
  var dataFormat = {
    title: '',
    remark: '',
    id: 0,
    router: '',
    sort: 0,
    gstatus: 3,
    type: '',
    _loading: false
  }, types = {
    '1': 'API路由',
    '2': '标识码'
  }, viewTitle = '权限设置';

  Spa.define(
    {
      mixins: [mixinLists, initTitle],
      data: function () {
        return {
          gid: 0,
          ginfo: {
            user_count: 0
          },
          isAddRow: false,
          tmpIndex: null,
          tmpData: [],
          loadGroup: false,
          types: types,
          menuData: []
        };
      },
      filters: {},
      watch: {
        gid: function (v) {
          if (v) {
            $this.title = '角色权限';
            $this.SpaTitle = '角色权限' + ' - %s';
            $this.$SpaSetTitle();
            $this.getGroup();
          } else {
            $this.title = viewTitle;
            $this.SpaTitle = $this.title + ' - %s';
            $this.$SpaSetTitle();
          }
        }
      },
      mounted: function () {
        this.menuData = $this.$store.getters.menus;
      },
      computed: {
        groups: function () {
          return this.$store.getters.groups;
        },
        rulesCount: function () {
          return this.banRuleIds.length + this.ruleIds.length;
        },
        banRuleIds: function () {
          return this.ginfo.ban_rule_ids || [];
        },
        ruleIds: function () {
          return this.ginfo.rule_ids || [];
        }
      },
      init: function (query, search) {
        $this.gid = search[0];
        if ($this.gid) {
        } else {
          $this.getRules();
        }
      },
      methods: {
        ruleType: function (v) {
          return types[v] || '--';
        },
        changeSort: function (v) {
          var row = this.ml_data[this.tmpIndex];
          $this.updateUserRuleStatus(row.id, row.gstatus, row.sort);
          this.tmpIndex = null;
        },
        sortFocus: function (e) {
          this.tmpIndex = e.target.dataset.index;
        },
        deleteRow: function (e) {
          $this
          .$api(apis.sysDeleteRule, e.row)
          .then(function () {
            $this.ml_data.splice(e.$index, 1);
            $this.ml_pagetotal--;
            $this.$nextTick(function () {
              if ($this.ml_data.length <= 0) $this.getLists();
            });
          })
          .catch(function (e) {
            $this.$warMsg(e);
          });
        },
        addRow: function (e) {
          e.row._loading = true;
          $this
          .$api(apis.sysAddRule, e.row)
          .then(function (v) {
            e.row._isEdit = false;
            e.row._isAdd = false;
            e.row._loading = false;
            $this.isAddRow = false;
            $this.$set($this.ml_data, e.$index, Object.assign({}, e.row, v.data));
          })
          .catch(function (err) {
            e.row._loading = false;
            $this.$warMsg(err);
          });
        },
        addRowStatus: function () {
          $this.isAddRow = true;
          $this.ml_data.unshift(
            Object.assign(
              { _isEdit: true, _isPopover: false, _isAdd: true },
              dataFormat
            )
          );
        },
        editRow: function (e) {
          if (this.gid) return;
          if (e.row._isAdd) {
            this.addRow(e);
            return;
          }
          if (e.row._isEdit) {
            $this
            .$api(apis.EditRule, e.row)
            .then(function (v) {
              v = { data: {} };
              e.row._isEdit = false;
              $this.$set(
                $this.ml_data,
                e.$index,
                Object.assign({}, e.row, v.data)
              );
            })
            .catch(function (e) {
              $this.$warMsg(e);
            });
          } else {
            this.$set(this.tmpData, e.$index, Object.assign({}, e.row));
            e.row._isEdit = !e.row._isEdit;
          }
        },
        quitRow: function (e) {
          var index = e.$index;
          if (!e.row._isAdd) {
            $this.$set(
              this.ml_data,
              e.$index,
              Object.assign({}, this.tmpData[index])
            );
          } else {
            $this.isAddRow = false;
            $this.ml_data.splice(e.$index, 1);
          }
        },
        getEditBtnAttrs: function (e) {
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
        },
        clickChangeStatus: function (e, l) {
          if (l.target.nodeName === 'INPUT') {
            setTimeout(function () {
              $this.tmpIndex = e.$index;
              $this.updateUserRuleStatus(e.row.id, e.row.gstatus, e.row.sort);
            });
          }
        },
        updateUserRuleStatus: function (id, status, sort) {
          $this
          .$api(apis.sysUpdateUserRuleStatus, {
            gid: $this.gid,
            id: id,
            status: status,
            sort: sort
          })
          .then(function (e) {
            switch (status) {
              case 1:
                window['arrayAdd']($this.ginfo.rule_ids, id);
                window['arrayReduce']($this.ginfo.ban_rule_ids, id);
                break;
              case 2:
                window['arrayReduce']($this.ginfo.rule_ids, id);
                window['arrayAdd']($this.ginfo.ban_rule_ids, id);
                break;
              default:
                window['arrayReduce']($this.ginfo.rule_ids, id);
                window['arrayReduce']($this.ginfo.ban_rule_ids, id);
            }
          })
          .catch(function (err) {
            $this.$warMsg(err);
          })
          .finally(function () {});
        },
        getGroup: function () {
          $this.loadGroup = true;
          $this
          .$api(apis.sysGroupInfo, { id: $this.gid })
          .then(function (e) {
            $this.ginfo = e.data;
            $this.getRules();
          })
          .catch(function (err) {
            // $this.$back();
            $this.gid = 0;
          })
          .finally(function () {
            $this.loadGroup = false;
          });
        },
        getRules: function () {
          this.updateGstatus = [];
          var data = {};
          if (this.ml_searchKey) {
            data['key'] = this.ml_searchKey;
          }
          $this.ml_listsLoading = true;
          $this.tmpData = [];
          this.$api(apis.sysRuleLists, data)
          .then(function (v) {
            var data = v.data;
            data.map(function (e) {
              if ($this.gid) {
                e.gstatus =
                  $this.ruleIds.indexOf(e.id) >= 0
                    ? 1
                    : $this.banRuleIds.indexOf(e.id) >= 0
                    ? 2
                    : 3;
              }
              e.__loading = false;
              e._isEdit = false;
              e._isPopover = false;
              return e;
            });
            $this.ml_data = data;
          })
          .catch(function (e) {
            $this.$warMsg(e);
          })
          .finally(function () {
            $this.ml_listsLoading = false;
          });
        },
        ml_reloadLists: function () {
          $this.ml_searchKey = '';
          $this.loadGroup = false;
          $this.getRules();
        },
        searchRow: function () {
          this.getRules();
        }
      }
    },
    [],
    '/index'
  );
</script>
