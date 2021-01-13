<template>
  <div>
    <div class="view-title float-clear"></div>
    <fieldset>
      <legend>{{ title }}</legend>
      <aside>
        <div class="text">
          <ul class="icon_lists dib-box">
            <li class="dib" v-for="(v,k) in icons" :key="k">
              <span class="icon font_family" :class="['icon-'+v.font_class]"></span>
              <div class="name">{{ v.name }}</div>
              <div class="code-name">icon-{{ v.font_class }}</div>
            </li>
          </ul>
        </div>
      </aside>
    </fieldset>
  </div>
</template>
<script>
const {ref, reactive, computed, onMounted, watch, onBeforeUnmount} = vue;
const {useRouter, useStore, useCache, useTip, useLoading, useConfirm} = hook;
const {user: userApi, useRequest} = api;
const {useInitTitle} = util;

export default {
  components: {},
  setup(prop, ctx) {
    const {title} = useInitTitle(ctx);
    onMounted(() => {
      getIcons();
    })

    let icons = ref([]);

    function getIcons() {
      VueRun.httpRequest('./pages/demo/iconfont.json').then(function (e) {
        if (typeof e === 'string') {
          e = JSON.parse(e)
        }
        icons.value = e.glyphs;
      });
    }

    return {
      title,
      icons
    };
  }
};
</script>

<style scoped>
ul {
  padding: 0;
  margin: 0;
}

.icon_lists {
  width: 100% !important;
  overflow: hidden;
  *zoom: 1;
}

.name {
  display: none;
}

.icon_lists li {
  width: 100px;
  margin: 0 10px 10px;
  text-align: center;
  list-style: none !important;
  cursor: default;
}

.icon_lists li .code-name {
  line-height: 1.2;
}

.icon_lists .icon {
  display: block;
  height: 100px;
  line-height: 100px;
  font-size: 42px;
  margin: 10px auto;
  color: #333;
  -webkit-transition: font-size 0.25s linear, width 0.25s linear;
  -moz-transition: font-size 0.25s linear, width 0.25s linear;
  transition: font-size 0.25s linear, width 0.25s linear;
}

.icon_lists .icon:hover {
  font-size: 100px;
}

.icon_lists .svg-icon {
  width: 1em;
  vertical-align: -0.15em;
  fill: currentColor;
  overflow: hidden;
}

.icon_lists li .name,
.icon_lists li .code-name {
  color: #666;
}

ol,
ul {
  list-style-type: none;
  list-style-image: none;
}

a {
  text-decoration: none;
  background-color: transparent;
}

a:active,
a:hover {
  outline-width: 0;
}

a:focus {
  outline: 1px dotted;
}

ul.icon_lists.dib-box {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -ms-flex-wrap: wrap;
  flex-wrap: wrap;
}

li.dib {
  -webkit-box-flex: 1;
  -ms-flex: 1;
  flex: 1;
  min-width: 100px;
}

.dib-box {
  font-size: 0;
  *word-spacing: -1px;
}

@media (-webkit-min-device-pixel-ratio: 0) {
  .dib-box {
    letter-spacing: -5px;
  }
}

.dib-box .dib {
  vertical-align: top;
  font-size: 12px;
  letter-spacing: normal;
  word-spacing: normal;
  line-height: inherit;
}
</style>
