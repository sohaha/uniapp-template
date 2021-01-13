const { ref, toRef, watch, computed, onMounted, onUnmounted, getCurrentInstance } = vue;
let vm;

export function setVm(root) {
  vm = root || this;
  app.setVm(vm);
}

// 路由控制
export function useRouter(ctx) {
  if (!ctx) ctx = getCurrentInstance();
  const root = (ctx && (ctx.root || ctx.$root)) || {};
  const router = root.$router;
  const route = root.$route;

  if (!router || !route) return console.error('使用useRouter前请全局注入vue-router');

  const go = (index) => {
    if (!index) return;
    router.go(index);
  };

  const replace = (path, query) => {
    return router.replace({
      path: getPath(path, route),
      query: query
    });
  };

  const push = (path, query) => {
    return router.push({
      path: getPath(path, route),
      query: query
    });
  };

  const getQuery = () => {
    return route.query;
  };

  const children = () => {
    let routes = {
      children: router.options.routes
    };
    const matched = route.matched;
    for (let i = 0; i < matched.length - 1; i++) {
      routes = routes.children.find((e) => (e.name === matched[i].name));
    }

    return routes.children;
  };

  const resetRouter = (routerData = [], global = false) => {
    return app.resetRouter(router)(routerData, global);
  };

  return {
    children,
    resetRouter,
    router,
    route,
    go,
    replace,
    push,
    getQuery
  };
}
function getPath(path, route) {
  if (path.indexOf('/') === 0) {
    return path;
  }
  const f = route.fullPath.split('/');
  return [...f, path].join('/');
}

// 状态管理
export function useStore(ctx, module) {
  if (!ctx) ctx = getCurrentInstance();
  const root = (ctx && (ctx.root || ctx.$root)) || {};
  if (!root.$store && !window['$store']) return console.error('使用useStore前请全局注入vuex');
  let store = root.$store || window['$store'];
  let state = store.state;
  let getters = store.getters;
  let commit = store.commit;
  let dispatch = store.dispatch;
  if (module) state = state.module;
  return {
    state,
    commit,
    getters,
    dispatch
  };
}

// 存储数据
export function useCache(option, storage) {
  if (!storage) storage = localStorage;
  if (!option) option = {};
  const keyBase = option.keyBase || '';
  const expires = option.expires || 0;
  const removeCache = (key) => {
    let currentKey = keyBase + key;
    storage.removeItem(currentKey);
  };
  return {
    getCache: (key, def) => {
      let currentKey = keyBase + key;
      try {
        let o = JSON.parse(storage.getItem(currentKey));
        if (!o || (!!o.expires && o.expires < Date.now())) {
          o && removeCache(key);
          return def;
        } else return o.value;
      } catch (e) {
        console.log(e);
        return storage[currentKey];
      } finally {
      }
    },
    setCache: (key, value, expi) => {
      let currentKey = keyBase + key;
      let currentExp = expi || expires || 0;
      if (value === undefined) return storage.removeItem(currentKey);
      if (currentExp) {
        currentExp = (new Date().getTime()) + (currentExp * 1000);
      }
      storage.setItem(currentKey, JSON.stringify({
        value,
        expires: currentExp
      }));
    },
    removeCache
  };
}

// 加载状态
export function useLoading() {
  const loading = ref(false);
  const setLoading = value => {
    loading.value = value;
  };
  const withLoading = (task, { autocomplete = true } = {}) => {
    return Promise.resolve()
      .then(() => {
        loading.value = true;
        return task();
      })
      .then(e => {
        autocomplete && setLoading(false);
        return e;
      })
      .catch(e => {
        autocomplete && setLoading(false);
        return e
      });
  };
  return {
    loading,
    setLoading,
    withLoading
  };
}

const ele = window['ELEMENT'];

// 显示提示
export function useTip() {
  const getOpt = (type, message, duration = 3000) => {
    if (!message && !type) return;
    if (typeof type === 'object')
      return Object.assign({ duration: duration }, type);
    if (!message) {
      [type, message] = [message, type];
    }
    if (!type) type = 'info';
    return { message, type, duration };
  };
  const message = function (type, tip, duration) {
    ele.Message(Object.assign({
      showClose: true,
      center: true,
    }, getOpt(type, tip, duration)));
  };
  const notify = function (type, tip, title = '温馨提示', duration) {
    ele.Notification(Object.assign({
      customClass: 'custom-notify-class',
      title: title,
      offset: 58
    }, getOpt(type, tip, duration)));
  };
  return { message, notify };
}

export function useConfirm() {
  var defaultCft = {
    center: true,
    showCancelButton: true,
    showConfirmButton: true
  }

  const warning = function (title, message, confirmFn, cancelFn, opt) {
    if (typeof opt !== 'object') opt = {};
    ele.MessageBox(Object.assign(defaultCft, {
      title: title,
      message: message,
      type: 'warning',
      callback: function (type) {
        switch (type) {
          case 'confirm':
            if (typeof confirmFn === 'function') confirmFn();
            break;
          case 'cancel':
            if (typeof cancelFn === 'function') cancelFn();
            break;
          default:
        }
      }
    }, opt))
  };
  return { warning };
}

export const usePageData = (fetchApi, form) => {

};

export function useHttp() {
  return axios;
}

export function useWebWorker(worker) {
  const code = worker.toString();
  const blob = new Blob(['(' + code + ')()']);
  return new Worker(URL.createObjectURL(blob));
}

export function useWindowSize() {
  let ban;
  const width = ref(window.innerWidth), height = ref(window.innerHeight), update = () => {
    if (ban) {
      clearTimeout(ban);
    }
    ban = setTimeout(() => {
      width.value = window.innerWidth;
      height.value = window.innerHeight;
    }, 200);

  };
  onMounted(() => {
    window.addEventListener('resize', update);
  });
  onUnmounted(() => {
    window.removeEventListener('resize', update);
  });

  return { width, height };
}

export function useWindowSizeRealTime() {
  const width = ref(window.innerWidth), height = ref(window.innerHeight), update2 = () => {
    width.value = window.innerWidth;
    height.value = window.innerHeight;
  };
  onMounted(() => {
    window.addEventListener('resize', update2);
  });
  onUnmounted(() => {
    window.removeEventListener('resize', update2);
  });

  return { width, height };
}
