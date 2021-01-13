var config = {
  title: '管理后台',
  baseURL: '',
  timeout: 5000,
  navServe: false,
  debug: false,
};

VueRun.config({
  cdn: '//resources.73zls.com/vue-admin',
  debug: config.debug,
  version: 1,
  notExist: function (url) {
    console.log('不存在', url);
    hook.useTip().message('warning', '页面不存在');
  }
});

var assetsCdn = '//resources.73zls.com/vue-admin-template';
var themePrefixPath = assetsCdn + '/themes';
var themes = ['lavender', 'green', 'dark', 'diablo'].map(function (theme) {
  return [themePrefixPath + '/' + theme + '/theme/index.css', themePrefixPath + '/' + theme + '/app.css'];
});

VueRun.init(function () {
  app.requestInit();
  Vue.mixin({ beforeCreate: hook.setVm });
  new Vue({
    router: app.initRouter(),
    store: app.initStore(),
    data: function () {
      return {
        initSate: false,
        loading: true,
        ProjectName: config.title,
      };
    },
    mounted: function () {
      this.getUserInfo();
    },
    methods: {
      getUserInfo: function () {
        var t = this, store = hook.useStore();
        if (!store.state.token) {
          t.initSate = true;
          return;
        }
        var done = function (data, router) {
          store.commit('setUser', data);
          // 根据情况动态注入路由
          if (config.debug) router = (app.demoMenu()).concat(router);
          store.commit('setRouter', router);

          t.initSate = true;
        };

        var user = function () {
          return api.useRequestWith(api.user.current(), { manual: true }).run();
        };
        return user().then(function (res) {
          var data = res[0];
          var err = res[1];
          if (!err && data) {
            if (!config.navServe) {
              return VueRun.httpRequest('./router.json').then(function (router) {
                if (typeof router === 'string') {
                  router = JSON.parse(router);
                }

                done(data, router);
                return res;
              });
            }

            done(data, data.menu);
            return res;
          }
          if (err) {
            console.log(err);
            hook.useTip().message('warning', err);
          }
        }).catch(function (err) {
          console.log(err);
        });
      }
    }
  }).$mount('#app');
}, {
  global: {
    hook: 'script/hook.es6',
    util: 'script/util.es6',
    app: 'script/app.es6',
    apiUtil: 'script/api-util.es6',
    api: 'script/api.es6',
  },
  js: [
    VueRun.isSupportEs6('new WeakMap()') ? '' : VueRun.lib('/lib/weakmap-polyfill.js'),
    'getOwnPropertySymbols' in Object ? '' : VueRun.lib('/lib/get-own-property-symbols-polyfill.js'),
    [VueRun.lib('/lib/axios.js'), { async: true }],
    VueRun.lib('/lib/vue-router.js'),
    VueRun.lib('/lib/vuex.js'),
    VueRun.lib('/lib/composition.js'),
    VueRun.lib('/element.js'),
    VueRun.lib('/nprogress/nprogress.js'),
  ],
  css: [
    VueRun.lib('/element.css'),
    VueRun.lib('/fonts/iconfont/iconfont.css'),
    VueRun.lib('/nprogress/nprogress.css'),
  ]//.concat(themes.lavender)
});
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function () {
    if (location.protocol === 'https:') navigator.serviceWorker.register('/sw.js');
  });
  var refreshing = false;
  navigator.serviceWorker.addEventListener('controllerchange', function () {
    if (refreshing) {
      return;
    }
    refreshing = true;
    window.location.reload();
  });
}
