import App from './App';
// import Store from './store';
import cfg from './config';
import {
	ajax as Api
} from './apis';

// #ifdef H5
import appApi from './apis/util-h5';
// #endif
// #ifndef H5
import appApi from './apis/util-app';
// #endif

import util from './common/util';

// import * as Sentry from "./common/sentry";
// Sentry.init({
//   dsn: "https://47703e01ba4344b8b252c15e8fd980fd@sentry.io/1528228",
// });
// Sentry.captureMessage("Hello, sentry!")


// // #ifdef MP-WEIXIN
// if (process.env.NODE_ENV === 'development') {
// 	uni.setEnableDebug({
// 		enableDebug: true
// 	});
// }
// // #endif

uni.getSystemInfo({
	success: function(res) {
		util.$log('SystemInfo', res);
	}
});

const mounted = async () => {
	await appApi.checkSession();
	try {
		return
		let res = await appApi.getUserInfo();
		if (!res) {
			util.$log('获取到用户信息失败,需要手动登录');
			return;
		}
		util.$log(
			'过期时间|授权状态|远程用户信息',
			cfg.autoTimeout,
			Store.getters.authState
		);
		if (res.code === 200) {
			const user = res.data;
			if (user.nickname) {
				if (cfg.autoTimeout === 0 && Store.getters.authState) {
					util.$log('不用重新获取用户信息', JSON.stringify(Store.getters.userInfo));
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
				util.$log('距离上次获取用户信息相隔: ' + diffTime / 1000 + 's');
				if (diffTime > cfg.autoTimeout) {
					util.$log('需要更新用户信息，重置授权状态');
					Store.commit('USER_SIGNIN', {
						authState: false
					});
					await appApi.getUserAuthInfo();
				}
			} else {
				util.$log('用户信息没有授权状态');
				Store.dispatch('USER_SIGNIN', {
					authState: false
				});
			}
			return;
		}
		const isBan = res.code === 402
		if (isBan) {
			Store.dispatch('USER_SIGNIN', {
				banState: true
			});
		}
		uni.hideTabBar({
			animation: true
		});
		if (util.$PLATFORM === 'h5') {
			util.$log('h5 需要单独一个的登录页面');
			util.$go("/pages/user/login");
		}
		const duration = util.$PLATFORM === 'h5' || isBan ? 9999999 : 300000;
		util.$alert(res.msg, duration, null, {
			mask: true
		});
	} catch (e) {
		console.error(e);
	}
}

import {
	createSSRApp
} from 'vue'
import * as Pinia from 'pinia';

export function createApp() {
	const app = createSSRApp(App)
	app.use(Pinia.createPinia());
	// app.use(Store)

	app.config.globalProperties.$websiteUrl = cfg.websiteUrl;
	// app.config.globalProperties.$store = Store;
	app.config.globalProperties.$api = Api;
	Object.keys(util).forEach((k => {
		app.config.globalProperties[k] = util[k]
	}))
	
	mounted.apply(app)
	return {
		app
	}
}