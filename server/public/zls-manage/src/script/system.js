/**
 * Created by 影浅-seekwe@gmail.com on 2019-4-10 11:39:38
 */
var api = function (name, data) {
  var api = typeof name === 'string' ? apis[name] || '' : name;
  return !!api
    ? typeof api === 'string'
      ? api
      : (function () {
        var url = api[1];
        if (typeof data === 'string') {
          url += (url.indexOf('?') > -1 ? '&' : '?') + data;
          data = {};
        }
        return this[api[0]]
          ? this[api[0]](url, data)
          : this['request'](api[0], url, data);
      })()
    : (function () {
      var p = Spa.promise();
      setTimeout(function () {
        typeof api === 'undefined'
          ? p.resolve()
          : p.reject('未知接口: ' + name);
      });
      return p;
    })();
};
var userData,
  defaultAvatar =
    'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAMCAgMCAgMDAwMEAwMEBQgFBQQEBQoHBwYIDAoMDAsKCwsNDhIQDQ4RDgsLEBYQERMUFRUVDA8XGBYUGBIUFRT/2wBDAQMEBAUEBQkFBQkUDQsNFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBT/wAARCABQAFADASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD7LooooAKKKKACinRQy3DFYopJmHURqTj64FEsMlu4WWN4mPQSKQT9MigBtFFFABRRRQAUUUUAFaXh3RzruqxWxYpEAXlZeoQdQPcnArNrq/hw6jVbxT99oBt+gYZoA7u0t4rCBYLaJbeFRgJGMD8T1J9zzReWsGowNBdRLcQsMFX5/EHsfcVJRQB5Lr+knQ9VmtNxeNcNG56sh5Gfft+FZ9dT8RWU61bqPvLbjd+JJH6Vy1ABRRRQAUUUUAFXdF1R9G1SC8QFwhIdB/Ep4I+uP1FUq2dC8KXuugSqBbWhP/HxIDhvXaOp+vT3oA9NtbqG+t457eQSwSDcrjuPf0Pt2pt3eQafbSXNzIIoYxlmPU+gA7ntiq+jaPBoVgtpAzuoYuXfqzHqcDgdOgpNb0WDXrIW07PGFYOrx4yrYIzg8EYPQ0AeX6tqT6vqVxeSDaZW+VP7q9APy/WqlbGueFL3QgZXAuLQH/j4iBwv+8O316e9Y9ABRRRQAUUUUAdL4M8NJrEr3d2u6zhYKsfaV+uD7D9TxXovQAAAADAA4AHYAdhWB4DA/wCEYh5A/fSdx610H4j8xQAlFL+I/MUfiPzFACdiCAQRgg8gjuCD1Fed+NPDSaRKl5aLts5m2tGOkTdcD2P6HivRfxH5iuf8eAf8IxNyD++j7j1oA80ooooAKKKKAE/E/maPxP5mlooAT8T+Zo/E/maWigBPxP5mj8T+ZpaKACiiigD/2Q==';

try {
  userData = JSON.parse(localStorage.getItem('user'));
} catch (e) {}
var vstore = new Vuex.Store({
  state: {
    token: localStorage.getItem('token'),
    user: userData || {},
    groups: [],
    nav: [],
    viewTitle: '',
    breadcrumb: [],
    unreadMessageCount: 0,
    defaultData: { avatar: defaultAvatar }
  },
  mutations: {
    setToken: function (state, data) {
      state.token = data;
      localStorage.setItem('token', data);
    },
    setUnreadMessageCount: function (state, count) {
      state.unreadMessageCount = count;
    },
    setUser: function (state, data) {
      if (data.groups) {
        this.commit('setGroups', data.groups);
        delete data.groups;
      }
      state.user = data;
      localStorage.setItem('user', JSON.stringify(data));
    },
    setNav: function (state, data) {
      state.nav = data;
    },
    setBreadcrumb: function (state, data) {
      state.breadcrumb = data;
    },
    setViewTitle: function (state, data) {
      state.viewTitle = data;
    },
    setGroups: function (state, data) {
      state.groups = data;
    }
  },
  actions: {},
  getters: {
    isSuper: function (state) {
      return state.user.isSuper || false;
    },
    avatar: function (state) {
      return state.user.avatar || state.defaultData.avatar;
    },
    nickname: function (state) {
      return state.user.nickname || state.user.username;
    },
    token: function (state) {
      return state.token;
    },
    menus: function (state) {
      return state.user.menus || [];
    },
    groupID: function (state) {
      return state.user.group_id || -1;
    },
    groups: function (state) {
      return state.groups.length > 0 ? state.groups : [{ name: '-无角色-', id: 0 }];
    }
  }
});
var headers = {};
var _then = function (data) {
  var code = data.code;
  var page = app.router.page;
  if (!code || !(code >= 200 && code < 210)) {
    var msg = data.msg || data.message || '请求失败';
    var nores = false;
    if (code === 403) {
      app.$warMsg(msg);
      throw null;
    } else if (code === 401) {
      if (!window['__login_tip__']) {
        window['__login_tip__'] = true;
        setTimeout(function () {
          window['__login_tip__'] = false;
        }, 1000);
        if (page !== 'index') {
          if (!app.sourceUrl) {
            app.sourceUrl = '/' + page;
          }
          app.$message({
            type: 'error',
            showClose: true,
            message: msg,
            center: true
          });
        }
        app.$store.commit('setToken', '');
        Spa.go('/login');
        // throw null;
      }
      msg = null;
    }
    throw msg;
  }
  // if (Spa.debug) console.log("_then", page, data);
  return data;
};
var get = function (url, data, opt) {
  var arg = Spa.urlEncode(data);
  url += apiUrl + url.indexOf('?') > -1 ? '&' : '?';
  return Spa.get
  .apply(Spa, [
    url + arg,
    __assign(
      {
        timeout: 0,
        headers: __assign({}, headers, { token: vstore.getters.token })
      },
      opt
    )
  ])
  .then(_then);
};
var post = function (url, data, opt) {
  return Spa.post
  .apply(Spa, [
    apiUrl + url,
    data,
    __assign(
      { headers: __assign({}, headers, { token: vstore.getters.token }) },
      opt
    )
  ])
  .then(_then);
};
var request = function (type, url, data, opt) {
  return Spa.request
  .apply(Spa, [
    type,
    apiUrl + url,
    data,
    __assign(
      { headers: __assign({}, headers, { token: vstore.getters.token }) },
      opt
    )
  ])
  .then(_then);
};

