<template>
  <el-container id="main">
    <el-header class="header" height="60px">
      <header-top aria-label="顶部导航" :is-collapse="isCollapse" @handle="useHandleNav" @click="useClickTopNav" :logout='logout'></header-top>
    </el-header>
    <el-container class="content">
      <div class="aside-nav-bg slow-motion" :class="!!asideNavOpen ? 'open' : ''" @click.self="asideNavOpen = !asideNavOpen"></div>
      <el-aside :class="asideClass">
        <nav-left :is-collapse='isCollapse'></nav-left>
      </el-aside>
      <el-container class="content-container">
        <el-main ref="content-box" class="content-box content-view-main" aria-label="页面内容">
          <nav-breadcrumb :class="{'wrap-breadcrumb': isWrapBreadcrumb}"></nav-breadcrumb>
          <transition name="fade" mode="out-in">
            <keep-alive>
              <component class="content-main" v-bind:is="childView" v-if="routeMeta.keepAlive"></component>
            </keep-alive>
          </transition>
          <transition name="fade" mode="out-in">
            <component class="content-main" v-bind:is="childView" v-if="!routeMeta.keepAlive"></component>
          </transition>
        </el-main>
        <!-- <el-button @click="setCollapse">setCollapse</el-button> -->
      </el-container>
    </el-container>
    <el-dialog :show-close="false" class="dialog-view" :title="editPassViewDialogtitle" :visible.sync="editPassViewDialogVisible" :close-on-press-escape="false" :close-on-click-modal="false" center>
      <edit-password @success="editPassSuccess"></edit-password>
    </el-dialog>
    <el-backtop target=".content-box"></el-backtop>
  </el-container>
</template>
<script>
const { ref, reactive, computed, onMounted, onUnmounted, watch } = vue;
const { useRouter, useStore, useCache, useTip, useWindowSize, useWindowSizeRealTime } = hook;
const { initWindowFunc } = util;
const { user: userApi, useRequest } = api;

