<?php

use Zls\Migration\AbstractMigration as M;
use Zls\Migration\MysqlAdapter;

class UserRules extends M
{
    public function up()
    {
        $table = $this->table('auth_user_rules', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'integer', ['identity' => true, 'comment' => '主键'])->comment('权限规则');
        $table->addColumn('title', 'string', ['default' => '', 'null' => false, 'comment' => '规则名称']);
        $table->addColumn('create_time', 'datetime', ['default' => null, 'null' => true, 'comment' => '创建时间']);
        $table->addColumn('update_time', 'datetime', ['default' => null, 'null' => true, 'comment' => '更新时间']);
        $table->addColumn('status', 'integer', ['default' => 1, 'limit' => 255, 'comment' => '状态：1正常，2禁止；标识码不支持禁止']);
        $table->addColumn('type', 'integer', ['default' => 1, 'limit' => MysqlAdapter::INT_TINY, 'comment' => '类型：1路由，2标识码']);
        $table->addColumn('mark', 'string', ['default' => '', 'comment' => '标识（唯一）']);
        $table->addIndex(['mark'], ['unique' => true]);// 唯一
        $table->addColumn('remark', 'string', ['default' => '', 'comment' => '备注']);
        $table->addColumn('condition', 'string', ['default' => '', 'comment' => '附加条件']);
        $table->addColumn('sort', 'integer', ['default' => 0, 'comment' => '排序']);
        $table->create();
        $rows = [
            [
                'title'       => '用户管理',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
                'remark'      => '',
                'type'        => 1,
                'mark'        => 'ZlsManage/UserManageApi*',
            ],
            [
                'title'       => '系统管理权限',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
                'type'        => 2,
                'remark'      => '系统管理权限',
                'mark'        => 'systems',
            ],
        ];
        $table->insert($rows);
        $table->saveData();
    }

    public function down()
    {
        $this->table('auth_user_rules')->drop()->save();
    }
}
