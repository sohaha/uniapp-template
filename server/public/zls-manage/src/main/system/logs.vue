<style>
.logs-view .panel {
  padding-top : 10px;
}

.logs-view .switch {
  margin-bottom : 10px;
  float : right;
}

.logs-view .el-textarea__inner {
  padding : 10px;
}

</style>
<template>
  <div class="logs-view">
    <div class="view-title float-clear">
      <!-- <span v-once>{{title}}</span> -->
      <div class="view-title-right">
        <el-form @submit.prevent.stop.native inline class="tip-top">
          <el-form-item>
            <el-select size="mini" v-model="currentType" placeholder="请选择类型">
              <el-option v-for="item in types" :key="item" :label="item" :value="item"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-select :disabled="ban" size="mini" v-model="current" placeholder="请选择日志文件">
              <el-option v-for="item in lists" :key="item" :label="item" :value="item"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label>
            <div class="btns-operating">
              <el-button type="info" size="mini" @click="reloadDate" icon="el-icon-refresh">刷 新</el-button>
              <el-popover placement="top" width="160" v-model="stateTip">
                <p>
                  确定删除吗？
                  <br>（操作无法逆转）
                </p>
                <div>
                  <el-button size="mini" @click="stateTip = false" type="info" plain>取 消</el-button>
                  <el-button @click="deleteLog" type="danger" size="mini" plain>确 定</el-button>
                </div>
                <el-button
                  :disabled="stateDel"
                  slot="reference"
                  size="mini"
                  type="danger"
                  icon="el-icon-delete"
                  title="删 除"
                >删 除</el-button>
              </el-popover>
            </div>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <fieldset>
      <legend v-show="logName">{{logName}}</legend>
      <aside>
        <div class="switch">
          <el-switch v-model="autoLoad" active-text="自动刷新" />
        </div>
        <el-input type="textarea" ref="textarea" :placeholder="current?'':'请先选择日志类型与日志文件'" readonly :autosize="{ minRows: 20, maxRows: 30}" v-model="content" />
      </aside>
    </fieldset>
  </div>
</template>
<script>
var $this, timer;
Spa.define(
  {
    mixins: [mixinLists, initTitle],
    data: function() {
      return {
        lists: [],
        types: [],
        currentType: null,
        current: null,
        content: "",
        stateTip: false,
        ban: true,
        autoLoad: true,
        currentLine: 0
      };
    },
    beforeCreate: function() {
    },
    beforeDestroy: function() {
      clearTimeout(timer);
    },
    watch: {
      current: function(v) {
        if (v) {
          $this.current = v;
          $this.currentLine = 0;
          $this.getDate();
        }
      },
      currentType: function(v, o) {
        if (v !== o) {
          $this.current = "";
          $this.content = "";
          $this.lists = [];
          this.stateTip = false;
          $this.getDate();
        }
        if ($this.ban && v) {
          $this.ban = false;
        }
      },
      autoLoad: function() {
        $this.timeout();
      }
    },
    mounted: function() {
      $this.getDate();
    },
    computed: {
      logName: function() {
        return $this.currentType ? $this.currentType + " - 日志查看" : "日志查看";
      },
      stateDel: function() {
        return !$this.current;
      }
    },
    init: function(query, search) {},
    methods: {
      timeout: function() {
        clearTimeout(timer);
        if ($this.autoLoad && $this.current) {
          timer = setTimeout(function() {
            $this.reloadDate();
          }, 2000);
        }
      },
      reloadDate: function() {
        $this.getDate();
      },
      deleteLog: function() {
        $this
          .$api(apis.systemLogsDelete, {
            name: $this.current,
            type: $this.currentType
          })
          .then(function() {
            $this.current = "";
            $this.content = "";
            $this.reloadDate();
          })
          .finally(function() {
            setTimeout(function() {
              $this.stateTip = false;
            });
          });
      },
      getDate: function() {
        $this
          .$api(apis.systemLogs, {
            name: $this.current,
            type: $this.currentType,
            currentLine: $this.currentLine
          })
          .then(function(e) {
            $this.lists = e.data.lists;
            if ($this.currentLine) {
              $this.content = $this.content + e.data.content;
            } else {
              $this.content = e.data.content;
            }
            $this.types = e.data.types;
            $this.currentLine = e.data.currentLine;
            $this.timeout();
          })
          .catch(function(err) {
            $this.$warMsg(err);
          })
          .finally(function() {
            $this.$nextTick(function() {
              if ($this.$refs.textarea) {
                var textarea = $this.$refs.textarea.$el.querySelector(
                  "textarea"
                );
                textarea.scrollTop = textarea.scrollHeight;
              }
            });
          });
      }
    }
  },
  [],
  "/index"
);
</script>
