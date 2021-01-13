<?php

use Zls\Action\StrUtils;
use Zls\Migration\AbstractMigration as M;
use Zls\Migration\MysqlAdapter;

/**
 * 管理员表
 * Class Users
 */
class Users extends M
{
    private $table = 'auth_user';

    public function up()
    {
        $table = $this->table($this->table);
        $table->comment('管理员');
        $table->addColumn('username', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_COMMENT => '用户名']);
        $table->addColumn('password', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_COMMENT => '用户密码']);
        $table->addColumn('key', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_COMMENT => '密码盐']);
        $table->addColumn('nickname', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_COMMENT => '用户昵称']);
        $table->addColumn('email', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_COMMENT => 'Email']);
        $table->addColumn('remark', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_COMMENT => '用户简介']);
        $table->addColumn('avatar', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_COMMENT => '头像']);
        $table->addColumn('status', self::TYPE_INTEGER, [self::OPTIONS_DEFAULT => 0, 'limit' => MysqlAdapter::INT_TINY, self::OPTIONS_COMMENT => '状态:-1软删除,0待激活,1正常,2禁止']);
        $table->addColumn('group_id', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', 'limit' => MysqlAdapter::TEXT_SMALL, self::OPTIONS_COMMENT => '角色Id']);
        $table->addColumn('is_super', self::TYPE_INTEGER, [self::OPTIONS_DEFAULT => 0, 'limit' => MysqlAdapter::INT_TINY, self::OPTIONS_COMMENT => '']);
        $table->addColumn('create_time', self::TYPE_DATETIME, [self::OPTIONS_DEFAULT => null, self::OPTIONS_NULL => true, self::OPTIONS_COMMENT => '创建时间']);
        $table->addColumn('update_time', self::TYPE_DATETIME, [self::OPTIONS_DEFAULT => null, self::OPTIONS_NULL => true, self::OPTIONS_COMMENT => '更新时间']);
        $table->addIndex(['username', 'status'], ['name' => 'username_status']);
        $table->create();
        /** @var StrUtils $StrUtils */
        $StrUtils = z::extension('Action\StrUtils');
        $time     = date('Y-m-d H:i:s');
        // 随机生成用户密码
        $userPassword = $StrUtils->randString(6, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        $key      = $StrUtils->randString();
        $rows[]       = [
            'username'    => 'manage',
            'key'         => $key,
            'status'      => 1,
            'group_id'    => 1,
            'is_super'    => 1,
            'nickname'    => '超级管理员',
            'email'       => 'admin@qq.com',
            'password'    => md5(Z::encrypt($userPassword, '', $key)),
            'create_time' => $time,
            'update_time' => $time,
        ];
        $key      = $StrUtils->randString();
        $rows[]       = [
            'username'    => 'admin',
            'key'         => $key,
            'status'      => 1,
            'group_id'    => 1,
            'is_super'    => 0,
            'nickname'    => '管理员',
            'email'       => 'admin@qq.com',
            'password'    => md5(Z::encrypt($userPassword, '', $key)),
            'create_time' => $time,
            'update_time' => $time,
        ];
        $key          = $StrUtils->randString();
        $rows[]       = [
            'username'    => 'seekwe',
            'key'         => $key,
            'status'      => 1,
            'group_id'    => 2,
            'is_super'    => 0,
            'nickname'    => '编辑',
            'email'       => 'seekwe@gmail.com',
            'password'    => md5(Z::encrypt($userPassword, '', $key)),
            'create_time' => $time,
            'update_time' => $time,
            'remark'      => '一名编辑员',
        ];
        $table->insert($rows);
        $table->saveData();
        Z::defer(function () use ($rows, $userPassword) {
            $this->printStrN();
            $this->printStrN('创建默认管理账号：', 'light_cyan');
            foreach ($rows as $k => $row) {
                $this->printStr('  用户名：');
                $this->printStrN($row['username'], 'blue');
                $this->printStr('  密码  ：');
                $this->printStrN($userPassword, 'red');
                $this->printStrN();
            }
        });
    }

    public function down()
    {
        $this->table($this->table)->drop()->save();
    }
}
