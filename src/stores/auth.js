import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
    state: () => {
        return {
            token:uni.getStorageSync('token')|| '',
            feedback: false, // 是否出现异常了
        };
    },
    getters:{
        isLogin(){
            return !!this.token
        }
    },
    actions: {
    },
});