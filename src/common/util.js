const $alert = (text, duration, success = null, opt = {}) => {
	let defOpt = {
		title: text,
		duration: duration || 3000,
		icon: success === true ? 'success' : success === false ? 'loading' : 'none'
	};
	if (typeof success === 'string') {
		defOpt.image = success;
	}
	uni.showToast(Object.assign(defOpt, opt));
};

const $loading = (text = '加载中') => {
	uni.showLoading({
		title: text
	});
	return uni.hideLoading;
};

const $go = (page, goType = false) => {
	let url = {
		url: page.indexOf('.') === 0 || page.indexOf('/') === 0 ?
			page :
			'/pages/' + page
	};
	if (goType === true) {
		return uni.redirectTo(url);
	} else if (goType === false) {
		return uni.navigateTo(url);
	} else if (typeof goType === 'string' && typeof uni[goType] === 'function') {
		return uni[goType](url);
	} else {
		return uni.reLaunch(url);
	}
};

export const $is = {
	object(arg) {
		return typeof arg === 'object' && arg !== null;
	}
};

function $log(...msg) {
	if (process.env.NODE_ENV === 'development') {
		console.log('%c LOG ', 'background:#aaa;color:#bada55', ...msg);
	}
}

export const $app = {
	async getSetting() {
		return await new Promise((resolve, reject) => {
			uni.getSetting({
				success(res) {
					resolve(res);
				},
				fail(err) {
					reject(err);
				}
			});
		});
	}
};
const systems = uni.getSystemInfoSync();
export const $systems = _ => {
	return systems;
};

export const $awaitWrap = promise => {
	return promise.then(data => [data, null]).catch(err => [null, err]);
};

export default {
	$go,
	$alert,
	$loading,
	$log,
	$is,
	$app,
	$awaitWrap,
	$systems,
	$PLATFORM: process.env.VUE_APP_PLATFORM
};
