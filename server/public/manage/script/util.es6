const {ref, toRef, watch, computed, onMounted, onUnmounted, getCurrentInstance, nextTick} = vue;

export default {};

export const utilTest = 'is test';

export function initWindowFunc() {
  window['arrayAdd'] = function (arr, v) {
    var i = arr.indexOf(v);
    if (i < 0) {
      arr.push(v);
    }
  };

  window['arrayReduce'] = function (arr, v) {
    var i = arr.indexOf(v);
    if (i >= 0) {
      arr.splice(i, 1);
    }
  };
}

export function useInitTitle(ctx) {
  let title = ref(hook.useStore(ctx).state.viewTitle);
  watch(() => hook.useStore(ctx).state.viewTitle, (val) => {
    title.value = val;
  })

  let SpaTitle = ref('SpaTitle');
  onMounted(() => {
    if (!SpaTitle.value && title.value) {
      SpaTitle.value = title.value + ' - %s';
    }
  })

  return {
    title,
    SpaTitle
  }
}

export function useMlSearchKeyObserver(ctx, ml_searchKey) {
  let useRouter = hook.useRouter;
  ml_searchKey.value = !useRouter(ctx).route.query.hasOwnProperty('key') ? '' : useRouter(ctx).route.query.key;
  let searchKey = computed(() => {
    return !useRouter(ctx).route.query.hasOwnProperty('key') ? '' : useRouter(ctx).route.query.key;
  })

  watch(searchKey, (val) => {
    ml_searchKey.value = val;
  })
}

export function useInitPage() {
  let ml_listsLoading = ref(false);
  let ml_searchKey = ref('');
  let ml_page = ref(1);
  let ml_data = ref([]);
  let ml_pagetotal = ref(0);
  let ml_pagesize = ref(10);
  let ml_pages = ref({});
  let ml_change = ref(1);

  watch(ml_page, (val, prevVal) => {
      ml_change.value++;
    },
    {
      immediate: true
    }
  )

  function ml_currentChange(e) {
    ml_page.value = e;
  }

  function ml_sizeChange() {
    ml_searchRow();
  }

  function ml_reloadLists() {
    ml_change.value++;
  }

  function ml_searchRow() {
    if (ml_page.value !== 1) {
      ml_page.value = 1;
    } else {
      ml_change.value++;
    }
  }

  function ml_getLists(data, page) {
    ml_data.value = data;
    ml_pagetotal.value = page.total;
    if (!!page.count && ml_page.value > page.end) {
      ml_page.value = page.end;
    }
  }

  return {
    ml_listsLoading,
    ml_searchKey,
    ml_page,
    ml_data,
    ml_pagetotal,
    ml_pagesize,
    ml_pages,
    ml_currentChange,
    ml_sizeChange,
    ml_reloadLists,
    ml_searchRow,
    ml_getLists,
    ml_change
  }
}

export function getInfo() {
  const {loading, error, data, run} = api.useRequest(api.user.userInfo);
  watch(data, (val) => {
    if (val.data && val.code < 400) {
      // if (!_t.childView) _t.childView = 'main_main';
      app.store.commit('setUser', val.data);
    }
    watch(error, (val) => {
      if (val) {
        hook.useTip().notify('error', '网络繁忙，请稍后再试！', '温馨提示');
      }
    })
  })
}
