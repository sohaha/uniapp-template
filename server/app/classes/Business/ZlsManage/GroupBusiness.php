<?php

namespace Business\ZlsManage;

use Dao\ZlsManage\GroupDao;
use Dao\ZlsManage\RulesDao;
use Dao\ZlsManage\RulesRelaDao;
use Dao\ZlsManage\UserDao;
use Z;

/**
 * 角色业务
 * @author seekwe <seekwe@gmail.com>
 */
class GroupBusiness extends \Zls_Business
{
    /**
     * 角色列表
     *
     * @param array $where 查询条件
     *
     * @return array
     */
    public function all($where = []): array
    {
        $dao    = new GroupDao();
        $table  = $dao->getTable();
        $fields = $dao->getReversalColumns('rule_ids,ban_rule_ids,create_time');

        return $dao->findAll($where, ['id' => 'desc'], null, $fields);
    }

    /**
     * 查找角色
     *
     * @param int $id
     *
     * @return array
     */
    public function find($id): array
    {
        return (new GroupDao())->find((int)$id);
    }

    /**
     * 创建/更新角色
     *
     * @param int    $id     角色ID
     * @param string $name   角色名称
     * @param string $remark 角色备注
     *
     * @return array|string
     */
    public function save($id, $name, $remark = '')
    {
        $GroupDao = new GroupDao();
        $time     = date('Y-m-d H:i:s');
        $data     = ['name' => $name, 'remark' => $remark];
        if ((bool)$id) {
            $data['id'] = $id;
        }
        $retData = $errorMsg = $errorKey = null;
        if (z::checkData($data, $GroupDao->verifyRules(), $retData, $errorMsg, $errorKey)) {
            $retData['update_time'] = $time;
            if ($id) {
                $res = $GroupDao->update($retData, $id);
            } else {
                $retData       += ['create_time' => $time];
                $res           = $GroupDao->insert($retData);
                $retData['id'] = (int)$res;
            }

            return $res ? $retData : '保存失败';
        }

        return $errorMsg;
    }

    /**
     * 整合角色相关数据
     *
     * @param array $info
     *
     * @return array
     */
    public function integration(array $info): array
    {
        if (!$info) {
            return [];
        }
        // todo 做缓存处理
        $gid          = $info['id'];
        $RulesRelaDao = new RulesRelaDao();
        $lists        = $RulesRelaDao->findAll(
            ['group_id' => $gid], ['id' => 'desc'], null, 'status,rule_id'
        );
        $rows         = [];
        foreach ($lists as $row) {
            $rows[$row['status']][] = $row;
        }
        $info['rule_ids']     = z::arrayValues(
            z::arrayGet($rows, '1', []), 'rule_id'
        );
        $info['ban_rule_ids'] = z::arrayValues(
            z::arrayGet($rows, '2', []), 'rule_id'
        );
        // $info['rules_count'] = count($info['rule_ids'])+count($info['ban_rule_ids']);
        $info['user_count'] = z::arrayGet(
            (new UserDao)->find(
                ['group_id' => $info['id']], false, [], 'count(*) as count'
            ), 'count', 0
        );

        return $info;
    }

    /**
     * 获取菜单
     *
     * @param int $userid
     *
     * @return array
     */
    public function getMenu($userid = 0): array
    {
        $menu = [
            [
                'id'    => 2,
                'title' => '日志查看',
                'index' => 'system/logs',
                'icon'  => 'icon-alert-circle',
            ],
            [
                'id'         => 1,
                'title'      => '系统设置',
                'index'      => 'system',
                'breadcrumb' => false,
                'icon'       => 'icon-options-',
                'child'      => [
                    ['id' => 10, 'title' => '程序设置', 'index' => 'system/config', 'icon' => 'icon-settings',],
                    ['id' => 11, 'title' => '用户设置', 'index' => 'user/lists', 'icon' => 'icon-person',],
                    ['id' => 12, 'title' => '角色设置', 'index' => 'user/group', 'icon' => 'icon-people'],
                    ['id' => 13, 'title' => '菜单设置', 'index' => 'user/menu', 'icon' => 'icon-pricetags',],
                ],
            ],
        ];

        return $menu;
    }

    /**
     * 获取权限标识列表
     *
     * @param $groupid
     *
     * @return array
     */
    public function getMarks(array $groupid): array
    {
        $rulesDao = new RulesDao();

        return array_values(Z::arrayValues($this->getRules($groupid, $rulesDao::TYPE_MARK), 'mark'));
    }

    public function getRouter(array $groupid): array
    {
        $rulesDao = new RulesDao();

        return Z::arrayValues($this->getRules($groupid, $rulesDao::TYPE_ROUTER), ['mark', 'status', 'condition']);
    }

    /**
     * 获取角色权限信息
     *
     * @param      $groupid
     * @param null $type
     *
     * @return array
     */
    public function getRules($groupid, $type = null): array
    {
        $dao      = new RulesDao();
        $relasDao = new RulesRelaDao();
        $db       = $dao->getDb()->join([$relasDao->getTable() => 're'], 're.rule_id=r.id')->from($dao->getTable(), 'r');
        $db->where(['re.group_id' => $groupid, 're.status !=' => $relasDao::STATUS_IGNORE]);
        $db->select('*,re.status as status');
        $rules = $db->execute()->rows();
        if ($type === null) {
            return $rules;
        }
        $rules = Z::arrayFilter($rules, static function ($v) use ($type) {
            return (int)$v['type'] === (int)$type;
        });

        return $rules;
    }
}
