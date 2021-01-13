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
     * 查看用户日志
     * @time       2018-12-13 19:15:42
     * @api-get    int page 页码  1 n
     * @api-get    int pagesize 条数  10
     * @api-get    int unread 是否只查询未读  0 N 1只查询未读
     * @return array
     */
    public function GETzLogs(): array
    {
        $assist = new AssistBusiness();
        $type   = Z::get('type');
        $unread = z::get('unread', 0);
        $userid = $this->getInfo('id');

        return [200, '用户日志', $assist->getLogs(Z::get('page', 1), z::get('pagesize', 10), $unread, $type, $userid)];
    }

    /**
     * 未读日志总数
     * @desc        建议轮询获取未读日志
     * @api-get     int id 上一条消息ID 0 N 只返回上条到最新的消息
     * @api-return  JSON {"code":200, "msg":"未读日志"}
     * @api-return  int code 状态码 200：成功
     * @return array
     */
    public function zUnreadMessageCount(): array
    {
        $assist = new AssistBusiness();
        $lastId = z::getPost('id', 0);
        $userid = $this->getInfo('id');

        // 可以在这里做token时间更新
        return [200, '未读日志', ['count' => $assist->getUnreadMessageCount($lastId, $userid)]];
    }

    /**
     * 更新日志状态
     * @return array
     */
    public function PUTzMessageStatus(): array
    {
        $assist = new AssistBusiness();
        $uid    = $this->getInfo('id');
        $ids    = z::postText('ids');

        return [200, '日志标记已读', $assist->updateMessageStatus($ids, $uid)];
    }

    /**
     * 用户详情
     * @time       2018-11-8 17:57:48
     * @api-return array "data.marks" 权限标识列表
     * @api-return array "data.menus" 用户菜单
     * @api-return array "data.system" 系统信息 没有'systems'权限不可见
     */
    public function GETzUseriInfo(): array
    {
        $show = ['id', 'group_id', 'avatar', 'email', 'nickname', 'username', 'status', 'marks', 'system', 'last', 'groups', 'menus', 'menu'];
        $user = Z::arrayFilter($this->getInfo(), static function ($v, $k) use ($show) {
            return in_array($k, $show, true);
        });
        // todo 注意控制某些用户不显示系统信息
        if ($this->hasPermission('systems')) {
            $user['system'] = (new AssistBusiness())->getSystemInfo();
            $user['isSuper'] = $user['is_super'] = $this->getInfo('isSuper');
        }
        $user['last'] = $this->UserBusiness->getLast($user['id'], $this->TOKENID);

        $group2Arr        = function ($groupID) {
            $temp = explode(',', $groupID);
            $re   = [];

            foreach ($temp as $v) {
                $re[] = (int)$v;
            }

            return $re;
        };
        $user['group_id'] = $group2Arr($user['group_id']);


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
        if ($this->UserBusiness->isBusyLogin()) {
            return [212, '登录过于频繁，请稍后再试'];
        }
        $user   = z::postGet('user');
        $pass   = z::postGet('pass');
        $user   = $this->UserBusiness->nameToInfo($user);
        $status = $this->UserBusiness->checkPass($user, $pass);
        if (is_string($status)) {
            return [211, $status];
        }
        $user          = $this->UserBusiness->filterField($user);
        $user['token'] = $this->UserBusiness->createToken($user['id']);
        // 新登录之后是否清除该用户的其他端登录状态？
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
        $ok = false;
        // 被修改的用户是否超级管理员
        $userIsSuper = $this->UserBusiness->isSuperAdminById((int)$userid);
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
        if ($isSuper && Z::arrayGet($post, 'password')) {
            $map[] = 'password';
            $map[] = 'password2';
            Z::defer(function ()use (&$ok, $userid){
                if($ok){
                    // 注销该用户的 Token
                    $this->UserBusiness->clearAllToken($userid,[$this->TOKENID]);
                }
            });
        }
        if ($isSuper && !$isMe) {
            $map[] = 'group_id';
        }
        $data = z::readData($map, $post);
        $rs   = $this->UserBusiness->update($userid, $data, $uid);
        $ok   = !is_string($rs) && !!$rs;
        return !$ok ? $rs : [200, '处理成功', ['result' => $rs]];
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
        $ok = false;
        if ($userid === $upUid) {
            $rs = $this->UserBusiness->editPassword($upUid, $oldPassword, $newPassword, $userid);
            Z::defer(function () use (&$ok, $upUid) {
                if ($ok) {
                    // 注销该用户的 Token
                    $this->UserBusiness->clearAllToken($upUid, [$this->TOKENID]);
                }
            });
        } else {
            $rs = '不能修改其他人密码';
        }
        $ok = !is_string($rs);
        return $ok ? [200, '修改密码成功', $rs] : $rs;
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
     * @return array
     */
    public function POSTzClearToken(): array
    {
        $result = $this->UserBusiness->clearToken($this->TOKENID);

        return [200, '清除用户Token', $result];
    }
}
