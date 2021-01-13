<template>
  <div>
    <div class="view-title float-clear">
      <!--占位-->
    </div>
    <fieldset>
      <legend @click="useDemoClick">{{ title }}</legend>
      <aside class="center" :aria-label="title">
        <ve-pie async :data="chartDatPaie" :init-options='chartDatPaieInitOptions'></ve-pie>
        <ve-histogram :data="chartDataHistogram" :init-options='chartDatPaieInitOptions'></ve-histogram>
      </aside>
    </fieldset>
    <div class="panel">
      更多图表请参考 <a href="https://v-charts.js.org" target="_blank">v-charts 文档</a>。
    </div>
  </div>
</template>
<script>
export default load({
  js: [['//cdn.jsdelivr.net/npm/zls-manage/echarts/echarts.js', '//cdn.jsdelivr.net/npm/zls-manage/echarts/v-charts.js']],
  css: ['//cdn.jsdelivr.net/npm/zls-manage/echarts/v-charts.css']
}).then(async () => {
  const {ref, reactive, computed, onMounted, watch, onBeforeUnmount} = vue;
  const {useRouter, useStore, useCache, useTip, useLoading, useConfirm} = hook;
  const {user: userApi, useRequest} = api;
  const {useInitTitle} = util;

  return {
    components: {
      demo: VueRun('components/demo.vue')
    },
    setup(prop, ctx) {
      const {title} = useInitTitle(ctx);

      let chartDataHistogram = ref({
        columns: ['日期', '访问用户', '下单用户', '下单率'],
        rows: [
          {'日期': '1/1', '访问用户': 1393, '下单用户': 1093, '下单率': 0.32},
          {'日期': '1/2', '访问用户': 3530, '下单用户': 3230, '下单率': 0.26},
          {'日期': '1/3', '访问用户': 2923, '下单用户': 2623, '下单率': 0.76},
          {'日期': '1/4', '访问用户': 1723, '下单用户': 1423, '下单率': 111.49},
          {'日期': '1/5', '访问用户': 3792, '下单用户': 3492, '下单率': 0.323},
          {'日期': '1/6', '访问用户': 4593, '下单用户': 4293, '下单率': 0.78}
        ]
      });

      let chartDatPaieInitOptions = ref({
        renderer: 'svg'
      });

      let chartDatPaie = ref({
        columns: ['日期', '访问用户'],
        rows: [
          {'日期': '1/1', '访问用户': 1393},
          {'日期': '1/2', '访问用户': 3530},
          {'日期': '1/3', '访问用户': 2923},
          {'日期': '1/4', '访问用户': 1723},
          {'日期': '1/5', '访问用户': 3792},
          {'日期': '1/6', '访问用户': 4593}
        ]
      });

      function useDemoClick() {
        title.value = "示例" + +new Date();
      }

      return {
        title,
        useDemoClick,
        chartDataHistogram,
        chartDatPaieInitOptions,
        chartDatPaie
      };
    }
  };
});
</script>

<style scoped>
</style>
