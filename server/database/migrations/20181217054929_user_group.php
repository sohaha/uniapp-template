<?php

use Zls\Migration\AbstractMigration as M;

class UserGroup extends M
{
    public function change()
    {
        $table = $this->table('auth_user_group');
        $table->comment('用户角色');
        $table->addColumn('name', 'string', ['default' => '', 'null' => false, 'comment' => '角色名称']);
        $table->addColumn('create_time', 'datetime', ['default' => null, 'null' => true, 'comment' => '创建时间']);
        $table->addColumn('update_time', 'datetime', ['default' => null, 'null' => true, 'comment' => '更新时间']);
        $table->addColumn('status', 'integer', ['default' => 1, 'limit' => 255, 'comment' => '状态:1正常，2禁止']);
        $table->addColumn('remark', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_COMMENT => '角色简介']);
        $table->create();
        $rows = [
            [
                'name'        => '管理员',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
                'remark'      => '我是一个管理员',
            ],
            [
                'name'        => '编辑员',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
                'remark'      => '我是一个编辑员',
            ],
        ];
        $table->insert($rows);
        $table->saveData();
    }
}
