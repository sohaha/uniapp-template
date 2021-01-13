<script>
  const { createApp, reactive, computed, onMounted, toRef, ref, watch, watchEffect } = vue;
  const { useRouter, useStatic, useStore } = hook;
  export default {
    name: 'homeView',
    setup (prop, ctx) {
      watch(() => [ctx.root.initSate, useStore(ctx).getters.isLogin], ([init, isLogin]) => {
        if (init) {
          let path = '/login';
          if (isLogin) {
            path = '/main';
          }
          useRouter(ctx).push(path);
        }
      }, { immediate: true });
      return {};
    },
    render (h) {
      return h('div', '', { style: { height: '100vh', backgroundColor: '#000000' } });
    }
  };
</script>