export default {
  components: {
    headerTop: VueRun('components/header.vue'),
    navLeft: VueRun('components/nav.vue'),
    navBreadcrumb: VueRun('components/breadcrumb.vue'),
    editPassword: VueRun('components/edit-password.vue')
  },
  name: 'mainView',
  setup (prop, ctx) {
    initWindowFunc();
    let title = ref('');

    const store = useStore(ctx);
    const user = computed(() => store.state.user);
    const routeMeta = computed(() => ctx.root.$route.meta);

    function getRouter (router, cPath) {
      for (let i in router) {
        if (!router.hasOwnProperty(i)) continue;
        if (router[i]['path'] === cPath && router[i].hasOwnProperty('component')) {
          return router[i];
        }
        if (router[i].hasOwnProperty('children') && router[i]['children']) {
          let c = getRouter(router[i]['children'], cPath);
          if (c) {
            return c;
          }
        }
      }
    }

    const childView = computed(() => {
      let router = useRouter(ctx)['router']['options']['routes'][2]['children'] || [],
        cPath = useRouter(ctx).route.path || '',
        cRouter = getRouter(router, cPath) || {};
      if (
        JSON.stringify(cRouter) !== '{}'
        && cRouter.hasOwnProperty('component')
        && cRouter.hasOwnProperty('meta')
        && cRouter.meta.has === true
      ) {
        return cRouter.component;
      } else {
        if (['/login'].indexOf(cPath) === -1) {
          useTip().message('warning', '权限不足');
        }
      }
    });

    let logoutState = false;
    watch(
      () => [ctx.root.initSate, store.getters.isLogin],
      ([init, isLogin]) => {
        if (init) {
          if (!isLogin) {
            ctx.root.loading = true;
            logoutState ||
            useTip().notify('warning', '请先登录账号', '温馨提示');
            const router = useRouter(ctx);
            // 记录下当前页面 url
            useCache({}, window['sessionStorage']).setCache('last_url', router.route.fullPath, 100);
            router.replace('/login');
            return;
          }
          ctx.root.loading = false;
        }
      },
      { immediate: true }
    );

    function logout () {
      const { data } = useRequest(userApi.logout);
      watch(data, (val) => {
        if (val.data && val.code < 400) {
          logoutState = true;
          window['SysGetUnreadMessageCount'] = null;
          store.commit('setUser', {});
          store.commit('setToken', null);
        }
      });
    }

    let isCollapse = ref(false);
    let smallScreenWidth = 640,
      mob = false;
    const { width } = useWindowSize();
    let smallScreen = width.value <= smallScreenWidth;

    clearTimeout(judge);
    let judge = setTimeout(() => {
      mob = window.getComputedStyle(document.querySelector('.content-container')).getPropertyValue('margin-left') !== '0px';
      if (mob) {
        isCollapse.value = false;
      } else {
        isCollapse.value = smallScreen;
      }

      var $box = document.querySelector('.el-main.content-box');
      var $breadcrumb = document.querySelector('.breadcrumb');
      var $viewTitleRight = document.querySelector(
        '.view-title.float-clear>.view-title-right'
      );
      if ($breadcrumb && $viewTitleRight) {
        if (
          $breadcrumb.offsetWidth + $viewTitleRight.offsetWidth >=
          $box.offsetWidth - 25
        ) {
          $viewTitleRight.classList.add('view-title-right__alone');
        } else {
          $viewTitleRight.classList.remove('view-title-right__alone');
        }
      }
      judge = false;
    }, 300);

    const asideClass = computed(() => {
        let defClass = 'nav-left aside-nav slow-motion ' + (asideNavOpen.value ? 'open' : '');
        return isCollapse.value
          ? defClass + ' is-collapse'
          : defClass + ' not-collapse';
      }
    );

    const isWrapBreadcrumb = ref(false), asideNavOpen = ref(false);

    function useHandleNav () {
      if (mob) {
        asideNavOpen.value = !asideNavOpen.value;
      } else {
        isCollapse.value = !isCollapse.value;
      }
    }

    function useClickTopNav (name) {
      switch (name) {
        case 'useEditPassword':
          editPassViewDialogVisible.value = true;
          break;
        default:
      }
    }

    const editPassViewDialogtitle = ref('修改密码'),editPassViewDialogVisible = ref(false);
    function editPassSuccess () {
      editPassViewDialogVisible.value = false;
    }

    return {
      title,
      user,
      logout,
      asideClass,
      isCollapse,
      asideNavOpen,
      routeMeta,
      setCollapse: () => {
        isCollapse.value = !isCollapse.value;
      },
      useHandleNav,
      useClickTopNav,
      childView,
      editPassViewDialogtitle,
      editPassViewDialogVisible,
      editPassSuccess,
      isWrapBreadcrumb
    };
  }
};
</script>
<style>
.main_scrollbar {
  min-height : calc(100vh - 50px);
}

.el-main.content-box.content-view-main {
  padding : 15px 15px 10px 15px;
}

.header {
  color : #FFFFFF;
  line-height : 60px;
  padding : 0 10px;
  z-index : 9;
}

@-webkit-keyframes opacity {
  0%, 100% {
    opacity : .1;
  }
  50% {
    opacity : .8;
  }
}

@keyframes opacity {
  0%, 100% {
    opacity : .1;
  }
  50% {
    opacity : .8;
  }
}

.nav-left.is-collapse {
  width : 74px !important;
}

.nav-left.not-collapse {
  width : 220px !important;
}

.mask-layer {
  width : 100%;
  position : fixed;
  left : 0;
  right : 0;
  top : 0;
  bottom : 0;
  background : rgba(0, 0, 0, .3);
  z-index : 9000;
  display : none !important;
}

html, body, #app {
  margin : 0;
  padding : 0;
}

body {
  font-family : 'Helvetica Neue', Helvetica, 'Microsoft Yahei', Arial, sans-serif;
}

:focus {
  outline : 0; /* Not friendly */
}

::-webkit-inner-spin-button {
  display : none;
}

svg[hidden] {
  display : none;
}

.iconfont, [class^='icon-'], [class*=' icon-'] {
  font-family : 'font_family', serif !important;
  font-size : inherit;
  font-style : normal;
  -webkit-font-smoothing : antialiased;
  -moz-osx-font-smoothing : grayscale;
}

.el-submenu [class^='icon-'], .el-menu-item [class^='icon-'] {
  vertical-align : middle;
  margin-right : 10px;
  width : 24px;
  text-align : center;
  font-size : 25px;
}

html {
  -webkit-user-select : none;
  -moz-user-select : none;
  -ms-user-select : none;
  user-select : none;
}

