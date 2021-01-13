<?php

declare(strict_types=1);

namespace Dao\ZlsManage;

use Z;

class GroupMenuDao extends \Zls_Dao
{
    public function getColumns(): array
    {
        return [
            'id', //id
            'groupid', //对应角色Id
            'menu', //菜单
            'create_time', //创建时间
            'update_time', //更新时间
        ];
    }

    public function verifyRules($columns = [])
    {
        if (!$columns) {
            $columns = $this->getColumns();
        }
        $verifyrules             = [];
        $verifyrules['groupid'] = [
            'functions[strip_tags,trim]' => '',
            'required'                   => '角色id不能为空'
        ];
        $verifyrules['menu'] = [
            'functions[strip_tags,trim]' => '',
            'required'                   => '菜单内容不能为空'
        ];

        $rule                    = [];

        foreach ($columns as $column) {
            if (Z::arrayKeyExists($column, $verifyrules)) {
                $rule[$column] = $verifyrules[$column];
            }
        }

        return $rule;
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }

    public function getTable(): string
    {
        return 'auth_group_menu';
    }
}
