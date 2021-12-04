/**
 * @Author: seekwe
 * @Date:   2019-11-07 12:44:25
 * @Last Modified by:   seekwe
 * @Last Modified time: 2019-12-18 20:05:59
 */
import util from '@/common/util';

export const SET_TOKEN = (state, token) => {
  state.token = token;
  uni.setStorage({
    key: 'token',
    data: token
  });
};

export const USER_SIGNIN = (state, user) => {
  util.$log('更新用户信息', user);
  if (user.nickname) {
    user.authState = true;
  }
  if (!user.banState) {
    user.banState = false;
  }
  Object.assign(state.user, user);
  uni.setStorage({
    key: 'user',
    data: Object.assign({}, state.user, { banState: false })
  });
};

export const USER_SIGNOUT = state => {
  state.user = {}
};

export const FEEDBACK = (state, token) => {
  state.feedback = true;
};