.float-clear:after {
  content : ' ';
  display : block;
  height : 0;
  clear : both;
  visibility : hidden;
}

.text {
  -webkit-user-select : text;
  -moz-user-select : text;
  -ms-user-select : text;
  user-select : text;
}

.tap {
  pointer-events : auto;
  cursor : pointer;
}

.text-nowrap {
  max-width : 100%;
  overflow : hidden;
  white-space : nowrap;
  text-overflow : ellipsis;
}

body, a {
  color : rgb(53, 53, 53);
}

body, h1, h2, h3, h4, h5, h6, p, ul, ol, dl, dd, fieldset, textarea {
  margin : 0;
}

img {
  max-width : 100%;
}

[class^='czs-'], [class^='icon-'] {
  display : inline-block;
}

.loading-group {
  height : 100%;
  width : 100%;
  display : -webkit-box;
  display : -ms-flexbox;
  display : flex;
  -webkit-box-pack : center;
  -ms-flex-pack : center;
  justify-content : center;
  -webkit-box-align : center;
  -ms-flex-align : center;
  align-items : center;
  -webkit-box-orient : vertical;
  -webkit-box-direction : normal;
  -ms-flex-direction : column;
  flex-direction : column;
  background : #F5F7F9;
  position : absolute;
  top : 0;
  right : 0;
  z-index : 100000;
  -webkit-transition : opacity 1s;
  transition : opacity 1s;
}

.loading-group .title {
  color : #495060;
  font-weight : bold;
  font-size : 14px;
  margin-top : 50px;
  margin-bottom : 10px;
  letter-spacing : .2em;
}

.loading-group .sub-title {
  color : #495060;
  font-size : 10px;
}

.loading-group .sk-cube-grid {
  width : 30px;
  height : 30px;
}

.loading-group .sk-cube-grid .sk-cube {
  width : 10px;
  height : 10px;
  background-color : #495060;
  float : left;
  -webkit-animation : sk-cubeGridScaleDelay 1.3s infinite ease-in-out;
  animation : sk-cubeGridScaleDelay 1.3s infinite ease-in-out;
}

.sk-cube-grid .sk-cube1 {
  border-top-left-radius : 4px;
  -webkit-animation-delay : .2s;
  animation-delay : .2s;
}

.sk-cube-grid .sk-cube2 {
  -webkit-animation-delay : .3s;
  animation-delay : .3s;
}

.sk-cube-grid .sk-cube3 {
  border-top-right-radius : 4px;
  -webkit-animation-delay : .4s;
  animation-delay : .4s;
}

.sk-cube-grid .sk-cube4 {
  -webkit-animation-delay : .1s;
  animation-delay : .1s;
}

.sk-cube-grid .sk-cube5 {
  -webkit-animation-delay : .2s;
  animation-delay : .2s;
}

.sk-cube-grid .sk-cube6 {
  -webkit-animation-delay : .3s;
  animation-delay : .3s;
}

.sk-cube-grid .sk-cube7 {
  border-bottom-left-radius : 4px;
  -webkit-animation-delay : 0s;
  animation-delay : 0s;
}

.sk-cube-grid .sk-cube8 {
  -webkit-animation-delay : .1s;
  animation-delay : .1s;
}

.sk-cube-grid .sk-cube9 {
  border-bottom-right-radius : 4px;
  -webkit-animation-delay : .2s;
  animation-delay : .2s;
}

@-webkit-keyframes sk-cubeGridScaleDelay {
  0%, 70%, 100% {
    -webkit-transform : scale3D(1, 1, 1);
    transform : scale3D(1, 1, 1);
  }

  35% {
    -webkit-transform : scale3D(0, 0, 1);
    transform : scale3D(0, 0, 1);
  }
}

@keyframes sk-cubeGridScaleDelay {
  0%, 70%, 100% {
    -webkit-transform : scale3D(1, 1, 1);
    transform : scale3D(1, 1, 1);
  }

  35% {
    -webkit-transform : scale3D(0, 0, 1);
    transform : scale3D(0, 0, 1);
  }
}

::-webkit-scrollbar {
  width : 5px;
  height : 5px;
}

.el-main::-webkit-scrollbar {
  width : 6px;
  height : 6px;
}

