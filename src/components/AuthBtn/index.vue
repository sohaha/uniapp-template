<template>
  <button
      class="z-auth-btn"
      :size="size"
      :class="customClass"
      :open-type="btnOpenType"
      @click="click"
      @getuserinfo="getUserInfo"
      @getphonenumber="getPhoneNumber"
  >
    <slot/>
  </button>
</template>
<script>
import {mapState, mapGetters} from 'vuex';

export default {
  props: {
    // 自定义样式类
    customClass: {
      type: String,
      default: ''
    },
    // openType 合法值: https://developers.weixin.qq.com/miniprogram/dev/component/button.html
    openType: {
      type: String,
      default: 'getUserInfo'
    },
    // 用户是否授权了
    authState: {
      type: [Boolean],
      default: null
    },
    size: {
      default: 'default'
    }
  },
  computed: {
    btnOpenType() {
      let authState = this.authState;
      if (authState === null) {
        if (this.openType === 'getUserInfo') {
          authState = this.getAuthState;
        }
      }
      return authState ? '' : this.openType;
    },
    ...mapGetters({getAuthState: 'authState'})
  },
  methods: {
    click() {
      if (!this.btnOpenType) {
        this.$emit('click');
      }
    },
    getUserInfo({detail}) {
      this.$emit('click', detail);
    },
    getPhoneNumber({detail}) {
      this.$emit('click', detail);
    },
  }
};
</script>
<style scoped lang="less">
.z-auth-btn {
  background: none;

  &::after {
    border: none;
  }
}

</style>