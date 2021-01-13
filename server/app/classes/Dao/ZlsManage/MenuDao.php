<?php

declare(strict_types=1);

namespace Dao\ZlsManage;

use Z;

class MenuDao extends \Zls_Dao
{
    public function getColumns(): array
    {
        return [
            'id', //id
            'title', //菜单标题
            'index', //路由
            'icon', //图标
            'breadcrumb', //面包屑显示
            'real', //面包屑可点击
            'show', //导航栏显示
            'pid', //父id
            'sort', //排序id
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
        $verifyrules['title'] = [
            'functions[strip_tags,trim]' => '',
            'required'                   => '菜单名称不能为空'
        ];
        $verifyrules['index'] = [
            'functions[strip_tags,trim]' => '',
            'required'                   => '路由不能为空'
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

    public function getHideColumns() {
        return ['create_time','update_time'];
    }

    public function getTable(): string
    {
        return 'menu';
    }
}
