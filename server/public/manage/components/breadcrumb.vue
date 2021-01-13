<template>
  <el-breadcrumb class="breadcrumb" separator-class="el-icon-arrow-right">
    <transition-group name="slide-fade">
      <el-breadcrumb-item v-for="(v, i) in breadcrumb" :key="v.path+'/'+v.name" :class="breadcrumbClass(v)" @click.native="go(v)">
        <i class="icon-home" v-if="i===0"></i>{{ v.name }}
      </el-breadcrumb-item>
    </transition-group>
  </el-breadcrumb>
</template>

<script>
const {useRouter, useStore} = hook;
const {toRef, ref, watch, computed} = vue;

export default {
  name: 'navBreadcrumb',
  props: {},
  setup(prop, ctx) {
    const currentPath = computed(() => {
      return useRouter(ctx).route.path;
    })

    const breadcrumb = computed(() => {
      let defaultBreadcrumb = {
            name: '后台中心',
            path: '/main/main',
            meta: {
              show: true,
              real: true,
              breadcrumb: true
            }
          },
          matched = useRouter(ctx)['route']['matched'] || [],
          breadcrumbArr = [];
      matched.forEach((v, k) => {
        if (k !== 0) {
          breadcrumbArr.push({
            name: v.name,
            path: v.path,
            meta: {
              show: v.meta.show,// 导航栏显示
              real: v.meta.real,// 导航栏点击
              breadcrumb: v.meta.breadcrumb// 面包屑显示
            }
          })
        }
      });
      if (breadcrumbArr[0]['name'] !== defaultBreadcrumb.name) {
        breadcrumbArr.unshift(defaultBreadcrumb);
      }

      useStore(ctx).commit('setViewTitle', breadcrumbArr[breadcrumbArr.length - 1]['name'] || '')

      return breadcrumbArr;
    })

    function breadcrumbReal(current) {
      return current.meta.real && current.path;
    }

    function breadcrumbClass(v) {
      if (breadcrumbReal(v)) {
        return 'breadcrumb__link';
      }

      return '';
    }

    function go(v) {
      if (breadcrumbReal(v)) {
        useRouter(ctx).replace(v.path)
      }
    }

    return {
      breadcrumb,
      breadcrumbClass,
      go
    };
  }
};
</script>

<style>
.breadcrumb {
  position: absolute;
  z-index: 1;
  top: 15px;
  left: 15px;
  font-size: 14px;
  line-height: 28px;
}

.breadcrumb__link .el-breadcrumb__inner {
  color: #2a6cb1;
  cursor: pointer;
}

.slide-fade-move {
  transition: all .3s;
}

.slide-fade-enter-active {
  transition: all .3s ease;
  position: initial;
}

.slide-fade-enter {
  position: initial;
}

.slide-fade-leave-active {
  transition: all .3s ease, left 1s linear;
  position: fixed;
  left: auto;
  right: 0;
}

.slide-fade-leave-to {
  opacity: 0;
}

.slide-fade-enter {
  transform: translateY(10px);
  opacity: 0;
}
</style>
