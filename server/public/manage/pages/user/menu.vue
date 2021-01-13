<template>
  <div class="page-user-menu">
    <div class="view-title float-clear">
      <div class="view-title-right">
        <el-form @submit.prevent.stop.native inline class="tip-top">
          <!--          <el-form-item>-->
          <!--            <el-button type="success" size="small" icon="el-icon-plus" @click="addMenu">增加菜单</el-button>-->
          <!--          </el-form-item>-->
        </el-form>
      </div>
    </div>
    <div class="tip-area">
      <div>
        温馨提示: 角色个性化菜单设置请前往 [
        <router-link to="/main/user/rules">权限设置</router-link>
        ]
      </div>
      <div class="btn-top">
        <el-button type="primary" size="mini" @click="addMenu" icon="el-icon-plus">增加菜单</el-button>
      </div>
    </div>

    <fieldset>
      <legend v-text="title"></legend>
      <div class="out-box">
        <!--  左边内容  -->
        <div class="block left-box">
          <el-alert title="菜单目录" type="success" center :closable="false"></el-alert>
          <aside class="center" :aria-label="title">
            <div class="treeBox">
              <el-tree :data="treeData" :props="defaultProps" node-key="id" default-expand-all
                       @node-drag-start="handleDragStart" @node-drag-enter="handleDragEnter"
                       @node-drag-leave="handleDragLeave" @node-drag-over="handleDragOver"
                       @node-drag-end="handleDragEnd" @node-drop="handleDrop" draggable :allow-drop="allowDrop"
                       :allow-drag="allowDrag">
                <div class="custom-tree-node" slot-scope="{ node, data }">
                  <div>
                    <span :class="data.icon"></span>
                    <span>
                      {{ data.title }}------<span style="color: #409eff">{{ data.index }}</span>
                    </span>
                  </div>
                  <span>
                      <template v-if="data.child !== null">
                          <el-button type="text" size="mini" @click.stop="append(data)">增加</el-button>
                      </template>
                      <template v-if="data.title !== '首页'">
                          <el-button type="text" size="mini" @click.stop="edit(node, data)">编辑</el-button>
                          <el-button type="text" size="mini"
                                     @click.stop="remove(node, data)">删除</el-button>
                      </template>
                  </span>
                </div>
              </el-tree>
            </div>
          </aside>
        </div>

        <!--  右边内容  -->
        <div class="right-box">
          <el-alert title="添加（编辑）菜单" type="warning" center :closable="false"></el-alert>
          <el-form label-width="100px" class="rightForm">
            <el-form-item label="菜单名称">
              <el-input class="my-input" v-model="addMenuData.title" placeholder="请输入内容" size="small"></el-input>
            </el-form-item>

            <el-form-item label="菜单路径">
              <el-input class="my-input" v-model="addMenuData.menuPath" placeholder="请输入内容" size="small"></el-input>
            </el-form-item>
            <el-form-item label="icon值">
              <!-- <el-input class="my-input" v-model="addMenuData.iconVal" placeholder="请输入内容" size="small"></el-input> -->
              <el-select v-model="addMenuData.iconVal" placeholder="请选择" size="small" clearable>
                <el-option v-for="item in iconList" :key="item.font_class" :label="item.font_class"
                           :value="item.font_class">
                  <span style="float: left">{{ item.font_class }}</span>
                  <span class="icon font_family" :class="['icon-'+item.font_class]"></span>
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="面包屑显示">
              <menu-select ref="breadShowSelectRef"></menu-select>
            </el-form-item>
            <el-form-item label="面包屑可点击">
              <menu-select ref="breadClickSelectRef"></menu-select>
            </el-form-item>
            <el-form-item label="导航栏显示">
              <menu-select ref="navigationSelectRef"></menu-select>
            </el-form-item>
            <el-form-item label="上级菜单(默认顶级)">
              <menu-select ref="selectMenuRef"></menu-select>
              <!--          <el-cascader-->
              <!--            :options="treeData"-->
              <!--            v-model="addMenuData.menuVal"-->
              <!--            :props="{ checkStrictly: true,label:'title',value:'id' }"-->
              <!--            clearable></el-cascader>-->
              <!--        </el-form-item>-->

              <!--        <el-form-item label="上级菜单">-->
              <!--          <components_el-select></components_el-select>-->
            </el-form-item>

            <div class="btn-box">
              <el-button type="primary" @click="confirmBtn" size="mini" icon="el-icon-check">
                {{ addMenuData.addAndEditTips }}
              </el-button>
            </div>
          </el-form>
        </div>
      </div>
    </fieldset>

    <!--    增加菜单-->
    <!--    <el-dialog-->
    <!--      title="添加菜单"-->
    <!--      :close-on-click-modal	='false'-->
    <!--      :visible.sync="addMenuData.dialogVisible"-->
    <!--      width="30%"-->
    <!--      :before-close="cancelW">-->
    <!--      <el-form label-width="100px">-->
    <!--        <el-form-item label="菜单名称">-->
    <!--          <el-input v-model="addMenuData.title" placeholder="请输入内容" size="small"></el-input>-->
    <!--        </el-form-item>-->
    <!--        <el-form-item label="菜单路径">-->
    <!--          <el-input v-model="addMenuData.menuPath" placeholder="请输入内容" size="small"></el-input>-->
    <!--        </el-form-item>-->
    <!--        <el-form-item label="icon值">-->
    <!--          <el-input v-model="addMenuData.iconVal" placeholder="请输入内容" size="small"></el-input>-->
    <!--        </el-form-item>-->
    <!--        <el-form-item label="面包屑显示">-->
    <!--          <components_el-select ref="breadShowSelect"></components_el-select>-->
    <!--        </el-form-item>-->
    <!--        <el-form-item label="面包屑可点击">-->
    <!--          <components_el-select ref="breadClickSelect"></components_el-select>-->
    <!--        </el-form-item>-->
    <!--        <el-form-item label="导航栏显示">-->
    <!--          <components_el-select ref="navigationSelect"></components_el-select>-->
    <!--        </el-form-item>-->
    <!--        <el-form-item label="上级菜单(默认顶级)" v-if="addMenuData.isMenuShow">-->
    <!--          <components_el-select ref="selectMenu"></components_el-select>-->

    <!--&lt;!&ndash;          <el-cascader&ndash;&gt;-->
    <!--&lt;!&ndash;            :options="treeData"&ndash;&gt;-->
    <!--&lt;!&ndash;            v-model="addMenuData.menuVal"&ndash;&gt;-->
    <!--&lt;!&ndash;            :props="{ checkStrictly: true,label:'title',value:'id' }"&ndash;&gt;-->
    <!--&lt;!&ndash;            clearable></el-cascader>&ndash;&gt;-->
    <!--&lt;!&ndash;        </el-form-item>&ndash;&gt;-->

    <!--&lt;!&ndash;        <el-form-item label="上级菜单">&ndash;&gt;-->
    <!--&lt;!&ndash;          <components_el-select></components_el-select>&ndash;&gt;-->
    <!--        </el-form-item>-->

    <!--      </el-form>-->
    <!--      <span slot="footer" class="dialog-footer">-->
    <!--    <el-button @click="cancelW" size="small">取 消</el-button>-->
    <!--    <el-button type="primary" @click="confirmBtn" size="small">确 定</el-button>-->
    <!--  </span>-->
    <!--    </el-dialog>-->
  </div>
