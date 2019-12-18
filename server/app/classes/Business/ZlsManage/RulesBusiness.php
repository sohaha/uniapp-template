<?php
declare (strict_types=1);

namespace Business\ZlsManage;

use Dao\ZlsManage\RulesDao;
use Dao\ZlsManage\RulesRelaDao;
use Z;

/**
 * 规则业务
 */
class RulesBusiness extends \Zls_Business
{
    /**
     * 规则列表
     *
     * @param array $where
     *
     * @return array
     */
    public function lists($where = []): array
    {
        $dao    = new RulesDao();
        $fields = ['mark', 'id', 'status', 'title', 'type', 'remark'];

        return $dao->findAll($where, ['sort' => 'desc', 'id' => 'desc'], null, $fields);
    }

    public function findRows(array $ids): array
    {
        $dao = new RulesDao();

        return $dao->findAll(['id' => $ids], ['sort' => 'desc', 'id' => 'desc'], null, 'sort,status,router,title,condition');
    }

    /**
     * 更新用户规则
     *
     * @param $gid
     * @param $id
     * @param $status
     * @param $sort
     *
     * @return null|array|string
     */
    public function updateUserRuleStatus(int $gid, int $id, int $status, int $sort)
    {
        $time         = date('Y-m-d H:i:s');
        $RulesRelaDao = new RulesRelaDao();
        $where        = ['group_id' => $gid, 'rule_id' => $id];
        $data         = ['status' => $status, 'sort' => $sort, 'update_time' => $time];
        $rules        = $RulesRelaDao->verifyRules(array_keys($data));
        $retData      = $errorMsg = $errorKey = null;
        if (z::checkData($data, $rules, $retData, $errorMsg, $errorKey)) {
            $row = $RulesRelaDao->find($where);
            if ($row) {
                $res = $RulesRelaDao->update($retData, $row['id']);
            } else {
                $data = $where + $retData + ['create_time' => $time];
                $res  = $RulesRelaDao->insert($data);
            }

            // todo 更新缓存
            return $res ? [$data] : '更新失败';
        }

        return $errorMsg;
    }

    /**
     * 添加规则
     *
     * @param string $title
     * @param        $mark
     * @param        $remark
     *
     * @return null|array|string
     */
    public function addRules($title, $mark, $type, $remark)
    {
        $data    = ['title' => $title, 'mark' => $mark, 'remark' => $remark, 'type' => $type];
        $dao     = new RulesDao();
        $rules   = $dao->verifyRules(array_keys($data));
        $retData = $errorMsg = $errorKey = null;
        if (z::checkData($data, $rules, $retData, $errorMsg, $errorKey)) {
            $id = $dao->insert($retData);

            return $id ? ['id' => $id] : '添加失败';
        }

        return $errorMsg;
    }

    /**
     * 编辑规则
     *
     * @param        $id
     * @param string $title
     * @param        $mark
     * @param        $remark
     *
     * @return null|array[]
     */
    public function editRules($id, $title, $mark, $remark)
    {
        $data    = ['title' => $title, 'mark' => $mark, 'remark' => $remark, 'id' => $id];
        $dao     = new RulesDao();
        $rules   = $dao->verifyRules(array_keys($data));
        $retData = $errorMsg = $errorKey = null;
        if (z::checkData($data, $rules, $retData, $errorMsg, $errorKey)) {
            $retData = z::readData($dao->getReversalColumns('id'), $retData, false);
            $rs      = $dao->update($retData, $id);

            return $retData + ['res' => $rs];
        }

        return $errorMsg;
    }

    /**
     * 删除规则
     *
     * @param int $id
     *
     * @return bool|int
     */
    public function deteleRules($id)
    {
        return (new RulesDao())->delete($id);
    }
}
