<?php

use Zls\Migration\AbstractMigration as M;

/**
 * 管理员Token
 * Class UserToken
 */
class UserToken extends M
{
    public function change()
    {
        $table = $this->table('auth_user_token');
        $table->comment('管理员密钥');
        $table->addColumn('userid', 'integer', ['default' => 0, 'limit' => 16777215, 'comment' => '管理员Id']);
        $table->addColumn('token', 'string', ['comment' => 'token']);
        $table->addColumn('ip', 'string', ['limit' => 20, 'comment' => '登录IP']);
        $table->addColumn('ua', 'text', ['null' => true, 'comment' => 'User Agent']);
        $table->addColumn('create_time', 'datetime', ['default' => null, 'null' => true, 'comment' => '创建时间']);
        $table->addColumn('update_time', 'datetime', ['default' => null, 'null' => true, 'comment' => '更新时间']);
        $table->addColumn('status', 'integer', ['default' => 1, 'limit' => 255, 'comment' => '状态:1正常,2禁止']);
        $table->addIndex(['status', 'token']);
        $table->create();
    }
}
