<?php

namespace Business\ZlsManage;

use Dao\ZlsManage\GroupDao;
use Dao\ZlsManage\GroupMenuDao;
use Dao\ZlsManage\MenuDao;
use Dao\ZlsManage\RulesDao;
use Dao\ZlsManage\RulesRelaDao;
use Dao\ZlsManage\UserDao;
use Z;

/**
 * 角色业务
 *
 * @author seekwe <seekwe@gmail.com>
 */
class GroupBusiness extends \Zls_Business
{
    /**
     * 角色列表
     *
     * @param array  $where  查询条件
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
     * @param int  $id
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
     * @param int     $id      角色ID
     * @param string  $name    角色名称
     * @param string  $remark  角色备注
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
     * @param array  $info
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
            ['group_id' => $gid],
            ['id' => 'desc'],
            null,
            'status,rule_id'
        );
        $rows         = [];
        foreach ($lists as $row) {
            $rows[$row['status']][] = $row;
        }
        $info['rule_ids']     = z::arrayValues(
            z::arrayGet($rows, '1', []),
            'rule_id'
        );
        $info['ban_rule_ids'] = z::arrayValues(
            z::arrayGet($rows, '2', []),
            'rule_id'
        );
        // $info['rules_count'] = count($info['rule_ids'])+count($info['ban_rule_ids']);
        $info['user_count'] = z::arrayGet(
            (new UserDao)->find(
                ['group_id' => $info['id']],
                false,
                [],
                'count(*) as count'
            ),
            'count',
            0
        );

        return $info;
    }

    /**
     * 获取菜单
     *
     * @param int  $userid
     *
     * @return array
     */
    public function getMenu($groupid = 0, $isSuper = false): array
    {
        $dao    = new MenuDao();
        $fields = $dao->getReversalColumns(null, true);

        $dao->getDb()
            ->select($fields)
            ->from($dao->getTable())
            ->orderBy('pid', 'asc')
            ->orderBy('sort', 'asc');
        $result = $dao->getDb()->execute()->rows();
        $result = array_combine(array_column($result, 'id'), $result);

        if (!$isSuper) {
            $groupMenuDao = new GroupMenuDao();
            $fields       = $groupMenuDao->getReversalColumns(["id", "create_time", "update_time"], true);

            $where["groupid"] = $groupid;
            $groupMenuDao->getDb()
                ->select($fields)
                ->from($groupMenuDao->getTable())
                ->where($where);
            $re = $dao->getDb()->execute()->row();
        }

        $menuArr = [];
        if (isset($re)) {
            $menuArr = explode(",", z::arrayGet($re, "menu", []));
        }

        $pMenu = [];
        $data  = [];
        //result是排序查询出来的，所以pid=0的必然在前面
        foreach ($result as $key => $value) {
            if (!$isSuper && !in_array($value["id"], $menuArr)) {
                continue;
            }

            if ($value['pid'] == 0) {
                $pMenu[] = $value["id"];
                $data[]  = $value;
            } else {
                //菜单1下有菜单2和菜单3，目前菜单1的主键是可能不保存到数据库的
                //循环出来的菜单是必然要显示的，所以需要找到他的上级菜单

                //找到上级菜单加入到data中
                if (!in_array($value["pid"], $pMenu)) {
                    $pMenu[]                           = $value["pid"];
                    $data[]                            = $result[$value["pid"]];
                    $data[count($data) - 1]['child'][] = $value;
                } else {
                    foreach ($data as $k => $v) {
                        if ($v['id'] == $value['pid']) {
                            $data[$k]['child'][] = $value;
                        }
                    }
                }
            }
        }
        foreach ($data as $key => $value) {
            $sort[$key] = $value["sort"];
        }
        array_multisort($sort, SORT_ASC, $data);
        return $data;
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
     * @param       $groupid
     * @param null  $type
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

    public function getMenus($user)
    {
        $MenuDao = new MenuDao();
        $menus   = $MenuDao->find([], true, ['sort' => 'asc'], 'id, title, index, icon, breadcrumb, real, show, pid, sort');

        $vueUrl = function ($show, $url) {
            if (!$show) {
                return '';
            }
            if ($url === 'main') {
                return 'pages/main/' . $url . '.vue';
            }

            if ($url[0] === '/') {
                return 'pages' . $url . '.vue';
            }

            return 'pages/' . $url . '.vue';
        };

        $vuePath = function ($path) {
            if ($path[0] === '/') {
                if (substr($path, 0, strlen('/main')) !== '/main') {
                    return '/main' . $path;
                }
            } else {
                if ($path === 'main') {
                    return '/' . $path . '/main';
                } elseif (substr($path, 0, strlen('/main/')) !== '/main/') {
                    return '/main/' . $path;
                }
            }

            return $path;
        };

        $conv = function ($menu, $groupMenusIds) use ($vuePath, $vueUrl, $user) {
            $show = $user['isSuper'] === 1 ? true : in_array($menu['id'], array_merge([1], $groupMenusIds));
            $has  = $user['isSuper'] === 1 ? true : in_array($menu['id'], array_merge([1, 2, 7], $groupMenusIds));
            return [
                'name'     => z::arrayGet($menu, 'title', ''),
                'path'     => $vuePath(z::arrayGet($menu, 'index', '')),
                'url'      => $vueUrl(true, z::arrayGet($menu, 'index', '')),
                'icon'     => z::arrayGet($menu, 'icon', ''),
                'meta'     => [
                    'breadcrumb' => z::arrayGet($menu, 'breadcrumb', 0) === 1,
                    'real'       => z::arrayGet($menu, 'real', 0) === 1,
                    'show'       => z::arrayGet($menu, 'show', 0) === 1 && $show,
                    'has'        => $has,
                    'collapse'   => false,
                ],
                'children' => [],
            ];
        };

        $getChild = function ($convFun, $menu, $groupMenusIds) use ($menus) {
            $re = [];
            foreach ($menus as $k => $v) {
                if ($v['pid'] === $menu['id']) {
                    $re[] = $convFun($v, $groupMenusIds);
                }
            }

            return $re;
        };


        $groupMenusIds = [];// 拥有的菜单栏权限
        $Dao = new GroupMenuDao();
        $res = $Dao->find(['groupid' => explode(',', $user['group_id'])], true, [], 'groupid, menu');
        foreach ($res as $v) {
            $groupMenusIds = array_merge($groupMenusIds, explode(',', $v['menu']));
        }
        $groupMenusIds = array_unique($groupMenusIds);
        asort($groupMenusIds);

        $f = [];
        foreach ($menus as $kk => $vv) {
            if ($vv['pid'] === 0) {
                $menuConv                     = $conv($vv, $groupMenusIds);
                $menuConv['children']         = $getChild($conv, $vv, $groupMenusIds);
                $menuConv['meta']['collapse'] = in_array(true, array_column(array_column($menuConv['children'], 'meta'), 'show'));
                $menuConv['url']              =
                    in_array(true, array_column(array_column($menuConv['children'], 'meta'), 'has'))
                    && $menuConv['name'] !== '后台中心'
                        ? ''
                        : $menuConv['url'];
                $f[]                          = $menuConv;
            }
        }

        return $f;
    }
}
