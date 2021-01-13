<?php
declare(strict_types=1);

namespace Controller\ZlsManage;

use Business\ZlsManage\MenuBusiness;
use Controller\ZlsManage;
use Z;

/**
 * 后台-菜单管理
 * @author        zjy
 * @desc          需要传递 header token（GetToken除外）
 * @api-permissionClass("systems")
 */
class MenuApi extends ZlsManage
{
    /**
     * 获取全部菜单
     * @api-post    int groupid 角色id 0 N
     * @api-return  JSON [{"id":1,"title":"首页","index":"main","icon":"icon-home","breadcrumb":1,"real":0,"show":0,"pid":0,"sort":1,"is_show":false},{"id":2,"title":"统计","index":"statistics","icon":"icon-pie-chart-","breadcrumb":1,"real":0,"show":0,"pid":0,"sort":2,"is_show":false,"child":[{"id":3,"title":"商城概况","index":"statistics/shopInfo","icon":"","breadcrumb":0,"real":0,"show":0,"pid":2,"sort":3,"is_show":false}]}]
     * @return array
     */
    public function POSTzUserMenu(): array
    {
        $post = z::post();
        $groupid = Z::arrayGet($post, 'groupid')?:0;

        $retVal = (new MenuBusiness())->lists($groupid);

        return is_array($retVal) ? [200, '请求成功', $retVal] : $retVal;
    }

    /**
     * 新增菜单
     * @api-post    string title 菜单名称 null
     * @api-post    string index 路由地址 null
     * @api-post    string icon 图标 null N
     * @api-post    int breadcrumb 面包屑显示 0 N 0不显示1可显示
     * @api-post    int real 面包屑可点击 0 N 0不可点击1可点击
     * @api-post    int show 导航栏显示 0 N 0不显示1显示
     * @api-post    int pid 父id  0 N 0为顶级菜单，>0为父菜单id
     * @return array|string
     */
    public function POSTzCreate()
    {
        $post = z::post();
        $business = new MenuBusiness();
        $rs   = $business->create($post);

        return is_string($rs) ? $rs : [200, '处理成功', ['id' => $rs]];
    }


    /**
     * 删除菜单
     * @api-post    int id 菜单id  0 Y 菜单含有子集，需要先删除子集
     * @return array|string
     */
    public function POSTzDelete()
    {
        $id = z::post('id', 0);
        if ($id == 0) {
            return '菜单id不允许为空';
        }
        $business = new MenuBusiness();
        $retVal = $business->Delete($id);

        return is_string($retVal) ? $retVal : [200, '处理成功', []];
    }

    /**
     * 更新菜单
     * @api-post    int id 菜单id
     * @api-post    string title 菜单名称 null
     * @api-post    string index 路由地址 null
     * @api-post    string icon 图标 null N
     * @api-post    int breadcrumb 面包屑显示 0 N 0不显示1可显示
     * @api-post    int real 面包屑可点击 0 N 0不可点击1可点击
     * @api-post    int show 导航栏显示 0 N 0不显示1显示
     * @return array|string
     */
    public function POSTzUpdate()
    {
        $business = new MenuBusiness();
        $retVal = $business->update(z::post());

        return is_string($retVal) ?  $retVal : [200, '请求成功', $retVal];
    }

    /**
     * 菜单拖拽排序(支持多次拖拽一起排)
     * @api-post    string menu json格式的菜单 null 菜单拖拽排序
     * @api-return  JSON [{"id":"2"},{"id":"3","child":[{"id":"4"},{"id":"5"}]}]
     * @return array|string
     */
    public function POSTzSort()
    {
        $business = new MenuBusiness();
        $post = z::post('menu', '');
        $json = @json_decode($post,true);

        $retVal = $business->sort($json);

        return [200, '请求成功', $retVal];
    }


    /**
     * 角色菜单更新
     * @api-post    int groupid 角色id
     * @api-post    string menu 菜单id null Y 多个菜单id格式1,2,3,4,5
     * @return array|string
     */
    public function POSTzUpdateGroupMenu()
    {
        $business = new MenuBusiness();
        $retVal = $business->updateGroupMenu(z::post());

        return is_string($retVal) ?  $retVal : [200, '请求成功', $retVal];
    }


}
