<template>
	<view class="z-main demo-main">
		<view v-if="banState" class="demo-title-box">对不起用户已经被禁了</view>
		<view v-else>
			<!-- <div class="z-status_height"> -->
			<!-- 这里是状态栏展位 -->
			<!-- </div> -->

			<!-- #ifdef MP-WEIXIN || MP-BAIDU || MP-QQ -->
			<open-data type="userAvatarUrl" class="demo-img"></open-data>
			<view class="demo-title-box">
				<open-data type="userCity"></open-data>
			</view>
			<!-- #endif -->

			<!-- #ifndef MP-WEIXIN || MP-BAIDU || MP-QQ -->
			<image mode="widthFix" class="demo-img" src="/static/my.jpg" />
			<!-- #endif -->

			<view class="demo-title-box">
				<text class="title" @click="goLogin">{{ title }}</text>
				<view>{{ token }}</view>
				<button
					size="mini"
					@click="clickGetuserinfo"
					@getuserinfo="getuserinfo"
					open-type="getUserInfo"
				>{{ loginText }}</button>
				<view v-for="(v, k) in systems" :key="k">{{ k }}: {{ v }}</view>
			</view>
		</view>
	</view>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import { onShareAppMessage } from '@/common/mixins';
import { $systems } from '@/common/util';
export default {
	onShareAppMessage,
	components: {},
	data() {
		return {
			title: 'Hello App',
			systems: this.$systems()
		};
	},
	computed: {
		token() {
			return this.userInfo.token || '-';
		},
		loginText() {
			return !this.authState ? '登  录' : '已登录';
		},
		...mapGetters(['userInfo', 'authState', 'banState'])
	},
	mounted() { },
	methods: {
		getShareAppMessageConfig() {
			console.log('自定义分享信息');
			return {
				title: '自定义标题',
				param: { name: 'hahaha', so: 'xixixi' }
			};
		},
		clickGetuserinfo(e) {
			// #ifndef MP-WEIXIN
			this.$alert('H5 登录');
			// #endif
		},
		getuserinfo(v) {
			if (v.detail.iv) {
				return this.$api('user.getUserInfo', v.detail)
					.then(e => {
						if (e.code === 200) {
							// 保存用户
							this.$store.commit('USER_SIGNIN', e.data);
							this.$alert('你来自: ' + e.data.city);
						} else {
							this.$alert(e.msg);
						}
					})
					.catch(e => {
						console.warn(e);
					});
			} else {
				this.$alert('拒绝了小程序登录');
			}
		},
		goLogin() {
			this.$go('user/login', true);
		}
	}
};
</script>

<style lang="less">
.demo-main {
	padding-top: 20rpx;
	min-height: 100vh;
	background-color: #fff;
}

.demo-title-box {
	text-align: center;
	margin: 10rpx 50rpx 0;
	padding-bottom: 50rpx;
	color: #2a3244;
	overflow: hidden;
}

.demo-img {
	width: 200rpx;
	height: 200rpx;
	margin: 0 auto 20rpx;
	display: block;
	border-radius: 100%;
	overflow: hidden;
	box-shadow: 2px 2px 5px;
}
</style>
