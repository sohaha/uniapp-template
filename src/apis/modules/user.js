/*
 * @Author: seekwe
 * @Date: 2020-07-29 16:54:03
 * @Last Modified by:: seekwe
 * @Last Modified time: 2020-07-30 17:17:30
 */ 
/**
 * @Author: seekwe
 * @Date:   2019-11-07 13:36:40
 * @Last Modified by:   seekwe
 * @Last Modified time: 2019-12-17 16:23:24
 */
import {
	request
} from "../index"

export default {
	getToken: ['post', '/WeAppApi/getToken.go'],
	getWebToken: ['post', '/WeAppApi/getWebToken.go'],
	getUserInfo: ['post', '/WeAppApi/getUserInfo.go'],
	login: ['post', '/H5Api/login.go'],
	update: ['post', '/WeAppApi/update.go'],
	info: ['get', '/WeAppApi/info.go']
};

export function getToken(data, request) {
	// #ifdef H5
	return request.post('/WeAppApi/getToken.go', data)
	// #endif
	// #ifndef H5
	return request.post('/WeAppApi/getToken.go', data)
    // #endif
}


export function login() {
	console.log("登录")
	return request.post('/H5Api/login.go')
}
