/**
 * @Author: seekwe
 * @Date:   2019-11-07 12:44:25
 * @Last Modified by:   seekwe
 * @Last Modified time: 2019-12-16 13:59:10
 */
import Vue from 'vue';
import Vuex from 'vuex';
import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';
import modules from './modules';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    token: uni.getStorageSync('token') || '', // 用户权鉴
    feedback: false, // 是否出现异常了
  },
  actions,
  getters,
  mutations,
  modules,
  strict: process.env.NODE_ENV !== 'production' //使用严格模式
});
