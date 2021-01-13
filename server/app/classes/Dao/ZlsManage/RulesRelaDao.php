<?php

declare(strict_types=1);

namespace Dao\ZlsManage;

use Z;

class RulesRelaDao extends \Zls_Dao
{
    public const STATUS_NORMAL = 1;
    public const STATUS_BAN = 2;
    public const STATUS_IGNORE = 3;

    public function getColumns(): array
    {
        return [
            'id', //主键
            'rule_id', //规则id
            'group_id', //角色id
            'create_time', //创建时间
            'update_time', //更新时间
            'status', //状态:1正常，2禁止，3忽略
            'sort', //排序
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }

    public function getTable(): string
    {
        return 'auth_user_rules_rela';
    }

    public function verifyRules($columns = []): array
    {
        if (!$columns) {
            $columns = $this->getColumns();
        }
        $verifyrules           = [];
        $verifyrules['status'] = [
            'enum[' . self::STATUS_NORMAL . ',' . self::STATUS_BAN . ',' . self::STATUS_IGNORE . ']' => '非正常状态码',
        ];
        $verifyrules['sort']   = [
            'range[0,255]' => '排序范围为0-255',
        ];
        $rule                  = [];
        foreach ($columns as $column) {
            if (Z::arrayKeyExists($column, $verifyrules)) {
                $rule[$column] = $verifyrules[$column];
            }
        }

        return $rule;
    }
}
