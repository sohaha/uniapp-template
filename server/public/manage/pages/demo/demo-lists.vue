<template>
	<div>
		<div class="view-title float-clear">
			<div class="view-title-right">
				<el-form
					@submit.prevent.stop.native
					inline
					class="tip-top"
				>
					<el-form-item>
						<el-button
							type="info"
							size="mini"
							icon="el-icon-refresh"
							@click="listChange"
						>刷新</el-button>
					</el-form-item>
				</el-form>
			</div>
		</div>
		<div class="tip-area">温馨提示: 这个一个示例页面</div>
		<fieldset>
			<legend>{{ title }}</legend>
			<aside v-loading="listLoading">
				<el-table
					:data="listData"
					style="width: 100%"
					size="mini"
					v-loading="listLoading"
				>
					<el-table-column
						prop="title"
						label="标题"
						width="170"
					></el-table-column>
					<el-table-column
						prop="date"
						label="日期"
					></el-table-column>
					<el-table-column
						label="操作"
						width="200"
					>
						<template slot-scope="scope">{{ scope.row.__ || '--' }}</template>
					</el-table-column>
				</el-table>
				<div
					class="tip-page"
					v-if="!!listPages.total"
				>
					<el-pagination
						:total="listPages.total"
						:page-size.sync="listPages.pagesize"
						:current-page.sync="listPages.curpage"
						@current-change="listChange"
						@size-change="listChange"
						background
						layout="prev, pager, next, sizes, total"
					></el-pagination>
				</div>
			</aside>
		</fieldset>
	</div>
</template>
<script>
const { ref, reactive, computed, onMounted, watch } = vue;
const { useTip } = hook;
const { useRequestPage } = api;
const { useInitTitle } = util;
export default {
	components: {},
	setup(prop, ctx) {
		const { title } = useInitTitle(ctx);

		// 请求列表
		const lists = useRequestPage(
			() =>
				// todo 因为没有真实请求，生成示例数据
				new Promise((r, j) => {
					const apiRes = {
						code: 200,
						data: {
							items: [],
							page: { total: 100, curpage: lists.pages.value.curpage },
						},
					};
					for (let i = 0; i < lists.pages.value.pagesize; i++) {
						apiRes.data.items.push({
							title: lists.pages.value.curpage + '-demo' + i,
							date: '2020-01-01',
						});
					}
					if (lists.pages.value.curpage % 2 === 0) {
						apiRes.code = 211;
						apiRes.msg = '触发错误，不能访问双数页码';
					}
					r(apiRes);
				}), // 请求接口
			{ page: 1, pagesize: 10 }, // 请求参数
			{
				dataHandle(e) {
					e.items = e.items.map((e) => {
						// 这里可以对数据做进一步处理
						return e;
					});
					return e;
				},
			}
		);

		let lastPage = 0; // 上次页码
		watch(lists.loading, (l) => {
			console.log('列表请求状态', l);
			if (!l) {
				if (lists.error.value) {
					useTip().message('error', lists.error.value);
					lists.error.value = null; // 获取到错误信息后需要重置掉
					lists.pages.value.curpage = lastPage; // 请求失败后页码改回之前的
					return;
				}
				lastPage = lists.pages.value.curpage;
			}
		});
		const listRes = {
			listData: lists.items,
			listLoading: lists.loading,
			listPages: lists.pages,
			listChange: lists.change,
		};
		// 请求列表结束

		return {
			title,
			...listRes,
		};
	},
};
</script>

<style scoped>
</style>