::-webkit-scrollbar-thumb {
  background-color : #CCCCCC;
}

::-webkit-scrollbar-thumb:hover {
  background-color : #999999;
}

::-webkit-scrollbar-thumb:active {
  background-color : #CCCCCC;
}

.content-container {
  position : relative;
  height : calc(100vh - 60px);
  -webkit-box-flex : 1;
  -ms-flex : auto;
  flex : auto;
  border-radius : 10px 10px 0 0;
  background-color : #F5F7F9;
  margin-right : 4px;
}

.el-menu--vertical .el-menu--popup.el-menu--popup-right-start {
  min-width : 100px;
}

.el-button {
  font-weight : normal;
}

.el-button.min {
  min-width : 98px;
}

.el-button.block {
  width : 100%;
}

.el-textarea__inner {
  padding : 15px;
}

.el-pagination {
  font-weight : normal;
}

.el-popover p {
  margin : 10px;
  text-align : center;
}

.el-popover > div {
  text-align : center;
}

.el-form-item__content {
  text-align : left;
}

.tip-top.el-form--inline .el-button + span:not(.class) {
  margin-left : 10px;
}

.el-button [class*='czs-'] + span, .el-button [class^='icon-'] + span, i[class^='icon-'] + span {
  margin-left : 5px;
}

/* color #2c6eb1 */
.el-submenu.is-active .el-submenu__title, .el-submenu.is-active .el-submenu__title i {
  color : #FFFFFF !important;
}

.el-menu-item i, .el-submenu__title i {
  color : #B3BECD;
}

.btns-operating > .el-button + .el-button {
  margin-left : 0;
}

.el-table__empty-block {
  line-height : 60px;
}

.tip-page .panel-left {
  position : absolute;
  left : 0;
}

.el-tooltip__popper.is-light {
  border-color : rgb(228, 232, 235);
}

.el-message-box {
  margin-top : -10%;
}

.el-notification.custom-notify-class .el-notification__content {
  margin-top : 2px;
  padding-right : 10px;
}

.el-notification.custom-notify-class.right {
  width : auto;
}

.el-loading-mask {
  background-color : rgba(255, 255, 255, .24);
}

.el-input__suffix {
  overflow : hidden;
}

