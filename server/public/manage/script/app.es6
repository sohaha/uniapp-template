export let vm = {};
export let request = {};
export let router = {};
export let store = {};
export let userData = {};
export let defaultAvatar = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAMCAgMCAgMDAwMEAwMEBQgFBQQEBQoHBwYIDAoMDAsKCwsNDhIQDQ4RDgsLEBYQERMUFRUVDA8XGBYUGBIUFRT/2wBDAQMEBAUEBQkFBQkUDQsNFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBT/wAARCABQAFADASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD7LooooAKKKKACinRQy3DFYopJmHURqTj64FEsMlu4WWN4mPQSKQT9MigBtFFFABRRRQAUUUUAFaXh3RzruqxWxYpEAXlZeoQdQPcnArNrq/hw6jVbxT99oBt+gYZoA7u0t4rCBYLaJbeFRgJGMD8T1J9zzReWsGowNBdRLcQsMFX5/EHsfcVJRQB5Lr+knQ9VmtNxeNcNG56sh5Gfft+FZ9dT8RWU61bqPvLbjd+JJH6Vy1ABRRRQAUUUUAFXdF1R9G1SC8QFwhIdB/Ep4I+uP1FUq2dC8KXuugSqBbWhP/HxIDhvXaOp+vT3oA9NtbqG+t457eQSwSDcrjuPf0Pt2pt3eQafbSXNzIIoYxlmPU+gA7ntiq+jaPBoVgtpAzuoYuXfqzHqcDgdOgpNb0WDXrIW07PGFYOrx4yrYIzg8EYPQ0AeX6tqT6vqVxeSDaZW+VP7q9APy/WqlbGueFL3QgZXAuLQH/j4iBwv+8O316e9Y9ABRRRQAUUUUAdL4M8NJrEr3d2u6zhYKsfaV+uD7D9TxXovQAAAADAA4AHYAdhWB4DA/wCEYh5A/fSdx610H4j8xQAlFL+I/MUfiPzFACdiCAQRgg8gjuCD1Fed+NPDSaRKl5aLts5m2tGOkTdcD2P6HivRfxH5iuf8eAf8IxNyD++j7j1oA80ooooAKKKKAE/E/maPxP5mlooAT8T+Zo/E/maWigBPxP5mj8T+ZpaKACiiigD/2Q==';

export function hasPermission (key) {
  let h = false;
  const t = typeof key;
  if (t === 'string') {
    h = store.getters.marks.indexOf(key) > -1;
  } else if (t === 'bool') {
    h = t;
  } else if (t === 'object' && key.length > 0) {
    h = true;
    for (const i in key) {
      h = store.getters.marks.indexOf(key[i]) > -1;
      if (!h) {
        break;
      }
    }
  }
  return h;
}

export function setVm (app) {
  vm = app || this;
  Vue.directive('permission', {
    inserted: function (el, binding) {
      let p = binding.value;
      if (p) {
        if (hasPermission(p)) {
          el.parentNode && el.parentNode.removeChild(el);
        }
      }
    }
  });
}

export function initStore () {
  const cache = hook.useCache();
  try {
    userData = JSON.parse(cache.getCache('appuser', {}));
  } catch (e) {
  }
  store = new Vuex.Store({
    state: {
      token: cache.getCache('apptoken', ''),
      user: userData || {},
      groups: [],
      router: [],
      breadcrumb: [],
      viewTitle: '',
      unreadMessageCount: 0,
      defaultData: { avatar: defaultAvatar },
      nav: [],
    },
    getters: {
      userid (state) {
        return state.user.id || 0;
      },
      isLogin (state) {
        return Object.keys(state.user).length > 0 && state.token;
      },
      getViewTitle (state) {
        return state.viewTitle;
      },
      isSuper (state) {
        return state.user.is_super || false;
      },
      avatar (state) {
        return state.user.avatar || state.defaultData.avatar;
      },
      nickname (state) {
        return state.user.nickname || state.user.username;
      },
      token (state) {
        return state.token;
      },
      groupID (state) {
        return state.user.group_id || -1;
      },
      groups (state) {
        return state.groups.length > 0 ? state.groups : [{ name: '-无角色-', id: 0 }];
      },
      menus (state) {
        return state.user.menus || [];
      },
      marks (state) {
        return state.user.marks || [];
      }
    },
    mutations: {
      setToken (state, data) {
        state.token = data;
        cache.setCache('apptoken', data);
      },
      setUnreadMessageCount (state, count) {
        state.unreadMessageCount = count;
      },
      setUser (state, data) {
        if (data.groups) {
          store.commit('setGroups', data.groups);
          delete data.groups;
        }
        state.user = data;
        cache.setCache('appuser', JSON.stringify(data));
      },
      setRouter (state, data) {
        state.router = resetRouter(router)(data);
      },
      setViewTitle (state, data) {
        state.viewTitle = data;
      },
      setGroups (state, data) {
        state.groups = data;
      }
    },
    strict: config && config.debug
  });
  window['$store'] = store;
  return store;
}

