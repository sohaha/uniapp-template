<style>
  .page-user-menu .tree-placeholder {
    display: inline-block;
    width: 20px;
    line-height: 20px;
    height: 20px;
    text-align: center;
    margin-right: 3px;
  }

</style>
<template>
  <div class="page-user-menu">
    <div class="view-title float-clear">
      <div class="view-title-right">
        <el-form @submit.prevent.stop.native inline class="tip-top">
          <el-form-item>
            <el-button type="primary" size="mini" icon="el-icon-plus">添加</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <div class="tip-area">温馨提示: 角色个性化菜单设置请前往 [ <a v-link='"/main/user/rules"'>权限设置</a> ]</div>
    <fieldset>
      <legend v-text="title"></legend>
      <aside class="center" :aria-label="title">
        <el-table :indent="0" :tree-props="{children: 'child'}" default-expand-all row-key="id" :data="ml_data" style="width: 100%" size="mini">
          <el-table-column label="名称" min-width="150">
            <template slot-scope="scope">
              <div v-if="!scope.row.child||scope.row.child.length<=0" class="tree-placeholder"></div>
              <span>
                <i :class="scope.row.icon"></i>
                {{scope.row.title}}
              </span>
            </template>
          </el-table-column>
          <el-table-column prop="index" label="路径" show-overflow-tooltip max-width="200"></el-table-column>
          <el-table-column prop="date" label="类型"></el-table-column>
          <el-table-column prop="date" label="更新"></el-table-column>
          <el-table-column label="操作" width="200">
            <template slot-scope="scope">{{scope.row.__||'--'}}</template>
          </el-table-column>
        </el-table>
        <!-- <el-tree default-expand-all show-checkbox :data="data" :props="defaultProps">
          <span class="custom-tree-node" slot-scope="{ node, data }">
            <span>
              {{ node.label }}
              {{data.id}}
            </span>
            <span>
              <el-button type="text" size="mini">添加</el-button>
              <el-button type="text" size="mini">删除</el-button>
            </span>
          </span>
        </el-tree>-->
      </aside>
    </fieldset>
  </div>
</template>
<script>
  Spa.define(
    {
      mixins: [mixinLists, initTitle],
      data: function () {
        return {
          title: '菜单管理(开发中)'
        };
      },
      mounted: function () {},
      computed: {},
      init: function (query, search) {},
      methods: {
        getLists: function () {
          var data = { page: this.ml_page, pagesize: this.ml_pagesize };
          if (this.ml_searchKey) {
            data['key'] = this.ml_searchKey;
          }
          $this.ml_listsLoading = true;
          this.$api(apis.sysMenu, data)
          .then(function (v) {
            console.log(v);
            // todo test 接口地址undefined是不会真实发起请求所以这里 v 是不会有数据
            v = {
              data: {
                items: $this.$store.getters.menus,
                page: { total: 40 }
              }
            };
            // test end
            var data = v.data.items || [];
            data.forEach(function (e, index, values) {
              values[index]._isEdit = false;
              values[index]._isPopover = false;
            var data = v.data;
            data.map(function (e) {
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
        }
      }
    },
    ['/components/demo'],
    '/index'
  );
</script>
