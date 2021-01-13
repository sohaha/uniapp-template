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
							type="primary"
							size="mini"
							@click="useAddRowStatus"
							icon="el-icon-plus"
							:disabled="isEditRow"
						>添加
						</el-button>
						<el-button
							type="info"
							size="mini"
							@click="listChange"
							icon="el-icon-refresh"
						>刷新</el-button>
					</el-form-item>
				</el-form>
			</div>
		</div>
		<fieldset>
			<legend>{{ title }}</legend>
			<aside v-loading="listLoading">
				<el-table
					@selection-change="useHandleSelectionChange"
					:data="listData"
					style="width: 100%"
					size="mini"
				>
					<el-table-column
						:selectable="useSelectable"
						type="selection"
						width="55"
					></el-table-column>
					<el-table-column label="标题">
						<template slot-scope="scope">
							<div v-if="scope.row._isEdit">
								<el-input
									v-model="scope.row.title"
									placeholder
									size="mini"
								></el-input>
							</div>
							<div
								v-else
								class="text-nowrap"
								:title="scope.row.title"
							>{{ scope.row.title }}</div>
						</template>
					</el-table-column>
					<el-table-column
						prop="date"
						label="日期"
					></el-table-column>
					<el-table-column
						label="操作"
						width="200"
					>
						<template slot-scope="scope">
							<div class="btns-operating">
								<el-button
									:loading="scope.row._loading"
									v-bind="useGetEditBtnAttrs(scope)"
									size="mini"
									@click="useEditRow(scope)"
								>{{ useGetEditBtnAttrs(scope).title }}
								</el-button>
								<el-button
									v-if="scope.row._isEdit"
									title="放 弃"
									@click="useQuitRow(scope)"
									size="mini"
									:loading="scope.row._loading"
									icon="el-icon-close"
								>放 弃
								</el-button>
								<template>
									<el-popover
										placement="top"
										width="160"
										v-model="scope.row._isPopover"
									>
										<p>确定删除吗？</p>
										<div>
											<el-button
												:disabled="isEditRow"
												size="mini"
												@click="scope.row._isPopover = false"
												type="info"
												plain
											>取 消</el-button>
											<el-button
												:disabled="isEditRow"
												type="danger"
												size="mini"
												@click="useDeleteRow(scope)"
												plain
											>确 定</el-button>
										</div>
										<el-button
											:disabled="isEditRow"
											v-show="!scope.row._isEdit"
											slot="reference"
											size="mini"
											type="danger"
											icon="el-icon-delete"
											title="删 除"
										>删 除
										</el-button>
									</el-popover>
								</template>
							</div>
						</template>
					</el-table-column>
				</el-table>
				<div
					class="tip-page"
					v-if="!!listPages.total"
				>
					<div
						class="panel-left"
						v-show="showColumnBtn"
					>
						<el-button
							@click="useDeleteSelection"
							size="mini"
							type="danger"
							icon="el-icon-delete"
							title="删除选中"
						></el-button>
					</div>

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
const { useRouter, useStore, useCache, useTip, useLoading } = hook;
const { user: userApi, useRequest, useRequestPage, useRequestWith } = api;
const { useInitTitle } = util;

let dataFormat = { title: '', date: '', id: 0 };

export default {
	components: {},
	setup(prop, ctx) {
		const { root } = ctx;
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
							id: '' + lists.pages.value.curpage + i,
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
						e._isEdit = false;
						e._isPopover = false;
						e._loading = false;
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
			} else {
				isEditRow.value = false;
			}
		});
		const listRes = {
			listData: lists.items,
			listLoading: lists.loading,
			listPages: lists.pages,
			listChange: lists.change,
		};
		// 请求列表结束

		// 编辑列表
		const addRow = useRequestWith(
			(e) => {
				console.log('需要添加的数据', e);
				// todo 因为没有真实请求，生成示例数据
				return new Promise((r, j) => {
					const apiRes = {
						code: 200,
						msg: '添加成功',
						data: {
							id: 99,
						},
					};
					setTimeout(function () {
						r(apiRes);
					}, 1000);
				});
			},
			{ manual: true }
		);
		const editRow = useRequestWith(
			(e) => {
				console.log('需要编辑的数据', e);
				// todo 因为没有真实请求，生成示例数据
				return new Promise((r, j) => {
					const apiRes = {
						code: 211,
						msg: '编辑失败',
					};
					setTimeout(function () {
						r(apiRes);
					}, 1000);
				});
			},
			{ manual: true }
		);
		const deleteRow = useRequestWith(
			(e) => {
				console.log('需要删除的数据', e);
				// todo 因为没有真实请求，生成示例数据
				return new Promise((r, j) => {
					const apiRes = {
						code: 200,
						msg: '删除成功',
					};
					r(apiRes);
				});
			},
			{ manual: true }
		);
		const isEditRow = ref(false);
		let editTmpData = {};
		const editRes = {
			isEditRow,
			useAddRowStatus() {
				isEditRow.value = true;
				lists.items.value.unshift(
					Object.assign(
						{ _isEdit: true, _isPopover: false, _isAdd: true, _loading: false },
						dataFormat
					)
				);
			},
			async useEditRow(e) {
				if (e.row._isEdit || e.row._isAdd) {
					e.row._loading = true;
					const [, err] = await (e.row._isAdd ? addRow : editRow).run(e.row);
					if (err) {
						useTip().message('error', err);
						e.row._loading = false;
						return;
					}
					if (e.row._isAdd) {
						lists.pages.value.total++;
					}
					e.row._loading = e.row._isEdit = e.row._isAdd = false;
					isEditRow.value = false;
					// 编辑完成是否重新获取列表
					// lists.change();
				} else {
					editTmpData = Object.assign({}, e.row);
					e.row._isEdit = true;
					isEditRow.value = true;
				}
			},
			useQuitRow(e) {
				let index = e.$index;
				if (!e.row._isAdd) {
					lists.items.value.splice(e.$index, 1, Object.assign({}, editTmpData));
				} else {
					lists.items.value.splice(e.$index, 1);
				}
				isEditRow.value = false;
			},
			async useDeleteRow(e) {
				isEditRow.value = true;
				const [, err] = await deleteRow.run(e.row);
				if (err) {
					useTip().message('error', err);
					return;
				}
				isEditRow.value = false;
				lists.pages.value.total--;
				lists.items.value.splice(e.$index, 1);
				if (lists.items.value.length === 0) {
					lists.change();
				}
			},
			useGetEditBtnAttrs(e) {
				return e.row._isEdit
					? {
							title: '提 交',
							type: 'primary',
							icon: 'el-icon-check',
					  }
					: {
							title: '编 辑',
							type: 'info',
							disabled: isEditRow.value,
							icon: 'el-icon-edit',
					  };
			},
		};
		// 编辑列表结束

		// 选中删除
		const selectIds = ref([]);
		const selectRes = {
			selectIds,
			showColumnBtn: computed(() => {
				return selectIds.value.length > 0;
			}),
			useHandleSelectionChange(e) {
				selectIds.value = e.map((e) => {
					return e.id;
				});
			},
			useSelectable(row, index) {
				if (index === 2) return false;
				return true;
			},
			useDeleteSelection() {
				console.log('删除选择', selectIds.value);
			},
		};
		// 选中删除结束

		return {
			title,
			...listRes,
			...editRes,
			...selectRes,
		};
	},
};
</script>

<style scoped>
</style>