var pathname = location.pathname.split('/');
var projectPath = pathname[pathname.length - 2];
Spa.log = function (msg) {
  if (Spa.debug)
    console.log('%c LOG ', 'background:#aaa;color:#bada55', msg);
};
Spa.run({
  debug: window['debug'],
  cache: typeof cache === 'undefined' ? true : cache,
  title: title,
  baseUrl: (projectPath ? '/' + projectPath + '/' : '') + 'src/',
  vueConfig: {
    data: function () {
      return {
        sourceUrl: '',
        ProjectName: title,
        loading: true,
        clientWidth: document.documentElement.clientWidth
      };
    },
    methods: {
      getInfo: function () {
        var _t = this;
        this.$api(apis.sysUseriInfo)
        .then(function (e) {
          if (!_t.childView) _t.childView = 'main_main';
          _t.$store.commit('setUser', e.data);
        })
        .catch(function (e) {
          if (e) {
            _t.$message({
              type: 'error',
              showClose: true,
              message: '网络繁忙，请稍后再试！',
              center: true
            });
            console.log('退出登录状态?');
          }
        })
        .finally(function () {
          var el = document.getElementById('loading');
          var existence = Spa.vue.loading && el;
          if (existence) {
            el.addEventListener(
              el.style['WebkitTransition'] !== undefined
                ? 'webkitTransitionEnd'
                : 'transitionend',
              function () {
                Spa.vue.loading = false;
              }
            );
            setTimeout(function () {
              el.style.opacity = 0;
            }, 30);
          }
        });
      }
    }
  },
  routerBeforeEach: function (to, from, next) {
    if (Spa.debug) {
      Spa.log(from + ' -> ' + to + '');
      console.groupEnd();
      console.group('router: ' + to);
    }
    if (to !== 'login' && !vstore.getters.nickname) {
      return 'login';
    } else {
      window['NProgress'] && NProgress.start();
      next(function () {
        window['NProgress'] && NProgress.done();
      });
    }
  },
  routerError: function (err, page, state) {
    console.warn(err);
    Spa.replace('/not-exist');
  }
})
.then(function () {
  Spa.loadJs('src/script/tools.js');
  Spa.loadRemoteJs(
    '//cdn.jsdelivr.net/npm/zls-manage/nprogress/nprogress.js',
    true
  );
  Spa.loadRemoteCss(
    '//cdn.jsdelivr.net/npm/zls-manage/nprogress/nprogress.css'
  );
  Vue.use(Vuex);
  Vue.prototype.$store = vstore;
  Vue.prototype.$api = function (name, data) {
    return window['api'](name, data);
  };
  Vue.prototype.$go = function (e) {
    Spa.go(
      e.indexOf('/') === 0 || e.indexOf('./') === 0 || e.indexOf('../') === 0
        ? e
        : 'main/' + e
    );
  };
  Vue.prototype.$replace = Spa.replace;
  Vue.prototype.$back = Spa.back;
  Vue.prototype.$get = get;
  Vue.prototype.$post = post;
  Vue.prototype.$request = request;
  var message = function (type, tip, duration) {
    if (!tip) return;
    if (typeof duration === 'undefined') duration = 3000;
    window['app'].$message({
      type: type,
      showClose: true,
      message: tip,
      center: true,
      duration: duration
    });
  };
  var notify = function (type, tip, duration) {
    if (!tip) return;
    if (typeof duration === 'undefined') duration = 3000;
    window['app'].$notify({
      message: tip,
      type: type,
      customClass: 'custom-notify-class',
      duration: duration,
      offset: 58
    });
  };
  var confirm = window['app'].$confirm;
  Vue.prototype.$confirm = function (tip, title, fn, opt) {
    confirm(tip, Object.assign({
      title: title,
      center: true,
      showClose: false,
      closeOnClickModal: false,
      callback: fn
    }, opt || {}));
  };

  Vue.prototype.$tipMsg = function (tip, duration) {
    message('info', tip, duration);
  };
  Vue.prototype.$sucMsg = function (tip, duration) {
    message('success', tip, duration);
  };
  Vue.prototype.$errMsg = function (tip, duration) {
    message('error', tip, duration);
  };
  Vue.prototype.$warMsg = function (tip, duration) {
    message('warning', tip, duration);
  };
  Vue.prototype.$sucNotify = function (tip, duration) {
    notify('success', tip, duration);
  };
  Vue.prototype.$errNotify = function (tip, duration) {
    notify('error', tip, duration);
  };
  Vue.prototype.$warNotify = function (tip, duration) {
    notify('warning', tip, duration);
  };
  Vue.prototype.$tipNotify = function (tip, duration) {
    notify('info', tip, duration);
  };
})
.finally(function () {
  console.log('ok');
});
