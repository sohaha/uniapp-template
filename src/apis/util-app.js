import { ajax } from './index';
import { getToken } from './index';
import { request } from './index';
import $store from '../store';
import util from '@/common/util';

export default {
  async checkSession () {
    const token = $store.getters.token;
    const authStatus = $store.getters.authStatus;
    if (!token || !authStatus) {
      request.lock();
    }
    if (!token) {
      // token 都没有，需要再次获取
      await getToken(_ => {
        util.$log('session_key 已重新获取');
        request.unlock();
      });
    } else if (!authStatus) {
      // 本地还没获取过用户信息，需要保证 session_key 有效期
      wx.checkSession({
        async success () {
          request.unlock();
          util.$log('session_key 未过期');
        },
        async fail () {
          util.$log('session_key 已经失效');
          await getToken(_ => {
            util.$log('session_key 已重新获取');
            request.unlock();
          });
        }
      });
    }
  },
  async getLoginCode () {
    return await new Promise((resolve, reject) => {
      wx.login({
        success (res) {
          if (res.code) {
            resolve(res.code);
          }
        }
      });
    });
  },
  async getUserInfo () {
    return await ajax('user.info');
  },
  async getUserAuthInfo () {
    const [res, err] = await util.$awaitWrap(util.$app.getSetting());
    if (err === null && res.authSetting['scope.userInfo']) {
      uni.getUserInfo({
        success (infoRes) {
          util.$log('获取最新用户资料', infoRes.userInfo);
          ajax('user.update', infoRes.userInfo).then(e => {
            $store.dispatch('USER_SIGNIN', e.data);
          });
        },
      });
    } else {
      util.$log('getauthSetting', res, err);
    }
  }
};
