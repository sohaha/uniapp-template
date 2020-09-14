<?php
/*
 * @Author: seekwe
 * @Date: 2020-07-29 16:54:03
 * @Last Modified by:: seekwe
 * @Last Modified time: 2020-07-30 17:19:43
 */

use Zls\Migration\AbstractMigration as M;
use Zls\Migration\MysqlAdapter;

class CreateWxMember extends M
{
    private $tableName = 'wx_member';

    public function change()
    {
        $table = $this->table($this->tableName);
        $table->addColumn('raw_data', self::TYPE_TEXT, [self::OPTIONS_NULL => true, self::OPTIONS_COMMENT => '原始数据']);
        $table->addColumn('nickname', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_COMMENT => '用户昵称']);
        $table->addColumn('openid', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_LIMIT => 64, self::OPTIONS_COMMENT => 'openid']);
        $table->addColumn('username', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_LIMIT => 64, self::OPTIONS_COMMENT => '用户名']);
        $table->addColumn('password', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_LIMIT => 64, self::OPTIONS_COMMENT => '用户密码']);
        $table->addColumn('unionid', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_LIMIT => 64, self::OPTIONS_COMMENT => 'unionid']);
        $table->addColumn('session_key', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_LIMIT => 64, self::OPTIONS_COMMENT => 'session_key']);
        $table->addColumn('avatar', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_COMMENT => '头像']);
        $table->addColumn('status', self::TYPE_INTEGER, [self::OPTIONS_DEFAULT => 1, 'limit' => MysqlAdapter::INT_TINY, self::OPTIONS_COMMENT => '状态:-1软删除,0待激活,1正常,2禁止']);
        $table->addColumn('create_time', self::TYPE_DATETIME, [self::OPTIONS_DEFAULT => null, self::OPTIONS_NULL => true, self::OPTIONS_COMMENT => '创建时间']);
        $table->addColumn('update_time', self::TYPE_DATETIME, [self::OPTIONS_DEFAULT => null, self::OPTIONS_NULL => true, self::OPTIONS_COMMENT => '更新时间']);
        $table->comment('微信会员')->create();
    }
}
