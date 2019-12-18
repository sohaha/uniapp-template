<?php
declare(strict_types=1);

namespace Business\ZlsManage;

use Dao\ZlsManage\LogsDao;
use Dao\ZlsManage\TokenDao;
use Dao\ZlsManage\UserDao;
use Z;
use Zls\Action\Id;
use Zls\Action\StrUtils;

/**
 * Class UserBusiness
 * @package Business\User
 */
class UserBusiness extends \Zls_Business
{
    public const USER_CACHE_KEY = 'USER_CACHE_KEY_';

    /**
     * 根据token获取用户信息
     *
     * @param $token
     *
     * @return array
     */
    public function tokenToInfo($token): array
    {
        /** @var TokenDao $tokenDao */
        $tokenDao = new TokenDao();
        $user     = [];
        if ($tokenInfo = $tokenDao->find(['token' => $token, 'status' => 1], false, [])) {
            $user            = $this->info((int)$tokenInfo['userid']);
            $GroupBusiness   = new GroupBusiness();
            $groupid         = explode(',', (string)$user['group_id']);
            $user['groups']  = $GroupBusiness->all();
            $user['menus']   = $GroupBusiness->getMenu($groupid);
            $user['marks']   = $GroupBusiness->getMarks($groupid);
            $user['routers'] = $GroupBusiness->getRouter($groupid);
        }

        return $user;
    }

    /**
     * 过滤用户密码等敏感字段
     *
     * @param $user
     *
     * @return array
     */
    public function filterField($user): array
    {
        /** @var UserDao $Dao */
        $Dao = z::dao('ZlsManage\UserDao', true);

        return z::readData($Dao->getReversalColumns(), $user);
    }

    /**
     * 用户名获取用户信息
     *
     * @param $username
     *
     * @return array
     */
    public function nameToInfo($username): array
    {
        /** @var UserDao $dao */
        $dao  = new UserDao();
        $user = $dao->find(['username' => $username], false, [], ['*']);
        if ($user) {
            $user = $dao->bean($user);
        }

        return $user;
    }

    /**
     * 创建token
     *
     * @param $id
     *
     * @return string
     */
    public function createToken($id): string
    {
        /** @var Id $ActionId */
        $ActionId = z::extension('Action\Id');

        return z::tap(
            Z::encrypt($id . '_' . date('y-m-d H:i:s') . '_' . $ActionId->uniqueId(4)),
            static function ($token) use ($id) {
                $tokenDao = new TokenDao();
                $tokenDao->saveToken($id, $token);
                $dao = new LogsDao();
                $dao->createOperationLog($id, null, '登录成功，欢迎回来！', $id, $dao::TYPE_NORMAL, $dao::STATUS_READ);
            }
        );
    }

    /**
     * 清除用户Token
     *
     * @param $token
     *
     * @return bool|int
     */
    public function clearToken($token)
    {
        return (new TokenDao())->update(['status' => 2], ['status' => 1, 'token' => $token]);
    }

    /**
     * 清除指定用户全部token
     *
     * @param $userid
     *
     * @return bool|int
     */
    public function clearAllToken($userid)
    {
        return (new TokenDao())->update(['status' => 2], ['status' => 1, 'userid' => $userid]);
    }

    /**
     * 用户列表
     *
     * @param        $page
     * @param int    $pagesize
     * @param array  $where
     *
     * @return array
     */
    public function lists($page, $pagesize = 10, $where = []): array
    {
        $dao           = new UserDao();
        $fields        = $dao->getReversalColumns(null, true);
        $data          = $dao->getPage($page, $pagesize, '{page}', $fields, $where, ['id' => 'desc']);
        $data['items'] = $dao->beans($data['items']);

        return $data;
    }

    /**
     * 更新用户
     *
     * @param     $id
     * @param     $data
     * @param int $operationUser
     *
     * @return int|string
     */
    public function update($id, $data, $operationUser = 0)
    {
        $dao = new UserDao();
        if (!$user = $dao->find($id, false, [], 'key,id,email')) {
            return '用户不存在';
        }
        $userid    = $user['id'];
        $veriField = ['status', 'remark', 'avatar', 'nickname'];
        $email     = z::arrayGet($data, 'email');
        if ($email && $user['email'] !== $email) {
            $veriField[] = 'email';
        }
        if (z::arrayKeyExists('password', $data)) {
            $veriField[] = 'password';
        }
        $rule    = $dao->verifyRules($veriField);
        $retData = $errorMsg = $errorKey = null;
        if (z::checkData($data, $rule, $retData, $errorMsg, $errorKey, $dao->getDb())) {
            $map                    = $dao->getColumns();
            $retData                = z::readData($map, $retData, false);
            $retData['update_time'] = date('Y-m-d H:i:s');
            if ($retData['avatar']) {
                /**
                 * @var AssistBusiness $AssistBusiness
                 */
                $AssistBusiness    = z::business('ZlsManage\AssistBusiness');
                $avatarFilename    = 'user_' . md5((string)$id) . '.png';
                $retData['avatar'] = $AssistBusiness->mvAvatar((string)$retData['avatar'], $avatarFilename);
            }
            $updatePassState = in_array('password', $veriField, true);
            if ($updatePassState) {
                $retData['password'] = $dao->encryptPassword($retData['password'], $user['key']);
            }

            return z::tap($dao->update($retData, $id), function (&$res) use ($userid, $operationUser, $updatePassState) {
                if ($res) {
                    $updatePassState && $this->updatePasswordTip($userid, $operationUser);
                    Z::cache()->delete(self::USER_CACHE_KEY . $userid);
                }
            });
        }

        return $errorMsg;
    }

