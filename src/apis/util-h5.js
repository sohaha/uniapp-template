import { ajax, getToken } from './index';
import { request } from './index';
import $store from '../store';
import util from '@/common/util';
export default {
  async checkSession() {
    util.$log('验证 session');
  },
  async getLoginCode(){
    util.$log('获取登录Code，有些平台没有');
    return ''
  },
  async getUserInfo() {
    util.$log('从服务端获取用户信息');

    return await ajax('user.info');
  },
  async getUserAuthInfo() {
    util.$log('获取授权用户信息');
  }
};
