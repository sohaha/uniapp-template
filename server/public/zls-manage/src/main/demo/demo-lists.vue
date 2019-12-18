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
    <div class="tip-area">温馨提示: 这个一个示例页面</div>
    <fieldset>
      <legend>{{title}}</legend>
      <aside v-loading="ml_listsLoading">
        <el-table :data="ml_data" style="width: 100%" size="mini">
          <el-table-column show-overflow-tooltip label="标题" width="170">
            <template slot-scope="scope">
              <div>{{ scope.row.title || ' - ' }}</div>
            </template>
          </el-table-column>
          <el-table-column prop="date" label="日期"></el-table-column>
          <el-table-column label="操作" width="200">
            <template slot-scope="scope">{{scope.row.__||'--'}}</template>
          </el-table-column>
        </el-table>
        <div class="tip-page" v-if="!!ml_pagetotal">
          <el-pagination
            :current-page.sync="ml_page"
            @size-change="ml_sizeChange"
            @current-change="ml_currentChange"
            background
            layout="prev, pager, next, sizes, total"
            :total="ml_pagetotal"
            :page-size.sync="ml_pagesize"
          ></el-pagination>
        </div>
      </aside>
    </fieldset>
  </div>
</template>
<script>
Spa.define(
  {
    name: "DemoViewLits",
    mixins: [mixinLists, initTitle], // mixinLists 内置的列表加载
    data: function() {
      return {};
    },
    computed: {},
    init: function(query, search) {
      $this.ml_pagesize = 10;
    },
    mounted: function() {},
    methods: {
      getLists: function() {
        var data = { page: this.ml_page, pagesize: this.ml_pagesize };
        if (this.ml_searchKey) {
          data["key"] = this.ml_searchKey;
        }
        $this.ml_listsLoading = true;
        this.$api(undefined, data)
          .then(function(v) {
            // todo test 接口地址undefined是不会真实发起请求所以这里 v 是不会有数据
            v = {
              data: {
                items: [
                  { title: "demo1", date: "2020-01-01" },
                  { title: "demo2", date: "2020-01-01" },
                  { title: "demo3", date: "2020-01-01" }
                ],
                page: { total: 40 }
              }
            };
            // test end
            var data = (v.data.items || []).map(function(e) {
              e._isEdit = false;
              e._isPopover = false;
              return e;
            });
            var page = v.data.page;
            $this.ml_getLists(data, page);
          })
          .catch(function(e) {
            $this.$warMsg(e);
          })
          .finally(function() {
            $this.ml_listsLoading = false;
          });
      }
    }
  },
  [],
  "/index"
);
</script>