/*UI END*/
#main {
  min-height : 100%;
  min-height : 100vh;
  min-width : 320px;
  background : linear-gradient(
  50deg,
  rgba(146, 146, 146, .02) 0%,
  rgba(146, 146, 146, .02) 25%,
  rgba(82, 82, 82, .02) 25%,
  rgba(82, 82, 82, .02) 50%,
  rgba(217, 217, 217, .02) 50%,
  rgba(217, 217, 217, .02) 75%,
  rgba(41, 41, 41, .02) 75%,
  rgba(41, 41, 41, .02) 100%
  ),
  linear-gradient(
  252deg,
  rgba(126, 126, 126, .01) 0%,
  rgba(126, 126, 126, .01) 25%,
  rgba(117, 117, 117, .01) 25%,
  rgba(117, 117, 117, .01) 50%,
  rgba(219, 219, 219, .01) 50%,
  rgba(219, 219, 219, .01) 75%,
  rgba(41, 41, 41, .01) 75%,
  rgba(41, 41, 41, .01) 100%
  ),
  linear-gradient(
  272deg,
  rgba(166, 166, 166, .01) 0%,
  rgba(166, 166, 166, .01) 20%,
  rgba(187, 187, 187, .01) 20%,
  rgba(187, 187, 187, .01) 40%,
  rgba(238, 238, 238, .01) 40%,
  rgba(238, 238, 238, .01) 60%,
  rgba(204, 204, 204, .01) 60%,
  rgba(204, 204, 204, .01) 80%,
  rgba(5, 5, 5, .01) 80%,
  rgba(5, 5, 5, .01) 100%
  ),
  linear-gradient(
  86deg,
  rgba(143, 143, 143, .02) 0%,
  rgba(143, 143, 143, .02) 12.5%,
  rgba(36, 36, 36, .02) 12.5%,
  rgba(36, 36, 36, .02) 25%,
  rgba(23, 23, 23, .02) 25%,
  rgba(23, 23, 23, .02) 37.5%,
  rgba(223, 223, 223, .02) 37.5%,
  rgba(223, 223, 223, .02) 50%,
  rgba(101, 101, 101, .02) 50%,
  rgba(101, 101, 101, .02) 62.5%,
  rgba(94, 94, 94, .02) 62.5%,
  rgba(94, 94, 94, .02) 75%,
  rgba(148, 148, 148, .02) 75%,
  rgba(148, 148, 148, .02) 87.5%,
  rgba(107, 107, 107, .02) 87.5%,
  rgba(107, 107, 107, .02) 100%
  ),
  linear-gradient(
  25deg,
  rgba(2, 2, 2, .02) 0%,
  rgba(2, 2, 2, .02) 16.667%,
  rgba(51, 51, 51, .02) 16.667%,
  rgba(51, 51, 51, .02) 33.334%,
  rgba(26, 26, 26, .02) 33.334%,
  rgba(26, 26, 26, .02) 50.001000000000005%,
  rgba(238, 238, 238, .02) 50.001%,
  rgba(238, 238, 238, .02) 66.668%,
  rgba(128, 128, 128, .02) 66.668%,
  rgba(128, 128, 128, .02) 83.33500000000001%,
  rgba(21, 21, 21, .02) 83.335%,
  rgba(21, 21, 21, .02) 100.002%
  ),
  linear-gradient(
  325deg,
  rgba(95, 95, 95, .03) 0%,
  rgba(95, 95, 95, .03) 14.286%,
  rgba(68, 68, 68, .03) 14.286%,
  rgba(68, 68, 68, .03) 28.572%,
  rgba(194, 194, 194, .03) 28.572%,
  rgba(194, 194, 194, .03) 42.858%,
  rgba(51, 51, 51, .03) 42.858%,
  rgba(51, 51, 51, .03) 57.144%,
  rgba(110, 110, 110, .03) 57.144%,
  rgba(110, 110, 110, .03) 71.42999999999999%,
  rgba(64, 64, 64, .03) 71.43%,
  rgba(64, 64, 64, .03) 85.71600000000001%,
  rgba(31, 31, 31, .03) 85.716%,
  rgba(31, 31, 31, .03) 100.002%
  ),
  linear-gradient(90deg, #324157, #324157);
}

.content-box {
  padding : 10px 20px 20px 5px;
  /* overflow-y: auto; */
  /* overflow: hidden; */
  position : relative;
}

.footer {
  text-align : center;
}

.panel {
  margin-bottom : 20px;
  background-color : rgb(255, 255, 255);
  border-radius : 3px;
  box-shadow : 0 2px 12px 0 rgba(0, 0, 0, .1);
  border : 1px solid #EBEEF5;
  padding : 14px 20px;
}

.view-title {
  font-size : 26px;
  font-weight : 400;
  line-height : 1;
  min-height : 35px;
  position : relative;
}

.view-title.float-clear > span:first-child {
  display : none;
}

.view-title.no {
  margin-bottom : 0;
}

.view-title > span {
  vertical-align : middle;
  margin-bottom : 10px;
  display : inline-block;
}

.view-title-right {
  float : right;
  min-height : 35px;
  overflow : auto;
  white-space : nowrap;
}

.view-title-right.view-title-right__alone {
  margin : 30px 0 5px;
}

.view-title-right .el-form--inline .el-form-item__content {
  line-height : 14px;
}

.view-title-right .el-form--inline .el-form-item {
  margin-right : 0;
  margin-bottom : 0;
}

.view-title-right .el-form--inline .el-form-item ~ .el-form-item {
  margin-left : 10px;
}

.view-title-right .el-form--inline + .el-form--inline {
  margin-top : 5px;
}

.popover-form {
  padding : 0 20px;
}

.popover-form .el-popover__title {
  margin-bottom : 8px;
  font-size : 18px;
}

.popover-form .el-form-item {
  margin-bottom : 16px;
}

.popover-form .el-form-item .el-form-item__error {
  padding-top : 1px;
}

.popover-form .el-form-item__label {
  padding : 0;
  line-height : 20px;
}

.dialog-view .el-dialog--center {
  min-width : 318px;
  max-width : 500px;
}

.tip-top {
  text-align : right;
  position : relative;
  /* overflow: hidden; */
}

.tip-page {
  padding : 10px 0 0;
  text-align : center;
  position : relative;
}

