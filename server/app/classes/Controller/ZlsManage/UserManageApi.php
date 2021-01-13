<?php

declare(strict_types=1);

namespace Controller\ZlsManage;

use Business\ZlsManage\GroupBusiness;
use Business\ZlsManage\RulesBusiness;
use Controller\ZlsManage;
use Z;

/**
 * 后台-用户管理接口
 * @desc          需要传递 header token（GetToken除外）
 */
class UserManageApi extends ZlsManage
{
    /**
     * 获取用户列表
     * @time      2018-11-7 17:57:39
     * @api-param int page 页码  1 n
     * @api-param int pagesize 条数  10
     * @api-param string key 用户名/Email  '' n 为空表示全部用户
     * @return array
     */
    public function GETzUserLists(): array
    {
        $page     = z::get('page', 1);
        $pagesize = z::get('pagesize', 10);
        $key      = z::get('key');
        $where    = [];
        if ($key) {
            $where['username like'] = "%{$key}%";
        }
        // 没有管理员权限，只能查看自己
        if (!$this->hasPermission("systems")) {
            $where['id'] = $this->getInfo('id');
        }

        return [200, '用户列表', $this->UserBusiness->lists($page, $pagesize, $where)];
    }

    /**
     * 创建新用户
     * @time      2018-11-7 17:57:39
     * @api-permission("systems")
     */
    public function POSTzUser()
    {
        $post = z::postText();
        $rs   = $this->UserBusiness->create($post);

        return is_string($rs) ? $rs : [200, '处理成功', ['id' => $rs]];
    }

    /**
     * 删除用户
     * @desc       该操作只是软删除用户
     * @time       2018-11-7 17:57:39
     * @api-permission("systems")
     * @api-delete int id 用户id  '' y
     * @return array|string
     */
    public function DELETEzUser()
    {
        $id = (int) z::postText('id');
        switch (true) {
            case ($id === z::arrayGet($this->USER, 'id')):
                $result = '不可以删除自己';
                break;
            case ($this->UserBusiness->isSuperAdminById($id)):
                $result = '请移除该用户的超级管理员身份';
                break;
            default:
                $result = $this->UserBusiness->delete($id);
        }

        return is_string($result) ? $result : z::tap([200, '删除用户', $result], function () use ($id) {
            $this->UserBusiness->clearAllToken($id);
        });
    }

    /**
     * 获取角色列表
     * @time      2018-11-7 17:57:39
     * @api-permission("systems")
     * @return array
     */
    public function GETzGroups()
    {
        return [200, '角色列表', (new GroupBusiness)->all()];
    }

    /**
     * 获取角色详情
     * @time      2018-11-7 17:57:39
     * @api-permission("systems")
     * @api-get   int id 角色ID  Y
     * @return array|string
     */
    public function zGroupInfo()
    {
        $id = z::getPost('id');
        if (!$id) {
            return '参数错误';
        }
        $GroupBusiness = new GroupBusiness;
        $info          = $GroupBusiness->find($id);

        return !!$info ? [200, '角色详情', $GroupBusiness->integration($info)] : '角色不存在';
    }

    /**
     * 创建角色
     * @time     2019-4-19 11:14:19
     * @api-permission("systems")
     * @api-post string name 角色名称  Y null
     * @api-post string remark 角色备注  Y ''
     * @return array|string
     */
    public function POSTzGroups()
    {
        $name   = z::post('name');
        $remark = z::post('remark', '');
        $res    = (new GroupBusiness)->save(null, $name, $remark);

        return is_string($res) ? $res : [200, '创建新角色', $res];
    }

    /**
     * 删除角色
     * @return string
     * @todo       未完成
     * @time       2019-09-04 16:10:49
     * @api-permission("systems")
     * @api-delete int id 角色id y null
     */
    public function DELETEzGroups()
    {
        return '暂不支持';
    }

    /**
     * 更新角色
     * @time     2019-4-19 11:14:19
     * @api-permission("systems")
     * @api-put  string name 角色名称  Y null
     * @api-put  string remark 角色备注  Y ''
     * @return array|string
     */
    public function PUTzGroups()
    {
        $name   = z::postText('name');
        $remark = z::postText('remark', '');
        $id     = z::postText('id', 0);
        $res    = (new GroupBusiness)->save($id, $name, $remark);

        return is_string($res) ? $res : [200, '更新角色', $res];
    }

    /**
     * 获取权限规则列表
     * @time 2019-09-04 16:10:49
     * @api-permission("systems")
     * @return array
     */
    public function GETzRules()
    {
        $key   = z::get('key', '');
        $where = function (\Zls_Database_ActiveRecord $db) use ($key) {
            if ($key) {
                $db->where(['title like' => "%{$key}%"]);
                $db->where(['mark like' => "%{$key}%"], 'or');
            }
        };

        return [200, '权限规则列表', (new RulesBusiness)->lists($where)];
    }

    /**
     * 添加权限规则
     * @time 2019-09-04 16:10:49
     * @api-permission("systems")
     * @return array|string
     */
    public function POSTzRules()
    {
        $title  = z::postText('title', '');
        $mark   = z::postText('mark', '');
        $type   = z::postText('type', '');
        $remark = z::postText('remark', '');
        $res    = (new RulesBusiness)->addRules($title, $mark, $type, $remark);

        return is_string($res) ? $res : [200, '权限规则列表', $res];
    }

    /**
     * 编辑权限规则
     * @time 2019-09-04 16:10:49
     * @api-permission("systems")
     * @return array|string
     */
    public function PUTzRules()
    {
        $title  = z::postText('title', '');
        $mark   = z::postText('mark', '');
        $id     = z::postText('id', '');
        $remark = z::postText('remark', '');
        $res    = (new RulesBusiness)->editRules($id, $title, $mark, $remark);

        return is_string($res) ? $res : [200, '权限规则列表', $res];
    }

    /**
     * 删除权限规则
     * @time 2019-09-04 16:10:49
     * @api-permission("systems")
     * @return array|string
     */
    public function DELETEzRules()
    {
        $id  = z::postText('id', '');
        $res = (new RulesBusiness)->deteleRules($id);

        return is_string($res) ? $res : [200, '删除权限规则', $res];
    }

    /**
     * 更新用户规则权限
     * @time 2019-09-04 16:10:49
     * @api-permission("systems")
     * @return array|string
     */
    public function PUTzUpdateUserRuleStatus()
    {
        $id     = z::postText('id');
        $gid    = z::postText('gid');
        $status = z::postText('status');
        $sort   = z::postText('sort');
        $res    = (new RulesBusiness)->updateUserRuleStatus((int) $gid, (int) $id, (int) $status, (int) $sort);

        return is_string($res) ? $res : [200, '权限规则列表', $res];
    }
}
