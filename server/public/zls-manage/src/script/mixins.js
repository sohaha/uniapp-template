/**
 * Created by 影浅-seekwe@gmail.com on 2018-11-16
 */
var initTitle = {
  data: function () {
    return {
      title: '',
      SpaTitle: ''
    };
  },
  watch: {
    title: function (v, o) {
      var breadcrumb = this.$store.state.breadcrumb;
      var current = breadcrumb[breadcrumb.length - 1];
      if (
        current &&
        current.title &&
        window['app'].router.page.indexOf(current.index) === 5
      ) {
        current.title = v;
        this.$store.commit('setBreadcrumb', breadcrumb);
      }
      this.SpaTitle = v + ' - %s';
      this.$SpaSetTitle();
    },
    '$store.state.viewTitle': function (v, o) {
      this.title = this.$store.state.viewTitle;
    }
  },
  mounted: function () {
    if (!this.title) {
      this.title = this.$store.state.viewTitle;
    }
    if (!this.SpaTitle && this.title) {
      this.SpaTitle = this.title + ' - %s';
    }
  }
};

var mixinLists = {
  data: function () {
    return {
      ml_listsLoading: false,
      ml_searchKey: '',
      ml_page: 1,
      ml_data: [],
      ml_pagetotal: 0,
      ml_pagesize: 10,
      ml_pages: {},
    };
  },
  watch: {
    ml_page: {
      handler: function (val, oldVal) {
        this.$nextTick(this.getLists);
      },
      immediate: true
    }
  },
  methods: {
    ml_currentChange: function (e) {
      this.ml_page = e;
    },
    ml_sizeChange: function (e) {
      this.ml_searchRow();
    },
    ml_reloadLists: function () {
      this.getLists();
    },
    ml_searchRow: function () {
      if (this.ml_page !== 1) {
        this.ml_page = 1;
      } else {
        this.getLists();
      }
    },
    // 设置数据
    ml_getLists: function (data, page) {
      this.ml_data = data;
      this.ml_pagetotal = page.total;
      if (!!page.count && this.ml_page > page.end) {
        this.ml_page = page.end;
      }
    },
    getLists: function () {}
  }
};
