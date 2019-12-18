<?php
declare(strict_types=1);

namespace Controller\ZlsManage;

use Business\ZlsManage\AssistBusiness;
use Controller\ZlsManage;
use Z;

/**
 * 后台-用户接口
 * @author        影浅 seekwe@gmail.com
 * @desc          需要传递 header token（GetToken除外）
 */
class UserApi extends ZlsManage
{
    /**
     * 用户详情
     * @time       2018-11-8 17:57:48
     * @api-return array "data.marks" 权限标识列表
     * @api-return array "data.menus" 用户菜单
     * @api-return array "data.system" 系统信息 没有'systems'权限不可见
     */
    public function GETzUseriInfo(): array
    {
        $show = ['id', 'group_id', 'avatar', 'email', 'nickname', 'username', 'status', 'marks', 'system', 'last', 'groups', 'menus'];
        $user = Z::arrayFilter($this->getInfo(), static function ($v, $k) use ($show) {
            return in_array($k, $show, true);
        });
        // todo 注意控制某些用户不显示系统信息
        if ($this->getInfo('isSuper') || $this->hasPermission('systems')) {
            $user['system'] = (new AssistBusiness())->getSystemInfo();
        }
        $user['last'] = $this->UserBusiness->getLast($user['id'], $this->getToken());

        return [200, '用户信息', $user];
    }

    /**
     * 用户登录
     * @time       2018-11-1 13:46:28
     * @api-post   string user 用户名
     * @api-post   string pass 用户密码
     * @api-ignore 忽略权限控制
     * @api-desc   游客权限
     * @return array
     * @return string "data.token" 鉴权token
     * @return string "data.avatar" 用户头像
     */
    public function POSTzGetToken(): array
    {
        $user   = z::postGet('user');
        $pass   = z::postGet('pass');
        $user   = $this->UserBusiness->nameToInfo($user);
        $status = $this->UserBusiness->checkPass($user, $pass);
        if (is_string($status)) {
            return [211, $status];
        }
        $user          = $this->UserBusiness->filterField($user);
        $user['token'] = $this->UserBusiness->createToken($user['id']);

        return [200, '登录成功', $user];
    }

    /**
     * 更新用户资料
     * @time      2019-3-4 19:30:56
     * @api-put   int id 用户ID  '' y 注意：如果空表示更新自己
     * @api-put   int status 用户状态  null y 1使用,2禁止
     * @return array|string
     */
    public function PUTzUpdate()
    {
        $uid     = (int)$this->getInfo('id');
        $userid  = (int)(z::postText('id') ?: $uid);
        $post    = z::postText();
        $isSuper = $this->getInfo('isSuper');
        // 被修改的用户是否超级管理员
        $userIsSuper = $this->UserBusiness->isSuperAdmin((int)$userid);
        $status      = (int)z::arrayGet($post, 'status');
        $isMe        = $userid === $uid;
        if ($isMe && 1 !== $status) {
            return '不能禁止自己';
        }
        if (($userid !== $uid) && $userIsSuper && $status !== null) {
            return '不能更新该账户状态';
        }
        // 如果是超级管理员有权限更新用户密码
        $map = ['status', 'avatar', 'remark', 'email', 'nickname'];
        if ($isSuper && z::arrayGet($post, 'password')) {
            $map[] = 'password';
            $map[] = 'password2';
        }
        if ($isSuper && !$isMe) {
            $map[] = 'group_id';
        }
        $data = z::readData($map, $post);
        $rs   = $this->UserBusiness->update($userid, $data, $uid);

        return is_string($rs) ? $rs : [200, '处理成功', ['result' => $rs]];
    }

    /**
     * 修改用户密码
     * @time      2018-12-12 17:06:25
     * @api-put   string userid 要修改的用户id  当前用户id n
     * @api-put   string oldPass 当前密码  '' y
     * @api-put   string pass 新密码  '' y
     * @api-put   string pass 确认密码  '' y
     * @return array|string
     */
    public function PUTzEditPassword()
    {
        $oldPassword = z::postText('oldPass');
        $newPassword = z::postText('pass');
        $userid      = (int)$this->getInfo('id');
        $upUid       = (int)(z::postText('userid') ?: $userid);
        if ($userid === $upUid) {
            $rs = $this->UserBusiness->editPassword($upUid, $oldPassword, $newPassword, $userid);
        } else {
            $rs = '不能修改其他人密码';
        }

        return is_string($rs) ? $rs : [200, '修改密码成功', $rs];
    }

    /**
     * 上传用户头像
     * @time      2018-11-8 18:56:59
     * @api-param raw file 文件域 POST null y 文件大小不能超过1024kb
     * @return array
     */
    public function POSTzUploadAvatar(): array
    {
        $AssistBusiness = new AssistBusiness();
        $result         = $AssistBusiness->uploadAvatar();
        if (is_string($result)) {
            return [211, $result];
        }

        return [200, '上传成功', ['path' => $result['url'], 'host' => z::host()]];
    }

    /**
     * 清除用户Token
     * @time       2018-11-7 17:57:31
     * @api-ignore 忽略权限控制
     * @return array
     */
    public function POSTzClearToken(): array
    {
        $token  = $this->getToken();
        $result = $this->UserBusiness->clearToken($token);

        return [200, '清除用户Token', $result];
    }
}
