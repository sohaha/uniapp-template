// stores/counter.js
import { defineStore } from 'pinia';

export const useCounterStore = defineStore('counter', {
    state: () => {
        return uni.getStorageSync('user') || {
            username: '影浅',
            authState: false,
            banState: false,
            author: 'seekwe@gmail.com'
        };
    },
    // 也可以这样定义
    // state: () => ({ count: 0 })
    actions: {
        increment() {
            this.count++;
        },
    },
});