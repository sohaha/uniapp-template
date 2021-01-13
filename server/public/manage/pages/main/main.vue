<style>
.welcome {
  line-height: 20px;
  font-size: 15px;
}

.welcome i {
  font-size: 20px;
  vertical-align: middle;
}

.welcome-tip {
  vertical-align: middle;
}

.welcome-logs {
  font-size: 13px;
  float: right;
  color: #9a9a9a;
}

.main .el-alert--warning {
  margin-bottom: 10px;
}

</style>
<template>
  <div class="main">
    <div class="view-title float-clear"></div>
    <div v-if="showError" class="panel">
      <el-alert v-for="(v,k) in error" :key="k" :title="v.tip" type="warning" :closable="false"></el-alert>
    </div>
    <fieldset>
      <aside>
        <div class="tip-area" v-if="last[0]">最近登录：{{ last[0] }} 【{{ last[1] }}】</div>
        <i class="icon-bulb"></i> <span class="welcome-tip">{{ hi }}，欢迎回来 ！</span>
      </aside>
    </fieldset>
    <el-row v-if="system" :gutter="20">
      <el-col :md="16" :span="24">
        <fieldset>
          <legend>系统信息</legend>
          <aside>
            <li>
              <span>核心版本</span> <span>{{ system.zls_version || ' -- ' }}</span>
            </li>

            <li>
              <span>主机系统</span> <span>{{ system.os || ' -- ' }}</span>
            </li>

            <li>
              <span>PHP版本</span> <span>{{ system.php_version || ' -- ' }}</span>
            </li>

            <li>
              <span>PHP环境</span> <span>{{ system.software || ' -- ' }}</span>
            </li>

            <li>
              <span>服务器名</span> <span>{{ system.hostname || ' -- ' }}</span>
            </li>

            <li>
              <span>可用空间</span> <span>{{ system.free_space || ' -- ' }}</span>
            </li>

            <li>
              <span>支持扩展</span> <span class="text">{{ system.extensions || ' -- ' }}</span>
            </li>

            <li>
              <span>禁用函数</span> <span class="text">{{ system.disable_functions || ' -- ' }}</span>
            </li>
          </aside>
        </fieldset>
      </el-col>
      <template>
        <el-col :md="8" :span="24">
          <fieldset>
            <legend>扩展信息</legend>
            <aside>
              <li v-for="(o,k) in composer" :key="k">
                <span>{{ o.name }}</span> <span>{{ o.version }}</span>
              </li>
            </aside>
          </fieldset>
        </el-col>
      </template>
    </el-row>
    <el-row v-if="JSON.stringify(goSystems) !== '{}'" :gutter="20">
      <el-col :md="24" :span="24">
        <fieldset>
          <legend>系统信息</legend>
          <aside>
            <li>
              <span>主机系统</span> <span>{{ goSystems.os.goOs || ' -- ' }}</span>
            </li>

            <li>
              <span>处理器架构</span> <span>{{ goSystems.os.arch || ' -- ' }}</span>
            </li>

            <li>
              <span>处理器逻辑</span> <span>{{ goSystems.cpu.cpuNum || ' -- ' }}</span>
            </li>

            <li>
              <span>GO版本</span> <span>{{ goSystems.os.version || ' -- ' }}</span>
            </li>

            <li>
              <span>编译工具链</span> <span>{{ goSystems.os.compiler || ' -- ' }}</span>
            </li>

            <li>
              <span>采样率</span> <span>{{ goSystems.os.memory || ' -- ' }}</span>
            </li>

            <li>
              <span>GO程数</span> <span>{{ goSystems.os.numGoroutine || ' -- ' }}</span>
            </li>
            <li>
              <span>磁盘</span> <span>
                容量 {{ goSystems.disk.total || ' -- ' }}&nbsp;&nbsp;
                空闲 <b :style="parseInt(goSystems.disk.free) < 10 ? 'color: red;':''">{{
                goSystems.disk.free || ' -- '
              }}</b>
              </span>
            </li>
            <li>
              <span>内存</span> <span>
                容量 {{ goSystems.memory.total || ' -- ' }}&nbsp;&nbsp;
                使用中 {{ goSystems.memory.used || ' -- ' }}&nbsp;&nbsp;
                可用 {{ goSystems.memory.free || ' -- ' }}&nbsp;&nbsp;
                使用率 <b :style="goSystems.memory.usage > 90 ? 'color: red;':''">{{
                goSystems.memory.usage + '%' || ' -- '
              }}</b>
              </span>
            </li>
          </aside>
        </fieldset>
      </el-col>
    </el-row>
  </div>
</template>
<script>
const { useRouter, useStore, useTip } = hook;
const { ref, reactive, computed, onMounted, watch } = vue;

export default {
  setup (prop, ctx) {
    let title = ref('后台中心');
    let error = ref([]);

    const hi = computed(() => {
      let now = new Date(),
          hour = now.getHours(),
          text;
      switch (true) {
        case hour < 6:
          text = '凌晨好';
          break;
        case hour < 9:
          text = '早上好';
          break;
        case hour < 12:
          text = '上午好';
          break;
        case hour < 14:
          text = '中午好';
          break;
        case hour < 17:
          text = '下午好';
          break;
        case hour < 19:
          text = '傍晚好';
          break;
        case hour < 22:
          text = '晚上好';
          break;
        default:
          text = '深夜好';
      }
      return text + '，' + nickname.value;

    });

    const last = computed(() => {
      let last = Object.assign({}, useStore(ctx).state.user.last || {});
      return [last.create_time, last.ip];
    });

    const systemInfo = computed(() => {
      let system = Object.assign({}, useStore(ctx).state.user.system || {});
      let composer = system['composer'];
      delete system['composer'];
      return [system, composer];
    });

    const system = computed(() => {
      let system = (systemInfo.value)[0];
      return (JSON.stringify(system) === '{}') ? null : system;
    });

    const goSystems = computed(() => {
      return Object.assign({}, useStore(ctx).state.user.systems);
    });

    const composer = computed(() => {
      return (systemInfo.value)[1];
    });

    const showError = computed(() => {
      return error.value.length > 0;
    });

    const nickname = computed(() => {
      return useStore(ctx).getters.nickname;
    });

    if (app.hasPermission('systems')) {
      console.log('拥有标识码: systems');
    }

    return {
      title,
      error,
      showError,
      last,
      hi,
      system,
      composer,
      goSystems
    };
  }
};
</script>

<style scoped></style>