</template>
<script>
const {ref, reactive, computed, onMounted, watch, onBeforeUnmount} = vue;
const {useRouter, useStore, useCache, useTip, useLoading, useConfirm} = hook;
const {sys: sysApi, useRequestWith} = api;
const {useInitTitle} = util;

export default {
  components: {
    menuSelect: VueRun('components/menu-select.vue')
  },
  setup(prop, ctx) {
    const {root} = ctx;

    onMounted(() => {
      useGetMenuList();
      getIcons();
    })

    let title = ref('菜单管理');

    let iconList = ref([]);
    let value = ref("");
    let treeData = ref([]);
    let newList = ref([]);
    let defaultProps = ref({
      children: "child",
      label: "title",
    });
    let addMenuData = ref({
      title: "",
      iconVal: "",
      isEdit: "add",
      selectId: "",
      editData: "",
      deleteKeepData: [],
      menuPath: "",
      insertParData: "",
      addAndEditTips: "增 加"
    });

    let selectMenuRef = ref(null)
    let breadShowSelectRef = ref(null)
    let breadClickSelectRef = ref(null)
    let navigationSelectRef = ref(null)

    function addMenu() {
      clearData();
      addMenuData.value.isEdit = "add";
      selectMenuRef.value.isDisabled = false;
      addMenuData.value.addAndEditTips = "增 加";
    }

    function append(data) {
      clearData();
      addMenuData.value.addAndEditTips = "增 加";
      selectMenuRef.value.isDisabled = true;
      addMenuData.value.isEdit = "insert";
      addMenuData.value.insertParData = data;
    }

    function edit(node, data) {
      addMenuData.value.addAndEditTips = "编 辑";
      addMenuData.value.isEdit = "edit";

      let title = data.title;
      let iconVal = data.icon.substring(5, data.icon.length);
      let breadcrumb = data.breadcrumb;
      let real = data.real;
      let show = data.show;
      let path = data.index;

      addMenuData.value.title = title;
      addMenuData.value.iconVal = iconVal;
      addMenuData.value.selectId = data.id;
      addMenuData.value.menuPath = path;

      root.$nextTick(() => {
        breadShowSelectRef.value.value = breadcrumb;
        breadClickSelectRef.value.value = real;
        navigationSelectRef.value.value = show;
        selectMenuRef.value.isDisabled = true;
      })
    }

    const addMenuResult = useRequestWith(sysApi.sysMenuCreate, {manual: true});

    async function useAddMenuResult(title, index, icon, breadcrumb, real, show, pid) {
      const [data, err] = await addMenuResult.run({
        title: title,
        index: index,
        icon: icon,
        breadcrumb: breadcrumb,
        real: real,
        show: show,
        pid: pid
      });
      if (err) {
        useTip().message('warning', err);
      } else {
        useTip().message('success', '新增菜单成功');
      }

      useGetMenuList();
    }

    const editMenu = useRequestWith(sysApi.sysMenuUpdate, {manual: true});

    async function useEditMenu(id, title, index, icon, breadcrumb, real, show) {
      const [data, err] = await editMenu.run({
        id: id,
        title: title,
        index: index,
        icon: icon,
        breadcrumb: breadcrumb,
        real: real,
        show: show
      });
      if (err) {
        useTip().message('warning', err);
      } else {
        useTip().message('success', '更新完成');
      }

      addMenu();
      useGetMenuList();
    }

    function confirmBtn() {
      let isEdit = addMenuData.value.isEdit;
      let title = addMenuData.value.title;
      let menuPath = addMenuData.value.menuPath;
      let iconVal = "icon-" + addMenuData.value.iconVal;
      let breadcrumb = breadShowSelectRef.value.value;
      let real = breadClickSelectRef.value.value;
      let show = navigationSelectRef.value.value;
      if (title.trim() === "") {
        useTip().message('warning', "请输入菜单名称");
        return;
      } else if (menuPath === "") {
        useTip().message('warning', "请输入菜单路径");
        return;
      } else if (breadcrumb === "") {
        useTip().message('warning', "请选择面包屑是否显示");
        return;
      } else if (real === "") {
        useTip().message('warning', "请选择面包屑可否点击");
        return;
      } else if (show === "") {
        useTip().message('warning', "请选择导航栏是否显示");
        return;
      }

      if (isEdit === "edit") {
        //编辑
        let editId = addMenuData.value.selectId;
        useEditMenu(
            editId,
            title,
            menuPath,
            iconVal,
            breadcrumb,
            real,
            show
        );
      } else if (isEdit === "add") {
        let selectMenu = selectMenuRef.value.value;
        if (selectMenu.length === 0) {
          useAddMenuResult(
              title,
              menuPath,
              iconVal,
              breadcrumb,
              real,
              show,
              0
          );
        } else {
          //添加到次级目录
          let selectData = comebackItem(treeData.value, selectMenu);
          let newPath = selectData.index + "/" + menuPath;
          useAddMenuResult(
              title,
              newPath,
              iconVal,
              breadcrumb,
              real,
              show,
              selectMenu
          );
        }
      } else {
        let insertParData = addMenuData.value.insertParData;
        //插入目录
        let selectMenu = insertParData.id;
        let newPath = insertParData.index + "/" + menuPath;
        useAddMenuResult(
            title,
            newPath,
            iconVal,
            breadcrumb,
            real,
            show,
            selectMenu
        );
      }
      clearData();
    }

    function comebackItem(data, id) {
      for (let i in data) {
        if (data[i].id === id) {
          return data[i];
        }
        let isChildren = data[i].children;
        if (isChildren) {
          let come = comebackItem(isChildren, id);
          if (come !== undefined) {
            return come;
          }
        }
      }
    }

    function findItem(data, id) {
      let length = data.length;
      for (let i = 0; i < length; i++) {
        if (data[i].id === id) {
          data[i].title = addMenuData.value.title;
          data[i].icon = "icon-" + addMenuData.value.iconVal;
          data[i].index = addMenuData.value.menuPath;
          data[i].breadcrumb = breadShowSelectRef.value;
          data[i].real = breadClickSelectRef.value;
          data[i].show = navigationSelectRef.value;
          useTip().message('success', '编辑完成');
          clearData();
        } else {
          let child = data[i].child;
          if (child) {
            findItem(child, id);
          }
        }
      }
    }

    function cancelW() {
      clearData();
    }

    function clearData() {
      let isEdit = addMenuData.value.isEdit;
      addMenuData.value.title = addMenuData.value.iconVal = addMenuData.value.menuPath = breadShowSelectRef.value.value = breadClickSelectRef.value.value = navigationSelectRef.value.value = "";
      if (isEdit !== "edit" && isEdit !== "insert") {
        selectMenuRef.value.value = "";
      } else {
        addMenuData.value.selectId = addMenuData.value.insertParData = "";
      }
    }

    let options = [{value: 1, label: "启用"}, {value: 0, label: "禁用"}];
    watch(breadShowSelectRef, (val) => {
      val.options = options;
    })
    watch(breadClickSelectRef, (val) => {
      val.options = options;
    })
    watch(navigationSelectRef, (val) => {
      val.options = options;
    })

    function remove(node, data) {
      let str = '是否删除 "' + data.title + '" 菜单';
      useConfirm().warning('提示', str, function () {
        useRundeleteMenu(data.id);
      }, function () {
      });
    }

    const rundeleteMenu = useRequestWith(sysApi.sysMenuDelete, {manual: true});

    async function useRundeleteMenu(id) {
      const [data, err] = await rundeleteMenu.run({id: id});
      if (err) {
        useTip().message('warning', err);
      } else {
        useTip().message('success', '删除菜单成功');
      }

      useGetMenuList();
    }

    function handleDragStart(node, ev) {
      // console.log('drag start', node);
    }

    function handleDragEnter(draggingNode, dropNode, ev) {
      // console.log('tree drag enter: ', dropNode.title);
    }

    function handleDragLeave(draggingNode, dropNode, ev) {
      // console.log('tree drag leave: ', dropNode.title);
    }

    function handleDragOver(draggingNode, dropNode, ev) {
      // console.log('tree drag over: ', dropNode.title);
    }

    function handleDragEnd(draggingNode, dropNode, dropType, ev) {
      if (dropType === "inner") {
        //获取到自己的路径
        let str = draggingNode.data.index;
        let index = str.lastIndexOf("/");
        str = str.substring(index + 1, str.length);
        //新的路径
        let newPath = dropNode.data.index + "/" + str;
        draggingNode.data.index = newPath;
        newList.value = [];
        setMenuSort(treeData.value, false);
        useSortMenu(JSON.stringify(newList.value));
      } else if (dropType === "before" || dropType === "after") {
        //获取到拖拽到位置的路径
        let str = dropNode.data.index;
        let index = str.lastIndexOf("/");
        str = str.substring(0, index + 1);
        //获取到自己的路径
        let myStr = draggingNode.data.index;
        let myIndex = myStr.lastIndexOf("/");
        myStr = myStr.substring(myIndex + 1, myStr.length);
        //新的路径
        let newPath = str + myStr;
        draggingNode.data.index = newPath;
        newList.value = [];
        setMenuSort(treeData.value, false);
        useSortMenu(JSON.stringify(newList.value));
      }
    }

    function handleDrop(draggingNode, dropNode, dropType, ev, data) {
      // console.log('tree drop: ', dropNode.title, dropType);
    }

    function allowDrop(draggingNode, dropNode, type) {
      if (draggingNode.label === '首页') {
        return;
      }

      if (
          draggingNode.data.child &&
          draggingNode.data.child !== undefined &
          draggingNode.data.child.length > 0
      ) {
        //顶层禁止拖拽到二级层限制
        if (type === "inner") {
          return;
        }
        if (dropNode.data.child === undefined) {
          return;
        }
      } else {
        //二级层禁止拖拽到三级层限制
        if (type === "inner") {
          if (dropNode.data.child === undefined) {
            return;
          }
        }
      }
      if (dropNode.data.title === "二级 3-1") {
        return type !== "inner";
      } else {
        return true;
      }
    }

    function allowDrag(draggingNode) {
      if (draggingNode.label === '首页') {
        useTip().message('warning', "首页禁止执行任何操作");
        return;
      }
      return draggingNode.data.title.indexOf("三级 3-2-2") === -1;
    }

    const sortMenu = useRequestWith(sysApi.sysMenuSort, {manual: true});

    async function useSortMenu(menuData) {
      const [data, err] = await sortMenu.run({menu: menuData});
      if (err) {
        useTip().message('warning', err);
      } else {
        useTip().message('success', '拖拽成功');
      }
    }

    function setMenuSort(treeData, isChild, isNum) {
      for (let i in treeData) {
        if (treeData[i].child && treeData[i].child.length > 0) {
          let child = treeData[i].child;
          newList.value.push({
            id: treeData[i].id,
            child: []
          });
          setMenuSort(treeData[i].child, true, i);
        } else {
          if (isChild) {
            (newList.value)[isNum].child.push({
              id: treeData[i].id
            })
          } else {
            newList.value.push({
              id: treeData[i].id
            });
          }
        }
      }
    }

    function getIcons() {
      VueRun.httpRequest(assetsCdn + '/pages/demo/iconfont.json').then(function (e) {
        if (typeof e === 'string') {
          e = JSON.parse(e)
        }
        iconList.value = e.glyphs;
      });
    }

    const getMenuList = useRequestWith(sysApi.sysUserMenu, {manual: true});
    async function useGetMenuList() {
      const [data, err] = await getMenuList.run();
      if (err) {
        useTip().message('warning', err);
      } else {
        let useData = data;
        treeData.value = data;

        root.$nextTick(() => {
          let newTreeData = [];
          let treeData = useData;
          for (let index in treeData) {
            if (treeData[index].title !== '首页') {
              let obj = {
                value: treeData[index].id,
                label: treeData[index].title
              };
              newTreeData.push(obj);
            }
          }
          selectMenuRef.value.options = newTreeData;
        })
      }
    }

    return {
      title,
      addMenu,
      treeData,
      defaultProps,
      allowDrop,
      allowDrag,
      handleDragStart,
      handleDragEnter,
      handleDragLeave,
      handleDragOver,
      handleDragEnd,
      handleDrop,
      addMenuData,
      iconList,
      confirmBtn,
      append,
      edit,
      remove,
      selectMenuRef,
      breadShowSelectRef,
      breadClickSelectRef,
      navigationSelectRef,
    };
  }
};
</script>

