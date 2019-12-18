<?php

use Zls\Migration\AbstractMigration;

/**
 * 操作日志
 * Class UserLogs
 */
class UserLogs extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('auth_user_logs');
        $table->comment('用户日志');
        $table->addColumn('userid', 'integer', ['default' => 0, 'limit' => 16777215, 'comment' => '对应用户Id']);
        $table->addColumn('operate_id', 'integer', ['default' => 0, 'limit' => 16777215, 'comment' => '操作人Id，游客为0']);
        $table->addColumn('content', 'text', ['comment' => '信息']);
        $table->addColumn('title', 'string', ['comment' => '标题', 'limit' => 100]);
        $table->addColumn('create_time', 'datetime', ['default' => null, 'null' => true, 'comment' => '创建时间']);
        $table->addColumn('update_time', 'datetime', ['default' => null, 'null' => true, 'comment' => '更新时间']);
        $table->addColumn('type', 'integer', ['default' => 1, 'limit' => 255, 'comment' => '类型:1正常，2警告，3错误']);
        $table->addColumn('status', 'integer', ['default' => 1, 'limit' => 255, 'comment' => '状态:1未读，2已读']);
        $table->create();
    }
}
