<?php

use Zls\Migration\AbstractMigration as M;

class UserRulesRela extends M
{
    public function change()
    {
        $table = $this->table('auth_user_rules_rela', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'integer', ['identity' => true, 'comment' => '主键'])->comment('角色权限规则对应');
        $table->addColumn('rule_id', 'integer', ['default' => 0, 'comment' => '规则id']);
        $table->addColumn('group_id', 'integer', ['default' => 0, 'comment' => '角色id']);
        $table->addColumn('create_time', 'datetime', ['default' => null, 'null' => true, 'comment' => '创建时间']);
        $table->addColumn('update_time', 'datetime', ['default' => null, 'null' => true, 'comment' => '更新时间']);
        $table->addColumn('status', 'integer', ['default' => 1, 'limit' => 255, 'comment' => '状态:1正常，2禁止，3忽略']);
        $table->addColumn('sort', 'integer', ['default' => 0, 'comment' => '排序']);
        $table->create();
        $date = date('Y-m-d H:i:s');
        $rows = [
            [
                'rule_id'     => 1,
                'group_id'    => 1,
                'create_time' => $date,
                'update_time' => $date,
                'status'      => 1,
            ],
            [
                'rule_id'     => 2,
                'group_id'    => 1,
                'create_time' => $date,
                'update_time' => $date,
                'status'      => 1,
            ]
        ];
        $table->insert($rows);
        $table->saveData();
    }
}
