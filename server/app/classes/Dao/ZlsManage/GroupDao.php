<?php

declare(strict_types=1);

namespace Dao\ZlsManage;

use Z;

class GroupDao extends \Zls_Dao
{
    public function getColumns(): array
    {
        return [
            'id', //id
            'name', //角色名称
            'create_time', //创建时间
            'update_time', //更新时间
            'status', //状态:1正常，2禁止
            'remark', //角色简介
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }

    public function getTable(): string
    {
        return 'auth_user_group';
    }


    public function verifyRules($columns = []): array
    {
        if (!$columns) {
            $columns = $this->getColumns();
        }
        $verifyrules         = [];
        $rules               = [];
        $verifyrules['name'] = $this->ruleName();
        foreach ($columns as $column) {
            if (Z::arrayKeyExists($column, $verifyrules)) {
                $rules[$column] = $verifyrules[$column];
            }
        }

        return $rules;
    }

    private function ruleName(): array
    {
        return [
            'required' => '角色名称不能为空',
            'function' => function ($key, $value, $data, $args, &$returnValue, &$break, &$db) {
                $row          = ((bool) $id = z::arrayGet($data, 'id')) ? $this->find($id) : [];
                $verifyRouter = (bool) $row ? $data['name'] !== $row['name'] : true;
                if ($verifyRouter && (!z::checkValue($value, ['unique[' . $this->getTable() . '.name]']))) {
                    return '角色名称已存在';
                }

                return null;
            },
        ];
    }
}
