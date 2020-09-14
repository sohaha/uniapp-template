<template>
	<view class="content">
		<input v-model="username" />
		<input v-model="password" />
		<button @click="login">登录</button>
	</view>
</template>

<script>
	import {login} from "@/apis/modules/user.js";
	import {
		mapState,
		mapGetters
	} from "vuex";
	export default {
		data() {
			return {
				username: "seekwe",
				password: "123456"
			};
		},
		computed: {
			...mapGetters(["userInfo"])
		},
		mounted() {},
		methods: {
			login() {
				this.$log("登录")
				login()
			}
		}
	};
</script>

<style lang='less'>
	.content {
		padding: 20rpx;
	}

	.title {
		font-size: 36upx;
		color: #8f8f94;
	}
</style>
