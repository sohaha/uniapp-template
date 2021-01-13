<?php
declare(strict_types=1);

namespace Business\ZlsManage;

use Dao\ZlsManage\MenuDao;
use Dao\ZlsManage\GroupMenuDao;
use Z;

/**
 * Class MenuBusiness
 * @package Business\ZlsManage
 */
class MenuBusiness extends \Zls_Business
{
    /**
     * 菜单列表
     * @return array
     */
    public function lists($groupid = 0): array
    {
        $dao  = new MenuDao();

        $fields = $dao->getReversalColumns(null, true);

        $dao->getDb()
            ->select($fields)
            ->from($dao->getTable())
            ->orderBy('pid', 'asc')
            ->orderBy('sort', 'asc');
        $result = $dao->getDb()->execute()->rows();

        $menuArr = [];
        if($groupid){
            $groupMenuDao = new GroupMenuDao();
            $fields = $groupMenuDao->getReversalColumns(["id","create_time","update_time"], true);
            $where["groupid"] = $groupid;
            $groupMenuDao->getDb()
                ->select($fields)
                ->from($groupMenuDao->getTable())
                ->where($where);
            $re = $dao->getDb()->execute()->row();

            if($re){
                $menuArr = explode(",",$re["menu"]);
            }
        }
        $data = [];
        foreach ($result as $key => $value){
            //如果$menuArr为空，is_show全部为真
            if($menuArr && in_array($value["id"],$menuArr)){
                $value["is_show"] = true;
            }else{
                $value["is_show"] = false;
            }
            if($value['pid'] == 0){
                $data[] = $value;
            }else{
                foreach ($data as $k => $v){
                    if($v['id'] == $value['pid']){
                        $data[$k]['child'][] = $value;
                    }
                }
            }
        }
        return $data ?: [];
    }

    /**
     * 新增菜单
     * @param $data
     * @return int|null
     */
    public function create($data)
    {
        $dao     = new MenuDao();
        $map     = $dao->getReversalColumns(['id']);

        $rule    = $dao->verifyRules(['title','index']);
        $retData = $errorMsg = $errorKey = null;
        if (z::checkData($data, $rule, $retData, $errorMsg, $errorKey, $dao->getDb())) {
            //验证pid是否合法
            $retData['pid'] = Z::arrayGet($retData, 'pid')?:0;
            if($retData['pid']){
                $where["id"] = $retData['pid'];
                $dao->getDb()->select('count(id) as total')
                    ->from($dao->getTable())
                    ->where($where);
                //echo Z::db()->getSql();die;
                $total = $dao->getDb()->execute()->value('total');
                if(!$total){
                    return "pid不合法";
                }
            }

            $retData                = z::readData($map, $retData, false);
            $date                   = date('Y-m-d H:i:s');
            $retData['create_time'] = $retData['update_time'] = $date;
            $retData['title'] = Z::arrayGet($retData, 'title');
            $retData['index'] = Z::arrayGet($retData, 'index');
            $retData['icon'] = Z::arrayGet($retData, 'icon')?:' ';
            $retData['breadcrumb'] = Z::arrayGet($retData, 'breadcrumb')?:0;
            $retData['real'] = Z::arrayGet($retData, 'real')?:0;
            $retData['show'] = Z::arrayGet($retData, 'show')?:0;

            return (int)$dao->insert($retData);
        }

        return $errorMsg;
    }

    /**
     * 删除菜单
     * @param $id
     * @return bool|int
     */
    public function delete($id)
    {
        $dao = new MenuDao();
        //先查询是否有子集
        $where["pid"] = $id;
        $dao->getDb()->select('count(id) as total')
            ->from($dao->getTable())
            ->where($where);
        //echo Z::db()->getSql();die;
        $total = $dao->getDb()->execute()->value('total');
        if($total){
            return "请先删除子集";
        }

        $result = $dao->delete($id);

        return $result ? [] : "id不存在";
    }

    /**
     * 更新菜单
     * @param $data
     * @return array|string|null
     */
    public function update($data)
    {
        $dao = new MenuDao();

        $rule    = $dao->verifyRules(['title','index']);
        $id =  Z::arrayGet($data, 'id')?:0;
        if(!$id){
            return "id不能为空";
        }
//        z::dump($rule);
        $retData = $errorMsg = $errorKey = null;

        if (z::checkData($data, $rule, $retData, $errorMsg, $errorKey, $dao->getDb())) {
            $retData['update_time'] = date('Y-m-d H:i:s');
            $retData['title'] = Z::arrayGet($retData, 'title');
            $retData['index'] = Z::arrayGet($retData, 'index');
            $retData['icon'] = Z::arrayGet($retData, 'icon')?:' ';
            $retData['breadcrumb'] = Z::arrayGet($retData, 'breadcrumb')?:0;
            $retData['real'] = Z::arrayGet($retData, 'real')?:0;
            $retData['show'] = Z::arrayGet($retData, 'show')?:0;
            $res = $dao->update($retData, ['id' => $data['id']]);

            return $res ? [] : '更新失败';
        } else {
            return $errorMsg;
        }
    }

    /**
     * 菜单排序
     * @param $data
     * @return bool
     */
    public function sort($data)
    {
        $dao  = new MenuDao();
        //保存需要更新的数据
        $newData = [];
        //把id作为数组的主键
//        $arr = array_combine(array_column($result, 'id'), $result);
        $count = 0;
        foreach ($data as $key => $value){
            $count++;
            //外层循环pid必为0
            $temp["id"] = $value["id"];
            $temp["pid"] = 0;
            $temp["sort"] = $count;
            $newData[] = $temp;

            if(isset($value["child"])){
                foreach ($value["child"] as $k => $v){
                    $count++;
                    $realPid = $value["id"];
                    $temp["id"] = $v["id"];
                    $temp["pid"] = $realPid;
                    $temp["sort"] = $count;
                    $newData[] = $temp;
                }
            }
        }
        $res = $dao->updateBatch($newData);
        return $res;
    }

    /**
     * 更新角色对应的菜单
     * @param $data
     * @return array|string|null
     */
    public function updateGroupMenu($data){
        $dao = new GroupMenuDao();

        $rule    = $dao->verifyRules(['groupid','menu']);

        $retData = $errorMsg = $errorKey = null;

        if (z::checkData($data, $rule, $retData, $errorMsg, $errorKey, $dao->getDb())) {
            $retData['update_time'] = date('Y-m-d H:i:s');
            $retData['groupid'] = Z::arrayGet($retData, 'groupid');
            $retData['menu'] = Z::arrayGet($retData, 'menu');
            //先查询，如果没有就添加，有就更新
            $where["groupid"] = $retData['groupid'];
            $dao->getDb()->select('count(id) as total')
                ->from($dao->getTable())
                ->where($where);
            $total = $dao->getDb()->execute()->value('total');

            if($total){
                $res = $dao->update($retData, ['groupid' => $retData['groupid']]);
            }else{
                $retData['create_time'] = date('Y-m-d H:i:s');
                $res = $dao->insert($retData);
            }

            return $res ? [] : '更新失败';
        } else {
            return $errorMsg;
        }
    }
}
