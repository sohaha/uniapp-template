/**
 * @Author: seekwe
 * @Date:   2019-11-07 12:44:25
 * @Last Modified by:   seekwe
 * @Last Modified time: 2019-12-17 15:59:14
 */

export const userInfo = state => state.user;
// 如果获取过用户信息那么就不会再进行sessionKey的过期验证了
export const authState = state => state.user.authState;
// 与后端通信 Token
export const token = state => state.token;
// 用户是否被禁止
export const banState = state => state.user.banState;
// 是否发生了异常
export const showFeedback = state => state.feedback;