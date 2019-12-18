/**
 * @Author: seekwe
 * @Date:   2019-11-07 12:44:25
 * @Last Modified by:   seekwe
 * @Last Modified time: 2019-12-17 17:15:42
 */
import cfg from '../config';
import util from '../common/util';
import store from '../store';
import Fly from 'flyio/dist/npm/wx';
import qs from 'qs';
// #ifndef H5
import appApi from './util-app';
// #endif
// #ifdef H5
import appApi from './util-h5';
// #endif

// https://wendux.github.io/dist/#/doc/flyio/readme
export const fly = new Fly();
const newFly = new Fly();
const files = require.context('./modules', false, /\.js$/);
let apis = {},
  defModule = {};

files.keys().forEach(key => {
  if (key === './index.js') {
    defModule = files(key).default;
    return;
  }
  apis[key.replace(/(\.\/|\.js)/g, '')] = files(key).default;
});

// if (typeof Promise.prototype['finally'] === 'undefined')
Promise.prototype['finally'] = function (callback) {
  let P = this.constructor;
  return this.then(
    value => P.resolve(callback(value)),
    err => P.resolve(callback(err))
  );
};

// if (typeof Promise.prototype['done'] === 'undefined')
Promise.prototype['done'] = function (onResolved, onRejected) {
  this.then(onResolved, onRejected).catch(function (err) {
    setTimeout(() => {
      throw err;
    }, 0);
  });
};

const log = (...msg) => {
  if (process.env.NODE_ENV === 'development') {
    console.log('%c API ', 'background:#f5f5dc;color:#bada55', ...msg);
  }
};
const noJSON = (data) => {
  if (typeof data === 'string') {
    util.$log('NoJSON');
    return { code: 200, msg: 'toJson', data: data };
  }
  return null;
};
const api = async (
  name,
  data = {},
  success,
  fail = () => {},
  complete = () => {}
) => {
  let api = '';

  if (typeof name === 'string') {
    const moduleArr = name.split('.');
    const module = apis[moduleArr[0]] || '';
    if (!!module) {
      api = moduleArr[1] ? module[moduleArr[1]] : '';
    } else {
      api = defModule[name];
    }
  } else {
    api = name;
  }

  if (!api) {
    log('Api Non existent: ' + name);
    return;
  }

  if (api === 'string') {
    return api;
  }

  let [type, url] = api;
  let res;
  if (typeof success === 'function') {
    res = await fly
    .request(url, qs.stringify(data), {
      method: type
    })
    .then(e => {
      log(type.toUpperCase(), url, data, e);
      return success(e);
    })
    .catch(e => {
      log('catch', e);
      log(type.toUpperCase() + '(catch)', url, data, e);
      return fail(e);
    })
    .finally(complete);
  } else {
    res = fly
    .request(url, qs.stringify(data), {
      method: type
    })
    .then(e => {
      log(type.toUpperCase(), url, data, e);
      return e;
    });
  }

  return res;
};

fly.config.baseURL = cfg.apiHost;
fly.config.timeout = cfg.apiTimeout;
fly.config.headers = {};

// 添加请求拦截器
fly.interceptors.request.use(
  request => {
    let token = store.getters.token;
    if (token) {
      request.headers['token'] = token;
    }
    request.headers['platform'] = process.env.VUE_APP_PLATFORM;
    request.headers['x_requested_with'] = 'xmlhttprequest';
    return request;
  },
  err => {
    return Promise.reject(err);
  }
);

let getTokenStatsu = false;
// 添加响应拦截器
fly.interceptors.response.use(
  async response => {
    const data = response.data;
    const no = noJSON(data);
    if (no) {
      return no;
    }
    if (data['code'] === 401) {
      log('NoLogin', getTokenStatsu);
      if (!getTokenStatsu) {
        // apis["user"] && apis["user"]["getToken"]
        getTokenStatsu = true;
        setTimeout(_ => {
          getTokenStatsu = false;
        }, 20000);
        newFly.config = fly.config;

        // #ifdef H5
        log('h5 不能预获取 Token');
        return data;
        // #endif

        return await getToken(_ => {
          return fly.request(response.request).then(e => {
            return e;
          });
        }) || data;
      }
      return Promise.reject('NoLogin');
    }
    return data;
  },
  err => {
    const message = err.message.trim();
    if (message === 'request:ok') {
      const status = err.status;
      let data = err.response.data;
      const no = noJSON(data);
      if (no) {
        data = { code: 200, msg: 'toJson', data: data };
      }
      return Promise.resolve(data);
    } else if (message === 'request:fail') {
      util.$alert('网络繁忙');
    }
    return Promise.reject(message);
  }
);

export const getToken = async (fn = _ => {}, errFn = _ => {}) => {
  fly.lock(); // 锁住请求队列，先执行获取token操作
  let code = await appApi.getLoginCode();
  newFly.config = fly.config;
  try {
    let url = '';
    // #ifdef H5
    url = apis['user']['getWebToken'][1];
    // #endif
    // #ifndef H5
    url = apis['user']['getToken'][1];
    // #endif
    const e = await newFly
    .post(url, qs.stringify({ code: code }));
    const data = e && e.data ? e.data : {};
    log('Login', data);
    if (data.code === 200 && data.data['token']) {
      store.commit('SET_TOKEN', data.data['token']);
      fly.unlock();
      return fn(data);
    }
    fly.unlock();
    return null;
  } catch (e) {
    return e;
  }
};

export const keys = Object.keys(apis);
export const ajax = api;
export default Object.assign({}, defModule, keys);
