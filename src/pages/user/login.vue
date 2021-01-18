<template>
  <view class="content">
    <button @click="upload">上传文件</button>
    <image v-if="avatar" mode="widthFix" class="demo-img" :src="avatar"/>
  </view>
</template>

<script>
import {
  mapState,
  mapGetters
} from "vuex";

import {apiToken} from "@/apis";

export default {
  data() {
    return {
      avatar: ''
    };
  },
  computed: {
    ...mapGetters(["userInfo"])
  },
  mounted() {

  },
  methods: {
    upload() {
      const t = this;
      wx.chooseImage({
        success(res) {
          const tempFilePaths = res.tempFilePaths;
          t.$api('user.uploadAvatar').then((url) => {
            uni.uploadFile({
              url: url,
              header: apiToken(),
              filePath: tempFilePaths[0],
              name: 'file',
              formData: {
                'language': t.userInfo['language'],
                'nickname': t.userInfo['nickname'],
              },
              success: (v) => {
                const data = JSON.parse(v.data);
                if (data.code === 200) {
                  t.avatar = data.data.url;
                  return;
                }
                t.$log(data);
              }
            })
          })
        }
      })
    }
  }
};
</script>

<style lang='less'>
.content {
  padding: 20 rpx;
}

.title {
  font-size: 36 upx;
  color: #8f8f94;
}
</style>