.tip-area {
  font-size : 12px;
  color : rgb(153, 153, 153);
  padding-bottom : 10px;
}

.btns-operating .el-button {
  margin-right : 6px;
}

.btns-operating .el-button + span:not(.class) {
  display : inline-block;
}

.center {
  text-align : center;
}

.text-grey {
  color : #C0C4CC;
}

/* 竖排表单 移除 */
.form-vertical {
  padding : 10px 10px 0;
}

.form-vertical.el-form--label-top .el-form-item__label {
  padding-bottom : 0;
  line-height : 14px;
}

.form-form-vertical .el-form-item:first-child {
  margin-top : 10px;
}

.form-vertical > .el-form-item:last-child {
  margin-bottom : 0;
}

.form-vertical .el-textarea {
  padding : 5px 0;
}

.form-vertical .el-form-item__content .el-select {
  width : 100%;
}

.form-create-mini .el-col:last-child {
  text-align : center;
}

.form-create-mini .el-col:last-child > .el-col {
  text-align : center;
  display : inline-block;
  width : auto;
  float : none;
}

.form-create-mini {
  overflow : auto;
}

.form-create-mini .el-date-editor.el-input {
  display : block;
  width : 100%;
}

.form-create-mini .el-select {
  width : 100%;
}

.form-create-mini .el-col:last-child > .el-col:last-child {
  left : 0;
  margin-left : 10px;
}

.form-create-mini .el-col:last-child > .el-col:first-child {
  margin-right : 10px;
}

/* 竖排表单 END */

.list-avatar {
  width : 40px;
  height : 40px;
  text-align : center;
  border : 1px solid rgb(228, 232, 235);
  vertical-align : middle;
}

.list-avatar-tooltip {
  width : 120px;
}

.list-avatar-tooltip img {
  width : 120px;
}

#nprogress .spinner {
  right : 42px !important;
  top : 20px !important;
}

fieldset {
  -webkit-box-shadow : 0 2px 12px 0 rgba(0, 0, 0, .1);
  box-shadow : 0 2px 12px 0 rgba(0, 0, 0, .1);
  border : 1px solid #EBEEF5;
  background-color : #FFFFFF;
  margin-bottom : 10px;
  padding : 5px 10px;
  font-size : 14px;
  min-inline-size : auto;
}

fieldset .el-form--inline .el-form-item {
  margin-bottom : 0;
}

fieldset > aside {
  padding : 5px;
}

fieldset aside > li {
  display : -webkit-box;
  display : -ms-flexbox;
  display : flex;
  -webkit-box-align : center;
  -ms-flex-align : center;
  align-items : center;
  min-height : 30px;
  border-bottom : 1px solid #EBEEF5;
  font-size : 13px;
  word-break : keep-all;
}

fieldset aside > li:last-child {
  border : 0;
}

fieldset aside > li span {
  padding : 5px;
}

fieldset aside > li span:last-child {
  -webkit-box-flex : 1;
  -ms-flex : 1;
  flex : 1;
  text-align : right;
}

legend {
  background-color : #FFFFFF;
  padding : 3px 10px;
  font-size : 13px;
  -webkit-box-shadow : 0 1px 2px 0 rgba(0, 0, 0, .1);
  box-shadow : 0 1px 2px 0 rgba(0, 0, 0, .1);
}

.slow-motion {
  -webkit-transition : all .2s linear 0s !important;
  transition : all .2s linear 0s !important;
}

.wrap-breadcrumb.breadcrumb {
  display : inline-block;
  position : static !important;
}

/* Mobile phone */
@media (min-device-width: 320px) and (max-device-width: 640px) {
  #app .nav_scrollbar {
    max-height : 100vh;
    margin-bottom : 0 !important;
    padding : 0;
  }

  .content-container {
    margin-left : 4px;
  }

  .aside-nav {
    display : block;
    height : 100%;
    position : fixed;
    top : 0;
    left : -200vw;
    z-index : 666;
    background : #304156;
  }

  .aside-nav.open {
    left : 0;
  }

  .aside-nav-bg.open {
    position : fixed;
    top : 0;
    bottom : 0;
    left : 0;
    right : 0;
    background : rgba(0, 0, 0, .6);
    z-index : 660;
  }

  .tip-page {
    overflow : auto;
  }

  .breadcrumb {
    position : static !important;
  }
}

</style>
