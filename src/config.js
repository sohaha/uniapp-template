let apiHost, websiteUrl;
let [appName, apiTimeout, autoTimeout] = ['小程序示例', 30000, 10]; //小程序默认名称, 接口超时时间ms, 重新授权获取用户信息间隔时间ms(0不会过期)

if (process.env.NODE_ENV !== 'development') {
    apiHost = 'http://weapp.test'; // 接口域名
    websiteUrl = 'http://weapp.test'; // 静态资源域名
} else {
    apiHost = 'http://127.0.0.1:3783'; // 测试接口域名
    websiteUrl = 'http://127.0.0.1:3783'; // 测试静态资源域名
}

export let apiDataKeepJSON = true;

export default {
    websiteUrl,
    apiHost,
    appName,
    apiTimeout,
    autoTimeout,
};