export const defaultRouter = [
  { name: '首页', path: '/', component: VueRun('./pages/home.vue') },
  { name: '登录', path: '/login', component: VueRun('./pages/login.vue') },
  {
    name: '管理中心', path: '/main', component: VueRun('./pages/main.vue'),
    children: []
  },
  {
    path: '*', name: 'Loading', component: VueRun('./pages/error-404.vue'),
  }
];
const originalPush = VueRouter.prototype.push;
VueRouter.prototype.push = function push (location) {
  return originalPush.call(this, location).catch(err => err);
};

function forInitRouter (data, parent) {
  return data.map(e => {
    if (!e.component && e.url) {
      e.component = VueRun(e.url);
    }
    if (!e.hasOwnProperty('meta')) e.meta = {};

    if (parent && e.path.indexOf('/') !== 0) {
      e.path = (parent.path + '/' + e.path).replace(/\/\//, '/');
    }
    if (e.children) {
      e.children = forInitRouter(e.children, e);
    }
    return e;
  });
}

export function resetRouter (router) {
  return (routerData, global) => {
    let data = [].concat(defaultRouter);
    data[2].children = [].concat(routerData);
    data = forInitRouter(data, global);
    const newRouter = new VueRouter({
      mode: router.mode,
      routes: data
    });
    router.options.routes = newRouter.options.routes;
    router.matcher = newRouter.matcher;
    return router.options.routes;
  };
};

export function demoMenu () {
  return appendRouter2Children;
}

const appendRouter2Children = [
  {
    'name': '页面示例',
    'path': 'demo',
    'url': '',
    'icon': 'icon-folder',
    'meta': {
      'keepAlive': true,
      'show': true,
      'has': true,
      'collapse': true,
    },
    'children': [
      {
        'name': '默认示例',
        'path': 'demo',
        'url': assetsCdn + '/pages/demo/demo.vue',
        'icon': 'icon-settings-',
        'meta': {
          'show': true,
          'has': true
        }
      },
      {
        'name': '列表示例',
        'path': 'demo-lists',
        'url': assetsCdn + '/pages/demo/demo-lists.vue',
        'icon': 'icon-list',
        'meta': {
          'show': true,
          'has': true
        }
      },
      {
        'name': '列表编辑',
        'path': 'demo-lists-edit',
        'url': assetsCdn + '/pages/demo/demo-lists-edit.vue',
        'icon': 'icon-edit',
        'meta': {
          'show': true,
          'has': true
        }
      },
      {
        'name': '表单示例',
        'path': 'demo-form',
        'url': assetsCdn + '/pages/demo/demo-form.vue',
        'icon': 'icon-checkmark-square-',
        'meta': {
          'show': true,
          'has': true
        }
      },
      {
        'name': '动态表单',
        'path': 'demo-form2',
        'url': assetsCdn + '/pages/demo/demo-form2.vue',
        'icon': 'icon-layout',
        'meta': {
          'show': true,
          'has': true
        }
      },
      {
        'name': '标签示例',
        'path': 'demo-tabs',
        'url': assetsCdn + '/pages/demo/demo-tabs.vue',
        'icon': 'icon-pricetags',
        'meta': {
          'show': true,
          'has': true
        }
      },
      {
        'name': '图标示例',
        'path': 'demo-icon',
        'url': assetsCdn + '/pages/demo/demo-icon.vue',
        'icon': 'icon-award',
        'meta': {
          'show': true,
          'has': true
        }
      },
      {
        'name': 'Markdown',
        'path': 'demo-markdown',
        'url': assetsCdn + '/pages/demo/demo-markdown.vue',
        'icon': 'icon-file-remove',
        'meta': {
          'show': true,
          'has': true
        }
      },
      {
        'name': '可视化图表',
        'path': 'demo-echarts',
        'url': assetsCdn + '/pages/demo/demo-echarts.vue',
        'icon': 'icon-pie-chart-',
        'meta': {
          'show': true,
          'has': true
        }
      },
      {
        'name': '富文本示例',
        'path': 'demo-content',
        'url': assetsCdn + '/pages/demo/demo-content.vue',
        'icon': 'icon-file-text',
        'meta': {
          'show': true,
          'has': true
        }
      }
    ]
  }
];

export function initRouter () {
  router = new VueRouter({
    routes: defaultRouter
  });
  router.beforeEach(function (to, from, next) {
    if (to.path !== from.path) {
      window['NProgress'] && NProgress.start();
    }
    next();
  });
  router.beforeResolve(function (to, from, next) {
    var title = config.title;
    if (typeof to.name === 'string') {
      title = to.name + ' - ' + config.title;
    }
    document.title = title;

    next && next();
    if (config.debug)
      console.log('%c Go ', 'background:#aaa;color:#117F51', from.path, '->', to.path);
  });
  router.afterEach(function (to, from) {
    window['NProgress'] && NProgress.done(true);
  });
  window['$router'] = router;
  return router;
}

export function requestInit () {
  request = axios.create({
    baseURL: config.baseURL + '/',
    timeout: config.timeout,
    headers: {
      'X-Requested-With': 'xmlhttprequest',
      'post': { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' }
    }
  });
  const urlEncode = arg => {
    const en = (param, key, encode) => {
      let paramStr = '';
      let t = typeof param;
      if (t === 'string' || t === 'number' || t === 'boolean') {
        paramStr +=
          '&' +
          key +
          '=' +
          (encode == null || encode ? encodeURIComponent(param) : param);
      } else {
        for (let i in param) {
          if (param.hasOwnProperty(i)) {
            let k =
              key == null
                ? i
                : key + (param instanceof Array ? '[' + i + ']' : '.' + i);
            paramStr += en(param[i], k, encode);
          }
        }
      }
      return paramStr;
    };
    let data = en(arg);
    return data.substr(0, 1) === '&' ? data.substr(1) : data;
  };
  request.interceptors.request.use(e => {
    const token = store.state.token;
    if (token) {
      e.headers['token'] = token;
    }
    if (typeof (e.data) === 'string' && e.method !== 'post') {
      e.headers.post['Content-Type'] = 'text/plain';
    } else if (typeof (e.data) === 'object') {
      e.headers.post['Content-Type'] = 'application/json;charset=UTF-8';
    }
    if (config.debug)
      console.log('%c HTTP -> ', 'background:#aaa;color:#bada99', e);
    return e;
  }, error => {
    return Promise.reject(error);
  });

  const errHandle = (error) => {
    let v = vm.$root;
    const { useTip } = hook;
    if (!!error.response) {
      switch (error.response.status) {
        case 401:
          store.commit('setToken', '');
          setTimeout(() => v.initSate = true);
          return;
      }
      let data = (typeof error.response.data === 'object') ? error.response.data : {
        code: -1,
        msg: '接口返回非合法格式',
        data: error.response
      };
      // data.msg && useTip().notify('error', data.msg || msg, '温馨提示');
      return Promise.resolve(data);
    } else if (!!error.message && error.message.indexOf('timeout') >= 0) {
      return Promise.reject('网络请求超时');
    }
    return Promise.reject(error);
  };
  request.interceptors.response.use(r => {
    if (config.debug)
      console.log('%c HTTP <- ', 'background:#aaa;color:#bada11', r.data);
    const isJSON = typeof r.data === 'object';
    if (isJSON && (r.data.code >= 200 && r.data.code < 300)) {
      return r.data;
    }
    let err = { response: r };
    if (r.status === 200 && isJSON && r.data.code !== 200) {
      err.response.status = r.data.code;
    }
    return errHandle(err);
  }, (error) => {
    return errHandle(error);
  });
  window['$request'] = request;

  const get = request.get;
  request.get = (url, data, conf = {}) => {
    conf['params'] = data;
    return get(url, conf);
  };

  const put = request.put;
  request.put = (url, data, conf = {}, keep = true) => {
    data = keep ? data : urlEncode(data);
    return put(url, data, conf);
  };

  const post = request.post;
  request.post = (url, data, conf = {}, keep = true) => {
    data = keep ? data : urlEncode(data);
    return post(url, data, conf);
  };

  const del = request.delete;
  request.delete = (url, data, conf = {}, keep = true) => {
    conf['data'] = keep ? data : urlEncode(data);
    return del(url, conf);
  };

  return request;
}
