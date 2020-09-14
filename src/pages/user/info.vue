<template>
  <view class="content">
    <view class="title" v-for="(v,k) in userInfo" :key="k">{{k}}: {{v}}</view>
  </view>
</template>

<script>
import { mapState, mapGetters } from "vuex";
export default {
  data() {
    return {};
  },
  computed: {
    ...mapGetters(["userInfo"])
  },
  mounted() {},
  methods: {}
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
