/**
 * @Author: seekwe
 * @Date:   2019-11-07 12:44:25
 * @Last Modified by:   seekwe
 * @Last Modified time: 2019-12-17 16:54:45
 */
import Vue from 'vue';
import App from './App';
import Store from './store';
import cfg from './config';
import { ajax as Api } from './apis';

// #ifdef H5
import appApi from './apis/util-h5';
// #endif
// #ifndef H5
import appApi from './apis/util-app';
// #endif

import util from './common/util';

Object.assign(Vue.prototype, util);

// #ifdef MP-WEIXIN
if (process.env.NODE_ENV === 'development') {
  uni.setEnableDebug({
    enableDebug: true
  });
}
// #endif

Vue.config.productionTip = false;

Vue.prototype.$websiteUrl = cfg.websiteUrl;
Vue.prototype.$store = Store;
Vue.prototype.$api = Api;
Vue.prototype.$eventHub = Vue.prototype.$eventHub || new Vue();

App.mpType = 'app';
uni.getSystemInfo({
  success: function (res) {
    util.$log('SystemInfo', res);
  }
});
const app = new Vue({
  components: {},
  async mounted () {
    await appApi.checkSession();
    try {
      let res = await appApi.getUserInfo();
      if (!res) {
        this.$log('获取到用户信息失败,需要手动登录');
        return;
      }
      this.$log(
        '过期时间|授权状态|远程用户信息',
        cfg.autoTimeout,
        Store.getters.authState
      );
      if (res.code === 200) {
        const user = res.data;
        if (user.nickname) {
          if (cfg.autoTimeout === 0 && Store.getters.authState) {
            this.$log('不用重新获取用户信息', JSON.stringify(Store.getters.userInfo));
            return;
          }
          // if (!Store.getters.authState) {
          //   this.$log('没有离线用户信息，需要更新');
          //   this.$store.dispatch('USER_SIGNIN', user);
          //   return;
          // }
          const updateTime = new Date(user.update_time.replace(/-/g, '/'));
          const now = new Date().getTime();
          const diffTime = now - updateTime;
          this.$log('距离上次获取用户信息相隔: ' + diffTime / 1000 + 's');
          if (diffTime > cfg.autoTimeout) {
            this.$log('需要更新用户信息，重置授权状态');
            this.$store.commit('USER_SIGNIN', { authState: false });
            await appApi.getUserAuthInfo();
          }
        } else {
          this.$log('用户信息没有授权状态');
          this.$store.dispatch('USER_SIGNIN', { authState: false });
        }
        return;
      }
      const isBan =res.code === 402
      if(isBan){
        this.$store.dispatch('USER_SIGNIN', { banState: true });
      }
      let duration = util.$PLATFORM === 'h5' || isBan ? 9999999 : null;
      uni.hideTabBar({ animation: true });
      if (util.$PLATFORM === 'h5') {
        this.$log('h5 需要独立的登录页面');
      }
      this.$alert(res.msg, duration, null, {
        mask: true
      });
    } catch (e) {
      console.error(e);
    }

  },
  methods: {},
  ...App
});
app.$mount();
