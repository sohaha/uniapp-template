<style>
  .main_scrollbar {
    min-height: calc(100vh - 50px);
  }

  .el-main.content-box.content-view-main {
    padding: 15px 15px 10px 15px;
  }

  @-webkit-keyframes opacity {
    0%,
    100% {
      opacity: 0.1;
    }
    50% {
      opacity: 0.8;
    }
  }

  @keyframes opacity {
    0%,
    100% {
      opacity: 0.1;
    }
    50% {
      opacity: 0.8;
    }
  }

  .nav-left.is-collapse {
    width: 74px !important;
  }

  .nav-left.not-collapse {
    width: 220px !important;
  }

  .mask-layer {
    width: 100%;
    position: fixed;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    z-index: 9000;
    display: none !important;
  }
</style>
<template>
  <el-container id="main">
    <el-header class="header" height="60px">
      <components_nav-top aria-label="顶部导航" :is-collapse="isCollapse" @handle="handleNav" @click="clickTopNav"></components_nav-top>
    </el-header>
    <el-container class="content">
      <div class="mask-layer" :class="asideClass" @click="handleNav"></div>
      <el-aside class="nav-left" :class="asideClass">
        <components_nav aria-label="页面导航" @handle="handleNav" :is-collapse="isCollapse"></components_nav>
      </el-aside>
      <el-container class="content-container">
        <el-main ref="content-box" class="content-box content-view-main" aria-label="页面内容">
          <components_breadcrumb></components_breadcrumb>
          <!--包含keep关键字的页面进行缓存-->
          <keep-alive :max="3" :include="/keep/">
            <component class="content-main" v-bind:is="childView"></component>
          </keep-alive>
        </el-main>
        <!-- <el-footer class='footer'>@jie</el-footer> -->
      </el-container>
    </el-container>
    <el-dialog :show-close="false" class="dialog-view" :title="editPassViewDialogtitle" :visible.sync="editPassViewDialogVisible" :close-on-press-escape="false" :close-on-click-modal="false" center>
      <components_edit-password @success="editPassSuccess"></components_edit-password>
    </el-dialog>
    <el-backtop target=".content-view-main"></el-backtop>
  </el-container>
</template>
<script>
  var that, timer;
  Spa.define(
    {
      data: function () {
        return {
          isCollapse: window.innerWidth <= 850,
          editPassViewDialogtitle: '修改密码',
          editPassViewDialogVisible: false
        };
      },
      watch: {
        SpaViews: {
          handler: function (e) {
            if (e.items.length <= 1) {
              // console.log('当前组件为空，重定向到main/main');
              Spa.replace('main/main');
            } else {
              this.setBreadcrumbBr();
            }
            if (window.innerWidth <= 640) {
              this.isCollapse = true;
            }
          },
          deep: true,
          immediate: true
        },
        isCollapse: {
          handler: function () {
            this.setBreadcrumbBr();
          },
          immediate: true
        }
      },
      computed: {
        view: function () {
          return this.childView === 'index' ? 'main_main' : this.childView;
        },
        asideClass: function () {
          return this.isCollapse ? 'is-collapse' : 'not-collapse';
        }
      },
      created: function () {
        that = this;
        this.$root.getInfo();
      },
      mounted: function () {
        var resizeTag = true,
          scrollTimer = false;
        window['onresize'] = function () {
          if (resizeTag) {
            that.onResize();
            resizeTag = false;
            setTimeout(function () {
              resizeTag = true;
            }, 100);
          }
        };
        that.onResize();
      },
      methods: {
        onResize: function () {
          var clientWidth = document.documentElement.clientWidth;
          that.$root.clientWidth = clientWidth;
          if (!that.isCollapse) {
            that.isCollapse = clientWidth <= 850;
          }
          that.setBreadcrumbBr();
        },
        setBreadcrumbBr: function () {
          clearTimeout(timer);
          timer = setTimeout(function () {
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
            timer = false;
          }, 300);
        },
        clickTopNav: function (name) {
          console.log(name);
          switch (name) {
            case 'editPassword':
              that.editPassViewDialogVisible = true;
              break;
            default:
          }
        },
        editPassSuccess: function () {
          that.editPassViewDialogVisible = false;
        },
        handleNav: function () {
          this.isCollapse = !this.isCollapse;
        },
        handleOpen: function (key, keyPath) {
          console.log(key, keyPath);
        },
        handleClose: function (key, keyPath) {
          console.log(key, keyPath);
        }
      }
    },
    [
      'main/main',
      'components/nav',
      'components/nav-top',
      'components/breadcrumb',
      'components/edit-password'
    ]
  );
</script>
