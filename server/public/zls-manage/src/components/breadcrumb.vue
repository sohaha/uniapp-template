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

</style>

<template>
  <el-breadcrumb class="breadcrumb" separator-class="el-icon-arrow-right">
    <el-breadcrumb-item v-for="(v,k) in breadcrumb" :key="k" :class="breadcrumbClass(v)" @click.native="go(v.index)">
      <i class="icon-home" v-if="k===0"></i> {{v.title}}
    </el-breadcrumb-item>
  </el-breadcrumb>
</template>

<!--suppress JSDuplicatedDeclaration -->
<script>
  var getParent = function (navs, current, parent, lastNav) {
    for (var k = 0, l = navs.length; k < l; k++) {
      var nav = navs[k],
        child = nav['child'],
        path = 'main/' + nav['index'];
      if (path === current) {
        if (lastNav && lastNav.length > 0) {
          for (var i = 0, ll = lastNav.length; i < ll; i++) {
            parent.push(lastNav[i]);
          }
        }
        parent.push(nav);
        return parent;
      } else if (child && child.length > 0) {
        var child = getParent(
          child,
          current,
          parent,
          [].concat(lastNav || [], [
            {
              title: nav.title,
              index: nav.real ? nav.index : '',
              show: nav.show !== false,
              breadcrumb: nav.breadcrumb !== false
            }
          ])
        );
        if (child) {
          return child;
        }
      }
    }
  };
  Spa.define({
    computed: {
      breadcrumb: function () {
        var navs = this.$store.state.nav || [],
          current = this.router.page,
          defaultBreadcrumb = { title: '后台中心', index: 'main' },
          data = getParent(navs, current, []);
        if (data && data[0] && data[0].title !== defaultBreadcrumb['title']) {
          data.unshift(defaultBreadcrumb);
        }
        var breadcrumb = [].concat(data || []).filter(function (v) {
          return v.breadcrumb !== false;
        });
        this.$store.commit('setBreadcrumb', breadcrumb);
        var lData = breadcrumb[breadcrumb.length - 1];
        if (lData && lData.title) {
          this.$store.commit(
            'setViewTitle',
            lData.title
          );
        }

        return breadcrumb;
      }
    },
    methods: {
      breadcrumbClass: function (v) {
        return !!v.index ? 'breadcrumb__link' : '';
      },
      go: function (e) {
        if (e && 'main/' + e !== this.router.page) this.$go(e);
      }
    }
  });
</script>
