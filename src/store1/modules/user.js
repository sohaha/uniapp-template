/**
 * @Author: seekwe
 * @Date:   2019-11-07 12:44:25
 * @Last Modified by:   seekwe
 * @Last Modified time: 2019-12-18 18:33:18
 */
export default {
  namespaced: true,
  state: uni.getStorageSync('user') || {
    username: '影浅',
    authState: false,
    banState: false,
    author: 'seekwe@gmail.com'
  },
  getters: {}
};
