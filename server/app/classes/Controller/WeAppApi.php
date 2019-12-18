<?php
declare(strict_types=1);

namespace Controller;

use Business\Wx\WeappBusiness;
use Dao\Wx\WxMemberDao;
use Z;

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
        $router   = str_replace('_', '/', z::strCamel2Snake(str_replace('\\', '/', $controllerShort) . '/' . $method, ''));
        $token    = $this->getToken();
        $ignore   = ['weappapi/gettoken', 'weappapi/getwebtoken'];
        $this->wx = new WeappBusiness();
        if (!in_array($router, $ignore, true)) {
            $userinfo = $this->wx->decryptToken($token);
            if (!$userinfo['session_key']) {
                return [401, '请先登录'];
            }
            /** @var WxMemberDao $wxMemberDao */
            $wxMemberDao = Z::dao('Wx\WxMemberDao', true);
            // 小程序只能单点登录
            if (!$member = $wxMemberDao->verificationSessionKey($userinfo['id'], $userinfo['session_key'])) {
                return [401, '登录已过期，请重新登录'];
            }
            $this->userinfo = array_merge($userinfo, $member);
            $status         = (int)Z::arrayGet($this->userinfo, 'status');
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
        $code = z::post('code', '');
        $info = $this->wx->instance()->getApp()->getSessionKey($code);
        if (is_string($info)) {
            return [211, $info];
        } elseif (!$info) {
            return [212, $this->wx->error()];
        }
        /** @var WxMemberDao $wxMemberDao */
        $wxMemberDao = Z::dao('Wx\WxMemberDao', true);
        $id          = $wxMemberDao->saveSessionKey($info);
        $sessionKey  = $info['session_key'];
        $token       = Z::encrypt($sessionKey . '|' . $id);

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
        $iv            = z::post('iv', '');
        $encryptedData = z::post('encryptedData', '');
        $info          = $this->wx->decrypt($iv, $encryptedData, $this->userinfo['session_key']);
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
        $info        = array_change_key_case($info, CASE_LOWER);
        $res         = $wxMemberDao->saveUserInfo($this->userinfo['id'], $info);
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
        $info = Z::post();
        /** @var WxMemberDao $wxMemberDao */
        $wxMemberDao = Z::dao('Wx\WxMemberDao', true);
        $info        = array_change_key_case($info, CASE_LOWER);
        $res         = $wxMemberDao->saveUserInfo($this->userinfo['id'], $info);

        return $res ? [200, '更新用户信息成功', $info] : [201, '更新用户信息失败', $info];
    }

    /**
     * 获取token
     * @return string
     */
    final protected function getToken(): string
    {
        return z::server('HTTP_TOKEN') ?: z::getPost('token', z::server('HTTP_X_UPLOAD_TOKEN', ''));
    }

    /**
     * 控制器返回数组时，直接输出 json 数据
     *
     * @param $contents
     * @param $methodName
     * @param $controllerShort
     * @param $args
     * @param $controller
     *
     * @return mixed|string
     */
    public function after($contents, $methodName, $controllerShort, $args, $controller)
    {
        switch (true) {
            case is_array($contents):
                return Z::json($contents);
            case is_string($contents):
                return Z::json(211, $contents);
            case null:
                return '';
            default:
                return $contents;
        }
    }
}
