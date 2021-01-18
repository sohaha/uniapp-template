<?php
declare(strict_types=1);

namespace Controller;

use Business\Wx\WeappBusiness;
use Dao\Wx\WxMemberDao;
use Z;
use Zls\Action\FileUp;

/**
 * WxApi 接口
 */
class WeAppApi extends ZlsApi
{
    /**
     * @var WeappBusiness
     */
    private $wx;
    /**
     * @var array
     */
    private $userinfo = [];

    public function before($method, $controllerShort, $args, $controller)
    {
        $router = str_replace('_', '/', z::strCamel2Snake(str_replace('\\', '/', $controllerShort) . '/' . $method, ''));
        $token = $this->getToken();
        $ignore = ['weappapi/gettoken', 'weappapi/getwebtoken'];
        $this->wx = new WeappBusiness();
        if (!in_array($router, $ignore, true)) {
            $userinfo = $this->wx->decryptToken($token);
            if (!$userinfo['session_key']) {
                return [401, '请先登录'];
            }

            /** @var WxMemberDao $wxMemberDao */
            $wxMemberDao = Z::dao('Wx\WxMemberDao', true);
            // 小程序不能多地登录
            if (!$member = $wxMemberDao->verificationSessionKey($userinfo['id'], $userinfo['session_key'])) {
                return [401, '登录已过期，请重新登录'];
            }

            $this->userinfo = array_merge($userinfo, $member);
            $status = (int)Z::arrayGet($this->userinfo, 'status');
            if ($status === $wxMemberDao::STATUS_BAN) {
                return [402, '账号已被禁止'];
            }
        }

        return null;
    }

    /**
     * 获取会话 Token
     * @return array
     */
    public function POSTzGetToken()
    {
        $code = z::postJson('code', '');
        $info = $this->wx->instance()->getApp()->getSessionKey($code);
        if (is_string($info)) {
            return [211, $info];
        } elseif (!$info) {
            return [212, $this->wx->error()];
        }

        /** @var WxMemberDao $wxMemberDao */
        $wxMemberDao = Z::dao('Wx\WxMemberDao', true);
        $id = $wxMemberDao->saveSessionKey($info);
        $sessionKey = $info['session_key'];
        $token = Z::encrypt($sessionKey . '|' . $id);

        return [200, '获取成功', ['token' => $token]];
    }

    /**
     * 获取 Web端 会话 Token
     * @api-time 2019-12-18 17:51:13
     * @return array
     */
    public function POSTzGetWebToken()
    {
        $token = Z::encrypt(2 . '|' . 1);

        return [200, '获取成功', ['token' => $token]];
    }

    /**
     * 解密用户信息
     */
    public function POSTzGetUserInfo()
    {
        $iv = z::postJson('iv', '');
        $encryptedData = z::postJson('encryptedData', '');
        $info = $this->wx->decrypt($iv, $encryptedData, $this->userinfo['session_key']);
        if (!$info) {
            $err = $this->wx->instance()->getError();
            if ($err['code'] === -41004) {
                // 数据解密失败一般是session过期了
                return [401, $err['msg'], $err];
            }

            return [211, $err['msg'], $err];
        }
        /** @var WxMemberDao $wxMemberDao */
        $wxMemberDao = Z::dao('Wx\WxMemberDao', true);
        $info = array_change_key_case($info, CASE_LOWER);
        $res = $wxMemberDao->saveUserInfo($this->userinfo['id'], $info);
        // 过滤敏感信息
        unset($info['openid'], $info['watermark'], $info['unionid']);

        return $res ? [200, '保存用户信息完成', $info] : [211, '保存用户信息失败', $info];
    }

    /**
     * 用户信息
     * @return string|array
     */
    public function zInfo()
    {
        $info = $this->userinfo;
        $info = Z::readData(array_filter(array_keys($info), function ($v) {
            // 过滤敏感信息
            return !in_array($v, ['session_key', 'raw_data', 'id'], true);
        }), $info, false);

        return [200, '用户信息', $info];
    }

    /**
     * 更新用户信息
     * @api-time 2019-12-17 16:24:12
     * @return array
     */
    public function POSTzUpdate()
    {
        $info = Z::postJson();
        /** @var WxMemberDao $wxMemberDao */
        $wxMemberDao = Z::dao('Wx\WxMemberDao', true);
        $info = array_change_key_case($info, CASE_LOWER);
        $res = $wxMemberDao->saveUserInfo($this->userinfo['id'], $info);

        return $res ? [200, '更新用户信息成功', $info] : [201, '更新用户信息失败', $info];
    }

    /**
     * 上传用户头像
     * @return array
     */
    public function POSTzUploadAvatar()
    {
        /** @var FileUp $fileUpload */
        $fileUpload = z::extension('Action\FileUp');
        $fileUpload->setFormField('file');
        $fileUpload->setMaxSize(2048);
        $fileUpload->setExt(['jpg', 'png', 'jpeg', 'gif']);
        $name = md5((string)$this->userinfo['id']);
        $dir = Z::realPathMkdir("static/wx/avatar", false);
        $path = $fileUpload->saveFile($name, $dir);
        // z::post('nickname'); // 接收额外的 form data
        if (!$path) {
            return z::arrayGet($fileUpload->getError(), 'error');
        }
        if (is_array($path)) {
            $path = $path[0];
        }

        return [200, '', ['url' => Z::host() . Z::safePath($path, '', true)]];
    }
}
