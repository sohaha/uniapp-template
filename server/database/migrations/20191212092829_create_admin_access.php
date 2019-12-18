<?php

use Zls\Migration\AbstractMigration as M;

class CreateAdminAccess extends M
{
    private $tableName = 'auth_user_access_log';

    public function change()
    {
        $table = $this->table($this->tableName);
        $table->comment('用户访问后台日志');
        $table->addColumn('userid', 'integer', ['default' => 0, 'limit' => 16777215, 'comment' => '管理员Id']);
        $table->addColumn('route', 'text', ['null' => true, 'comment' => '路由']);
        $table->addColumn('param', 'text', ['null' => true, 'comment' => 'Get数据']);
        $table->addColumn('input', 'text', ['null' => true, 'comment' => 'Input数据']);
        $table->addColumn('content', 'text', ['null' => true, 'comment' => '返回数据']);
        $table->addColumn('create_time', 'datetime', ['default' => null, 'null' => true, self::OPTIONS_COMMENT => '创建时间']);
        $table->create();
    }
}