    /**
     * 建立用户
     *
     * @param $data
     *
     * @return string|int
     */
    public function create($data)
    {
        $dao     = new UserDao();
        $map     = $dao->getReversalColumns(['id']);
        $rule    = $dao->verifyRules(array_keys($data));
        $retData = $errorMsg = $errorKey = null;
        if (z::checkData($data, $rule, $retData, $errorMsg, $errorKey, $dao->getDb())) {
            $retData                = z::readData($map, $retData, false);
            $date                   = date('Y-m-d H:i:s');
            $retData['create_time'] = $retData['update_time'] = $date;
            /** @var StrUtils $StrUtils */
            $StrUtils            = z::extension('Action\StrUtils');
            $key                 = $StrUtils->randString();
            $retData['key']      = $key;
            $retData['password'] = $dao->encryptPassword($retData['password'], $key);
            $retData['group_id'] = Z::arrayGet($retData, 'group_id') ?: 0;

            return (int)$dao->insert($retData);
        }

        return $errorMsg;
    }

    /**
     * 删除用户
     *
     * @param $userid
     *
     * @return int|bool
     */
    public function delete($userid)
    {
        $UserDao = new UserDao();
        $count   = $UserDao->find(null, false, [], 'count(*) as count');
        if (z::arrayGet($count, 'count') <= 1) {
            return '不予许删除唯一用户';
        }

        return $UserDao->delete($userid);
    }

    /**
     * 获取真实用户id
     *
     * @param $userid
     *
     * @return int|bool
     */
    public function decodeUserid($userid)
    {
        return z::extension('Action\Id')->decode($userid) ?: $userid;
    }

    public function checkPass(array $user, $pass)
    {
        $UserDao = new UserDao();
        $res     = true;
        switch (true) {
            case !$user:
                $res = '用户不存在';
                break;
            case ($UserDao->encryptPassword($pass, $user['key']) !== $user['password']):
                $dao = new LogsDao();
                $dao->createOperationLog($user['id'], null, "游客尝试登录【{$user['username']}】账号！", 0, LogsDao::TYPE_WARN);
                $res = '密码错误';
                break;
            case $UserDao::STATUS_WAIT === $user['status']:
                $res = '用户待激活';
                break;
            case $UserDao::STATUS_BAN === $user['status']:
                $res = '用户已停用';
                break;
            default:
        }

        return $res;
    }

    /**
     * 修改用户密码
     *
     * @param $userid
     * @param $oldPassword
     * @param $newPassword
     * @param $operationUser
     *
     * @return string|int
     */
    public function editPassword($userid, $oldPassword, $newPassword, $operationUser)
    {
        $UserDao = new UserDao();
        $where   = ['id' => $userid];
        $user    = $UserDao->find($where, false, [], '*');
        if (!$user) {
            return '用户不存在';
        }
        if (md5(z::encrypt($oldPassword, '', $user['key'])) !== $user['password']) {
            return '原密码错误';
        }
        /** @var StrUtils $StrUtils */
        $StrUtils         = z::extension('Action\StrUtils');
        $data['key']      = $StrUtils->randString();
        $data['password'] = $UserDao->encryptPassword($newPassword, $data['key']);

        return z::tap($UserDao->update($data, $where), function ($res) use ($userid, $operationUser) {
            if ($res) {
                $this->updatePasswordTip($userid, $operationUser);
            }
        });
    }

    public function updatePasswordTip($userid, $operationId): void
    {
        // 非自己修改了用户密码之后需要记录日志 修改密码之后要重置用户token
        if ((int)$userid === (int)$operationId) {
            $tip    = '修改密码成功';
            $level  = LogsDao::TYPE_NORMAL;
            $status = LogsDao::STATUS_READ;
        } else {
            $username = (new UserDao)->findCol('username', $operationId) ?: '未知用户';
            $tip      = "您的密码被【{$username}】修改！";
            $level    = LogsDao::TYPE_WARN;
            $status   = LogsDao::TYPE_NORMAL;
        }
        (new LogsDao())->createOperationLog($userid, null, $tip, $operationId, $level, $status);
        $this->clearAllToken($userid);
    }

    /**
     * 获取用户上次登录信息
     *
     * @param $userid
     * @param $token
     *
     * @return array
     */
    public function getLast($userid, $token): array
    {
        $tokenDao = new TokenDao();
        $field    = ['ip', 'ua', 'create_time'];
        $last     = $tokenDao->find(['userid' => $userid, 'token !=' => $token], false, ['id' => 'desc'], $field);

        return z::readData($field, $last);
    }

    public function getTokenLists($userid, $showToken = false): array
    {
        $tokenDao = new TokenDao();
        $field    = ['ip', 'ua', 'create_time'];
        if ($showToken) {
            $field[] = 'token';
        }
        $last = $tokenDao->findAll(['userid' => $userid, 'status' => $tokenDao::STATUS_OPEN], ['id' => 'desc'], null, $field);

        return z::readData($field, $last);
    }

    /**
     * 是否超级管理员
     *
     * @param int $userid
     *
     * @return boolean
     */
    public function isSuperAdmin(int $userid): bool
    {
        $superAdminIds = z::config('ini.manage.superAdmin', true, [1]);

        return in_array($userid, $superAdminIds, true);
    }

    public function info($userid): array
    {
        return Z::cacheDate(self::USER_CACHE_KEY . $userid, static function () use ($userid) {
            $UserDao = new UserDao();
            $where   = ['id' => $userid];
            if ($user = $UserDao->find($where, false, [], '*')) {
                return $user;
            }

            return null;
        }) ?: [];
    }
}
