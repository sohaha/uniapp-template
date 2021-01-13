<?php

declare(strict_types=1);

namespace Dao\ZlsManage;

use Z;
use Zls_Database_ActiveRecord;

class RulesDao extends \Zls_Dao
{
    public const TYPE_ROUTER = 1; //路由
    public const TYPE_MARK = 2; //标识码

    public function getColumns(): array
    {
        return [
            'id', //主键
            'title', //规则名称
            'create_time', //创建时间
            'update_time', //更新时间
            'status', //状态：1正常，2禁止；标识码不支持禁止
            'type', //类型：1路由，2标识码
            'mark', //标识码（唯一）
            'remark', //备注
            'condition', //附加条件
            'sort', //排序
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }

    public function getTable(): string
    {
        return 'auth_user_rules';
    }

    /**
     * @param Zls_Database_ActiveRecord $db
     * @param string                    $method
     *
     * @return void|array
     */
    public static function findBefore(Zls_Database_ActiveRecord $db, $method)
    { }

    /**
     * @param Zls_Database_ActiveRecord $db
     * @param string                    $method
     *
     * @return void|array
     */
    // public static function deleteBefore(Zls_Database_ActiveRecord $db, $method)
    // { }

    /**
     * @param Zls_Database_ActiveRecord $db
     * @param string                    $method
     *
     * @return void|array
     */
    public static function insertBefore(Zls_Database_ActiveRecord $db, $method, &$data)
    { }

    /**
     * @param Zls_Database_ActiveRecord $db
     * @param string                    $method
     *
     * @return void|array
     */
    public static function updateBefore(Zls_Database_ActiveRecord $db, $method, &$data)
    { }

    public function find($values, $isRows = false, array $orderBy = [], $fields = null): array
    {
        $this->cache(3);

        return parent::find($values, $isRows, $orderBy, $fields);
    }

    public function verifyRules($columns = []): array
    {
        if (!$columns) {
            $columns = $this->getColumns();
        }
        $verifyrules          = [];
        $verifyrules['title'] = [
            'required' => '名称不能为空',
        ];
        $verifyrules['type'] = [
            'required' => '类型不能为空',
        ];
        $verifyrules['mark']  = [
            'required' => '标识不能为空',
            'function' => function ($key, $value, $data, $args, &$returnValue, &$break, &$db) {
            //     if (strpos(z::arrayGet($data, 'mark', ''), "\n") !== false) {
            //         $markArr = explode("\n", $data['mark']);
            //         $markArr2 = [];
            //         foreach ($markArr as $key => $value) {
            //             $value = trim($value);
            //             if ($value) {
            //                 if (!$this->find(['mark' => $value])) {
            //                     $markArr2[] = $value;
            //                 }
            //             }
            //         }
            //         $returnValue = join(',', $markArr2) . ',';
            //         return null;
            //     }

                $id           = z::arrayGet($data, 'id');
                $row          = ((bool) $id) ? $this->find($id) : [];
                $verifyRouter = $row ? $data['mark'] !== $row['mark'] && $data['id'] : true;
                if ($verifyRouter && ($r = $this->find(['mark' => $data['mark']])) && ($r['id'] != $id)) {
                    return '标识规则已存在';
                }

                return null;
            },
        ];
        $rule                 = [];
        foreach ($columns as $column) {
            if (Z::arrayKeyExists($column, $verifyrules)) {
                $rule[$column] = $verifyrules[$column];
            }
        }

        return $rule;
    }
}
