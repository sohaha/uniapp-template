const {ref, toRefs, isRef, reactive, watch} = vue;

const getFn = (fn) => {
  return typeof fn === 'function' ? fn : (e) => e;
}

const getOptions = (o) => {
  if (typeof o === 'object') {
    return o
  }
  if (o) {
    return {}
  }
  return {manual: true}
}

const runApi = (e) => {
  const [m, u, d, o, k] = e;
  return app.request[m.toLowerCase()](u, d, o, k);
}

const isProm = (e) => {
  return !!e && (typeof e === 'object' || typeof e === 'function') && typeof e.then === 'function'
}

export function useRequestPage(fn, page, o) {
  o = getOptions(o);
  const dataHandle = getFn(o['dataHandle']);
  let data = reactive({
    key: page.key || '',
    items: [],
    pages: {pagesize: page.pagesize || 10, curpage: page.page || 1, total: 0},
  })
  o['dataValue'] = {items: [], pages: {}}
  o['dataHandle'] = (e) => {
    e = dataHandle(e);
    data.items = ref(e.items);
    e.page.pagesize = data.pages.pagesize;
    data.pages = ref(e.page);
    return e;
  }
  const res = useRequestWith(() => {
    let param = {pagesize: data.pages.pagesize, page: data.pages.curpage}
    if (!!data.key) {
      if (Object.prototype.toString.call(data.key) === '[object Object]') {
        param = Object.assign(param, data.key)
      } else if (Object.prototype.toString.call(data.key) === '[object String]') {
        param['key'] = data.key;
      }
    }
    return fn(param)
  }, o);
  data.reload = res.run;
  data.error = res.error;
  data.data = res.data;
  data.loading = res.loading;
  data.run = res.run;
  return {
    change() {
      res.run()
    },
    search(key) {
      data.key = key;
      data.pages.curpage = 1;
      res.run();
    },
    ...toRefs(data)
  };
}

export function useRequestWith(fn, o = {}) {
  o = getOptions(o);
  const dataHandle = getFn(o['dataHandle']);
  o['dataHandle'] = (e) => {
    const err = '接口异常';
    if (!e || typeof e !== 'object' || !e.code) {
      throw err;
    } else if (e.code <= 209 && e.code >= 200) {
      return dataHandle(e.data);
    }
    throw (e.msg || err);
  };
  return useRequest(fn, o);
}

export function useRequest(fn, o = {}) {
  o = getOptions(o);
  const {manual, pollingInterval, repeat, dataHandle, dataValue} = o;
  const {loading, withLoading} = hook.useLoading();
  let data = ref(dataValue === undefined ? {} : dataValue), error = ref(''), run = (...d) => {
    if (repeat || !loading.value) {
      error.value = '';
      return withLoading(() => {
        let e = (typeof fn === 'function' ? fn(...d) : (isProm(fn) ? fn : runApi(fn))), p;
        if (isProm(e)) {
          p = e;
        } else {
          p = runApi(e);
        }
        return p.then(e => {
          data.value = dataHandle ? dataHandle(e) : e;
          return [data.value, null];
        }).catch(e => {
          error.value = e;
          return [{}, e];
        })
      });
    }
    return [{}, "请求繁忙"];
  }, cancel = () => {
  };
  if (!manual) {
    run();
  }
  return {
    loading,
    withLoading,
    data,
    error,
    cancel,
    run
  };
}
