<template>
  <!--<div class="header">
    <el-button @click='logout'>退出登录</el-button>
  </div>-->
  <el-container :class='isCollapse?"is-collapse":"not-collapse"'>
    <el-aside width="auto" class="header-logo tap" @click.native.prevent="useHandleNav">
      <img src="./static/images/logo.svg" alt="Logo">
    </el-aside>
    <el-aside v-if="isCollapse" width="30px" class="nav-top-collapse-icon">
      <i @click.prevent="handleNav" class="icon-arrowhead-right-outl tap"></i>
      <!--      <i v-else @click.prevent="handleNav" class="icon-arrowhead-left-outli tap"></i>-->
    </el-aside>
    <el-main class="header-nav">
      <div class="tap">
        <el-dropdown trigger="click" class="user-menu" @command="useClickMenu">
          <div>
            <el-avatar fit="cover" icon="el-icon-user-solid" class="header-avatar" :src="avatar">
              {{ nickname }}
            </el-avatar>
            <i class="el-icon-caret-bottom el-icon--right"></i>
            <!-- <span class="header-name text-nowrap">{{ nickname }}</span> -->
          </div>
          <el-dropdown-menu slot="dropdown">
            <el-dropdown-item command="useUser">
              <i class="icon-person"></i> <span>账号信息</span>
            </el-dropdown-item>
            <el-dropdown-item command="useLogs">
              <i class="icon-award-outline"></i> <span>查看消息</span>
            </el-dropdown-item>
            <el-dropdown-item command="useEditPassword">
              <i class="icon-unlock-outline"></i> <span>修改密码</span>
            </el-dropdown-item>
            <el-dropdown-item command="useClear" divided>
              <i class="icon-charging-outline"></i> <span>清除缓存</span>
            </el-dropdown-item>
            <el-dropdown-item command="logout">
              <i class="icon-log-out"></i> <span>退出登录</span>
            </el-dropdown-item>
          </el-dropdown-menu>
        </el-dropdown>
      </div>
      <div @click="useLogs" class="tap" v-show="unreadMessageCount">
        <el-badge :value="unreadMessageCount" class="tip-msg">
          <i class="icon-email-outline"></i>
        </el-badge>
      </div>
      <!-- <el-dropdown class="tap" trigger="click">
        <span class="el-dropdown-nav">
          <i class="icon-options-"></i>
        </span>
        <el-dropdown-menu slot="dropdown">
          <el-dropdown-item><i class="icon-options-"></i><span>系统设置</span></el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>-->
    </el-main>
  </el-container>
</template>
<script>
let MessageCountTime;
const {useStore, useRouter, useTip} = hook;
const {reactive, ref, watch, computed, onMounted, onBeforeUnmount} = vue;
const {user: userApi, useRequestWith} = api;
export default {
  name: 'headerView',
  props: {
    logout: Function,
    default: () => {
    },
    isCollapse: {
      type: Boolean,
      default: false
    }
  },
  setup(prop, ctx) {
    onMounted(() => {// mounted
      getUnreadMessageCount();
      window['SysGetUnreadMessageCount'] = getUnreadMessageCount;
    })

    onBeforeUnmount(() => {// beforeDestroy
      setTimeout(() => {
        clearTimeout(MessageCountTime);
      });
    })

    const nickname = computed(() => {
      return useStore(ctx).getters.nickname;
    })

    const avatar = computed(() => {
      return useStore(ctx).getters.avatar;
    })

    const unreadMessageCount = computed(() => {
      return useStore(ctx).state.unreadMessageCount;
    })

    const unreadMessageCountApi = useRequestWith(userApi.unreadMessageCount, {manual: true});

    async function getUnreadMessageCount() {
      clearTimeout(MessageCountTime);

      const [data, err] = await unreadMessageCountApi.run();
      if (err) {
        useTip().message('warning', err);
      } else {
        if (useStore(ctx).state.unreadMessageCount !== data.count) {
          useStore(ctx).commit('setUnreadMessageCount', data.count)
        }
      }
      if (useStore(ctx).state.token && window['messagesRegularly']) {
        MessageCountTime = setTimeout(getUnreadMessageCount, window['messagesRegularly']);
      }
    }


    function useClickMenu(command) {
      // this.logout()
      if (this[command]) {
        this[command]();
      }
      ctx.emit('click', command)
    }

    function useUser() {
      useRouter(ctx).replace('/main/user/lists?key=' + useStore(ctx).state.user.username + '&v=' + +new Date())
    }

    function useLogs() {
      useRouter(ctx).replace('/main/user/logs?v=' + +new Date())
    }

    function useClear() {
      VueRun.clearCache();
      useTip().message('success', '清除缓存成功');
    }

    function useHandleNav() {
      ctx.emit('handle')
    }

    return {
      avatar,
      nickname,
      unreadMessageCount,
      getUnreadMessageCount,
      useUser,
      useLogs,
      useClear,
      useClickMenu,
      useHandleNav
    }
  }
};
</script>

<style scoped>
.header-logo {
  padding : 0 0 0 5px;
  text-align : center;
  color : #2C6EB1;
  font-weight : bold;
  letter-spacing : 2px;
}

.is-collapse .header-logo.tap {
  width : 52px !important;
  overflow : hidden;
}

.header-logo img {
  height : 42px;
  vertical-align : middle;
  padding-bottom : 4px;
  width : 164px;
}

.is-collapse .header-logo img {
  max-width : inherit;
}

.header-nav {
  padding : 0;
  height : 60px;
  display : -webkit-box;
  display : -ms-flexbox;
  display : flex;
  overflow : hidden;
  -ms-flex-direction : row-reverse;
  -webkit-box-orient : horizontal;
  -webkit-box-direction : reverse;
  flex-direction : row-reverse;
}

.header-nav > div {
  width : 60px;
  text-align : center;
  padding : 0 10px;
}

.header-nav > div:hover {
  /*background-color: #eaf1f7;*/
  background-color : #28344A;
}

.tip-msg {
  font-size : 30px;
  line-height : 20px;
}

.el-dropdown-nav {
  font-size : 30px;
  line-height : 56px;
}

.header-avatar {
  width : 40px;
  height : 40px;
  overflow : hidden;
  vertical-align : middle;
  border-radius : 50%;
  margin : 10px 5px 0 7px;
}

.el-dropdown-menu.el-popper {
  white-space : nowrap;
  margin-top : 5px !important;
}

.header-nav .user-menu.el-dropdown {
  height : 60px;
  line-height : 25px;
  display : block;
  width : 65px;
  text-align : center;
  float : right;
  box-sizing : border-box;
  overflow : hidden;
}

.header-nav .el-icon--right {
  position : absolute;
  right : -3px;
  top : 37px;
  color : #E3E4E4;
}

.header-name {
  display : block;
  font-size : 12px;
  color : #999999;
  border-top : 1px solid #E4E8EB;
  margin-top : 5px;
  padding-top : 2px;
  line-height : 12px;
}

.nav-top-collapse-icon {
  height : 60px;
  text-align : center;
  font-size : 25px;
  -webkit-animation : opacity 2s infinite;
  animation : opacity 2s infinite;
}

</style>
