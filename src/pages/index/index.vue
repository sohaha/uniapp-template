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
      <image mode="widthFix" class="demo-img" src="/static/my.jpg"/>
      <!-- #endif -->

      <view class="demo-title-box">
        <text class="title" @click="goNextView">{{ title }} - 跳转</text>
        <view>{{ authStore.token }}</view>
        <AuthBtn @click="getuserinfo">{{ loginText }}</AuthBtn>
        <AuthBtn openType="getPhoneNumber" size="mini" @click="getPhoneNumber" :authState="false">获取手机号码</AuthBtn>
        <view v-for="(v, k) in systems" :key="k">{{ k }}: {{ v }}</view>
      </view>
    </view>
    <!-- #ifdef MP-WEIXIN -->
    <button v-if="authStore.feedback" open-type="feedback" style="position:fixed;margin:auto;right:0;width:200rpx;left:0;bottom:200rpx;background-color:red;border-radius:10rpx;color:#fff;">上报错误</button>
    <!-- #endif -->
  </view>
</template>

<script setup>
import {onShareAppMessage} from '@/common/mixins';
import AuthBtn from '@/components/AuthBtn';
import {$systems} from '@/common/util';
import { useAuthStore } from '@/stores/auth';
import {computed, ref} from 'vue';

const title = ref('Hello App')

const authStore = useAuthStore()


const   loginText = computed(()=>{
      return !authStore.isLogin ? '登  录' : '已登录';
   }) 
</script>

<script>

// import {mapState, mapGetters} from 'vuex';

// export default {
//   onShareAppMessage,
//   components: {
//     AuthBtn
//   },
//   data() {
//     return {
//       systems: this.$systems()
//     };
//   },
//   watch: {
// 		showFeedback(n) {
// 			if (n) {
//         // #ifdef MP-WEIXIN
// 				uni.showModal({
// 					title: '抱歉',
// 					content: '程序发生了异常，请尝试上报给开发者，以便尽快修复，谢谢！',
// 					showCancel: false,
//         });
//         // #endif
// 			}
// 		},
// 	},
//   computed: {
// 		showFeedback() {
// 			return this.$store.getters.showFeedback;
// 		},
//     token() {
//       return this.$store.getters.token || '-';
//     },
//     loginText() {
//       return !this.authState ? '登  录' : '已登录';
//     },
//     // ...mapGetters(['userInfo', 'authState', 'banState'])
//   },
//   mounted() {
//   },
//   methods: {
//     getShareAppMessageConfig() {
//       console.log('自定义分享信息');
//       return {
//         title: '自定义标题',
//         param: {name: 'hahaha', so: 'xixixi'}
//       };
//     },
//     getuserinfo(detail) {
//       // #ifndef MP-WEIXIN
//       this.$alert('H5 登录');
//       return;
//       // #endif
//       if (detail) {
//         if (detail.iv) {
//           return this.$api('user.getUserInfo', detail)
//               .then(e => {
//                 if (e.code === 200) {
//                   // 保存用户
//                   this.$store.commit('USER_SIGNIN', e.data);
//                   this.$alert('你来自: ' + e.data.city);
//                 } else {
//                   this.$alert(e.msg);
//                 }
//               })
//               .catch(e => {
//                 console.warn(e);
//               });
//         } else {
//           this.$alert('拒绝了小程序登录');
//         }
//       } else {
//         console.log("已经授权过的用户",this.userInfo);
//       }
//     },
//     getPhoneNumber(e){
//       console.log(e)
//     },
//     goNextView() {
//       // 页面跳转
//       this.$go('user/login', true).then(e => {
//         console.log(e)
//       })
//     }
//   }
// };
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
