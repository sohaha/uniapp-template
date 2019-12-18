<?php
declare (strict_types=1);

namespace Dao\ZlsManage;

use Z;
use Zls_Database_ActiveRecord as Zls_Database_ActiveRecordAlias;

/**
 * Class UserDao
 * @package Dao\ZlsManage
 */
class UserDao extends \Zls_Dao
{
    public const STATUS_NORMAL = 1;
    public const STATUS_BAN = 2;
    public const STATUS_DELETE = -1;
    public const STATUS_WAIT = 0;


    /**
     * 查询前置.
     *
     * @param Zls_Database_ActiveRecordAlias $db
     * @param string                         $method
     *
     * @return void|array
     */
    public static function findBefore(Zls_Database_ActiveRecordAlias $db, $method)
    {
        // 查询排除软删除的用户
        $db->where(['status !=' => self::STATUS_DELETE]);
    }


    public function verifyRules($columns = []): array
    {
        if (!$columns) {
            $columns = $this->getColumns();
        }
        $verifyrules             = [];
        $verifyrules['username'] = [
            'functions[strip_tags,trim]' => '',
            'required'                   => '用户名不能为空',
            'max_len[20]'                => '用户名最多20字符',
            'function'                   => function ($key, $username, $data, $args, &$returnValue, &$break, &$db) {
                $id = z::arrayGet($data, 'id');
                if ($id && ($user = $this->find($id)) && (z::arrayGet($user, 'username') !== $username)) {
                    $username = null;
                }
                if ($username && $this->find(['username' => $username], false, [], 'id')) {
                    return '用户名已被使用';
                }

                return null;
            },
        ];
        $verifyrules['remark']   = [
            'functions[strip_tags,trim]' => '',
            'max_len[200]'               => '用户简介最多200字符',
        ];
        $verifyrules['avatar']   = [
            'default[]'                  => '',
            'functions[strip_tags,trim]' => '',
            'max_len[250]'               => '头像地址不能超过250字符',
        ];
        $verifyrules['status']   = [
            'functions[intval]' => '',
            'enum[1,2]'         => '用户状态值错误',
        ];
        $verifyrules['password'] = [
            'min_len[3]'  => '密码最少3字符',
            'max_len[50]' => '密码最多50字符',
            'function'    => static function ($key, $password, $data) {
                $password2 = z::arrayGet($data, 'password2');
                if ($password2 !== $password) {
                    return '两次密码不一致';
                }

                return null;
            },
        ];
        $verifyrules['email']    = [
            'email'    => 'Email地址错误',
            'function' => function ($_, $value, $data) {
                $id    = z::arrayGet($data, 'id');
                $email = $value;
                if ($id && ($user = $this->find($id)) && (z::arrayGet($user, 'email') === $email)) {
                    $email = null;
                }
                if ($email && $this->find(['email' => $value], false, [], 'id')) {
                    return 'Email已被使用';
                }

                return null;
            },
        ];
        $rule                    = [];
        foreach ($columns as $column) {
            if (Z::arrayKeyExists($column, $verifyrules)) {
                $rule[$column] = $verifyrules[$column];
            }
        }

        return $rule;
    }

    public function getColumns(): array
    {
        return [
            'id',//id
            'username',//用户名
            'password',//用户密码
            'key',//密码盐
            'nickname',//用户昵称
            'email',//Email
            'remark',//用户简介
            'avatar',//头像
            'status',//状态:-1软删除,0待激活,1正常,2禁止
            'group_id',//角色Id
            'create_time',//创建时间
            'update_time',//更新时间
        ];
    }

    public function getHideColumns(): array
    {
        return ['password', 'key'];
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }

    public function getTable(): string
    {
        return 'auth_user';
    }

    /**
     * 加密密码
     *
     * @param $password
     * @param $key
     *
     * @return string
     */
    public function encryptPassword($password, $key): string
    {
        return md5(Z::encrypt($password, '', $key));
    }
}