<style scoped>
.page-user-menu .tree-placeholder {
  display: inline-block;
  width: 20px;
  line-height: 20px;
  height: 20px;
  text-align: center;
  margin-right: 3px;
}

.custom-tree-node {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.out-box {
  width: 100%;
  height: auto;
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
}

.left-box,
.right-box {
  width: 50%;
}

.right-box {
  padding: 20px 20px 0 30px;
  box-sizing: border-box;
  flex: 1;
}

.block {
  padding: 20px 20px 0 30px;
  text-align: center;
  /* border-right: 1px solid #eff2f6; */
  flex: 1;
}

.btn-box {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 10px;
}

.labelName {
  margin: 10px auto;
  display: block;
}

.el-input--small .el-input__inner {
  width: 300px;
}

.el-form-item__label {
  width: 138px !important;
}

.my-input {
  width: 56%;
  min-width: 300px;
}

.rightForm {
  padding: 30px 0 30px 0;
}

.el-form-item__content {
  margin-left: 0px !important;
}

.itemBox {
  display: flex;
  justify-content: center;
  align-items: center;
}

.el-form-item {
  display: flex;
  justify-content: center;
  align-items: center;
}

.treeBox {
  margin: 30px 0;
}

.btn-top {
  display: flex;
  justify-content: flex-end;
}

.tip-area {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

fieldset {
  min-width: 450px;
}

.el-scrollbar .font_family {
  font-family: "font_family" !important;
  font-size: 16px;
  font-style: normal;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
</style>
