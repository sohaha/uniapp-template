import {
    request
} from "../index"

export default {
    getToken: ['post', '/WeAppApi/getToken.go'],
    getWebToken: ['post', '/WeAppApi/getWebToken.go'],
    getUserInfo: ['post', '/WeAppApi/getUserInfo.go'],
    login: ['post', '/H5Api/login.go'],
    update: ['post', '/WeAppApi/update.go'],
    info: ['get', '/WeAppApi/info.go'],
    uploadAvatar: '/WeAppApi/uploadAvatar.go',
};

export function getToken(data, request) {
    // #ifdef H5
    return request.post('/WeAppApi/getToken.go', data)
    // #endif
    // #ifndef H5
    return request.post('/WeAppApi/getToken.go', data)
    // #endif
}
