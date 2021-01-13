<template>
  <el-scrollbar
      :native="false"
      wrap-class="nav_scrollbar"
      wrap-style
      view-style
      view-class="view-box"
  >
    <el-menu
        :default-active='routePath'
        :router='true'
        :collapse="isCollapse"
        :text-color="textColor"
        :background-color='navBgColor'
        @select="handleSelect"
        active-text-color="#fff"
        unique-opened
    >
      <template v-for="(v, i) in state.nav">
        <el-submenu v-if="isChildren(v)&&v.meta.show !== false" :index="'nav-'+i" :key="'submenu1-'+i">
          <template slot="title">
            <i v-if="v.hasOwnProperty('icon')&&!!v.icon" :class="v.icon"></i>
            <i v-else class="icon-flag"></i>
            <span slot="title" class="menu_title">{{ v.name }}</span>
          </template>
          <el-menu-item v-for="(vv, ii) in v['children']" :index="vv.path" :key="'menuitem-'+ii"
                        v-show="vv.meta.show !== false">
            <i v-if="vv.hasOwnProperty('icon')&&!!vv.icon" :class="vv.icon"></i>
            <i v-else class="icon-flag"></i>
            <span>{{ vv.name }}</span>
          </el-menu-item>
        </el-submenu>
        <el-menu-item v-if="!isChildren(v)&&v.meta.show !== false" :index="v.path" :key="'submenu2-'+i">
          <i v-if="v.hasOwnProperty('icon')&&!!v.icon" :class="v.icon"></i>
          <i v-else class="icon-flag"></i>
          <span slot="title" class="menu_title">{{ v.name }}</span>
        </el-menu-item>
      </template>
    </el-menu>
  </el-scrollbar>
</template>
<script>
const {useRouter, useStore} = hook;
const {reactive, toRef, ref, watch, computed} = vue;

export default {
  name: 'navView',
  props: {
    isCollapse: Boolean,
    default: false
  },
  setup(prop, ctx) {
    const state = reactive({
      nav: []
    });

    const currentPath = computed(() => {
      return useRouter(ctx).route.path;
    })

    function activePath(arr) {
      for (let i = arr.length - 1; i > 0; i--) {
        if (
            i === arr.length - 1
            && currentPath === arr[i].path
            && arr[i]['meta']['show']
        ) {
          return arr[i].path;
        } else if (arr[i]['meta']['show']) {
          return arr[i].path;
        }
      }

      return '/main/main';
    }

    const routePath = computed(() => {
      return activePath(useRouter(ctx)['route']['matched']);
    });

    function handleSelect(e) {
      console.log(e);
    }

    const routerOk = computed(
        () => useStore(ctx).state.router.length > 0 && ctx.root.initSate
    );
    watch(
        () => routerOk.value,
        init => {
          if (init) {
            console.log('路由准备好了,可以设置导航了');

            state.nav = useStore(ctx).state.router[2]['children'];
          }
        },
        {immediate: true}
    );

    const isChildren = (v)=>{
      return v.meta.collapse&&v['children'];
    }
    return {
      routePath,
      handleSelect,
      isChildren,
      navBgColor: ref('#324157'),
      textColor: ref('#b3becd'),
      state
    };
  }
};
</script>

<style>
.el-menu {
  border: 0 !important;
}

.nav-left {
  overflow: hidden;
  -webkit-transition: width 0.35s cubic-bezier(0.55, 1.03, 0.54, 1.33);
  transition: width 0.35s cubic-bezier(0.55, 1.03, 0.54, 1.33);
}

.nav-left .menu_title {
  font-size: 16px;
  margin-left: 10px;
}

.nav-left .el-scrollbar__wrap {
  overflow-x: hidden;
}

.nav-left .el-menu {
  background: none !important;
}

.nav-left .el-menu-item,
.nav-left .el-submenu__title {
  height: 60px;
}

.el-menu--collapse .el-submenu__title i,
.el-menu-item > .el-tooltip > i {
  margin-right: 0;
}

.nav-left .el-menu-item:focus {
  background: none;
}

.nav_scrollbar {
  padding: 5px 0 10px 0;
  max-height: calc(100vh - 50px);
  box-sizing: border-box;
  margin-bottom: -10px !important;
}

.el-submenu__title,
.el-menu-item[role='menuitem'], .el-submenu__title:hover {
  background: none !important;
}

.not-collapse ul[role='menu'] .el-menu-item [class^='icon-'] {
  position: absolute;
  left: 30px;
}

ul[role='menu'] .el-menu-item {
  display: flex;
  justify-content: center;
  align-items: center;
}

ul[role='menu'] .el-menu-item::after {
  content: '';
  margin-right: 5px;
}

.el-menu-item {
  position: relative;
}
</style>
