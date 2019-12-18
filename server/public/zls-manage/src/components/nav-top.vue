<style>
  .header {
    /*background-color: #ffffff;*/
    color: #fff;
    /*color: #333333;*/
    line-height: 60px;
    padding: 0 10px;
    z-index: 9;
    /*box-shadow: 0 1px 4px 0 #c0c4cc;*/
  }

  .header-logo {
    padding: 0 0 0 5px;
    text-align: center;
    color: #2c6eb1;
    font-weight: bold;
    letter-spacing: 2px;
  }

  .is-collapse .header-logo.tap {
    width: 52px !important;
    overflow: hidden;
  }

  .header-logo img {
    height: 42px;
    vertical-align: middle;
    padding-bottom: 4px;
  }

  .is-collapse .header-logo img {
    max-width: inherit;
  }

  .header-nav {
    padding: 0;
    height: 60px;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    overflow: hidden;
    -ms-flex-direction: row-reverse;
    -webkit-box-orient: horizontal;
    -webkit-box-direction: reverse;
    flex-direction: row-reverse;
  }

  .header-nav > div {
    width: 60px;
    text-align: center;
    padding: 0 10px;
  }

  .header-nav > div:hover {
    /*background-color: #eaf1f7;*/
    background-color: #28344a;
  }

  .tip-msg {
    font-size: 30px;
    line-height: 20px;
  }

  .el-dropdown-nav {
    font-size: 30px;
    line-height: 56px;
  }

  .header-avatar {
    width: 40px;
    height: 40px;
    overflow: hidden;
    vertical-align: middle;
    border-radius: 50%;
    margin: 10px 5px 0 7px;
  }

  .el-dropdown-menu.el-popper {
    white-space: nowrap;
    margin-top: 5px !important;
  }

  .header-nav .user-menu.el-dropdown {
    height: 60px;
    line-height: 25px;
    display: block;
    width: 65px;
    text-align: center;
    float: right;
    box-sizing: border-box;
    overflow: hidden;
  }

  .header-nav .el-icon--right {
    position: absolute;
    right: -3px;
    top: 37px;
    color: #e3e4e4;
  }

  .header-name {
    display: block;
    font-size: 12px;
    color: #999999;
    border-top: 1px solid #e4e8eb;
    margin-top: 5px;
    padding-top: 2px;
    line-height: 12px;
  }

  .nav-top-collapse-icon {
    height: 60px;
    text-align: center;
    font-size: 25px;
    -webkit-animation: opacity 2s infinite;
    animation: opacity 2s infinite;
  }
</style>
<template>
  <el-container :class='isCollapse?"is-collapse":"not-collapse"'>
    <el-aside width="auto" class="header-logo tap" @click.native.prevent="handleNav">
      <img src="./src/images/logo.svg" alt="Logo">
    </el-aside>
    <el-aside v-if="isCollapse" width="30px" class="nav-top-collapse-icon">
      <i @click.prevent="handleNav" class="icon-arrowhead-right-outl tap"></i>
      <!--      <i v-else @click.prevent="handleNav" class="icon-arrowhead-left-outli tap"></i>-->
    </el-aside>
    <el-main class="header-nav">
      <div class="tap">
        <el-dropdown trigger="click" class="user-menu" @command="clickMenu">
          <div>
            <el-avatar fit="cover" icon="el-icon-user-solid" class="header-avatar" :src="avatar">{{nickname}}</el-avatar>
            <i class="el-icon-caret-bottom el-icon--right"></i>
            <!-- <span class="header-name text-nowrap">{{ nickname }}</span> -->
          </div>
          <el-dropdown-menu slot="dropdown">
            <el-dropdown-item command="user">
              <i class="icon-person-outline"></i> <span>账号信息</span>
            </el-dropdown-item>
            <el-dropdown-item command="logs">
              <i class="icon-award-outline"></i> <span>查看消息</span>
            </el-dropdown-item>
            <el-dropdown-item command="editPassword">
              <i class="icon-unlock-outline"></i> <span>修改密码</span>
            </el-dropdown-item>
            <el-dropdown-item command="clear" divided>
              <i class="icon-charging-outline"></i> <span>清除缓存</span>
            </el-dropdown-item>
            <el-dropdown-item command="logout">
              <i class="icon-log-out"></i> <span>退出登录</span>
            </el-dropdown-item>
          </el-dropdown-menu>
        </el-dropdown>
      </div>
      <div @click="gologs" class="tap" v-show="unreadMessageCount">
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
  var that, MessageCountTime;
  Spa.define({
    data: function () {
      return {
        passViewDialogVisible: false
      };
    },
    created: function () {
      that = this;
    },
    props: {
      isCollapse: {
        type: Boolean,
        default: false
      }
    },
    computed: {
      nickname: function () {
        return this.$store.getters.nickname;
      },
      avatar: function () {
        return this.$store.getters.avatar;
      },
      unreadMessageCount: function () {
        return this.$store.state.unreadMessageCount;
      }
    },
    mounted: function () {
      this.getUnreadMessageCount();
      window['SysGetUnreadMessageCount'] = this.getUnreadMessageCount;
    },
    beforeDestroy: function () {
      setTimeout(function () {
        clearTimeout(MessageCountTime);
      });
    },
    methods: {
      gologs: function () {
        that.logs();
      },
      getUnreadMessageCount: function () {
        clearTimeout(MessageCountTime);
        that
        .$api(apis.sysUnreadMessageCount)
        .then(function (e) {
          if (that.$store.state.unreadMessageCount !== e.data.count)
            that.$store.commit('setUnreadMessageCount', e.data.count);
        })
        .finally(function () {
          if (that.$store.getters.token && window['messagesRegularly']) {
            MessageCountTime = setTimeout(that.getUnreadMessageCount, window['messagesRegularly']);
          }
        });
      },
      handleNav: function () {
        that.$emit('handle');
      },
      clickMenu: function (command) {
        if (that[command]) {
          that[command]();
        }
        that.$emit('click', command);
      },
      user: function () {
        that.$go(
          'user/@' + that.$store.state.user.username + '/lists?v=' + +new Date()
        );
      },
      logs: function () {
        that.$go('user/logs?v=' + +new Date());
      },
      client: function () {
        that.$go('user/client');
      },
      clear: function () {
        var isSpaV = function (str) {
          var endStr = '.v', d = str.length - endStr.length;
          return d >= 0 && str.lastIndexOf(endStr) === d;
        };
        for (var key in localStorage) {
          if (localStorage.hasOwnProperty(key) && isSpaV(key)) {
            localStorage.removeItem(key);
          }
        }
        that.$sucNotify('清除缓存成功');
      },
      logout: function () {
        that.$api(apis.logout).then(function (e) {
          window['SysGetUnreadMessageCount'] = null;
          that.$store.commit('setToken', '');
          that.$store.commit('setUser', {});
          Spa.go('/login');
        });
      }
    }
  });
</script>
