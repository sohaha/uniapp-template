<?php
declare (strict_types = 1);

namespace Business\Wx;

use Z;
use Zls\WeChat\Main;

class WxBusiness extends \Zls_Business

{
    /** @var Main */
    protected $wx;

    public function __construct()
    {
        $this->wx = new Main();
    }

    /**
     * @return Main
     */
    public function wxInstance()
    {
        return $this->wx;
    }

    /**
     * 获取 AccessToken
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->wx->getAccessToken() ?: '';
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function error(): string
    {
        return $this->wx->getError()['msg'];
    }

    /**
     * 加密用户信息为 Token
     *
     * @param string $sessionKey
     * @param string $openid
     * @param string $unionid
     *
     * @return string
     */
    public function encryptToken(string $sessionKey, string $openid, string $unionid): string
    {
        return Z::encrypt($sessionKey . '|' . $openid . '|' . $unionid);
    }

    /**
     * 解密 Token 为用户信息
     *
     * @param string $token
     *
     * @return array
     */
    public function decryptToken(string $token): array
    {
        $info = [
            'session_key' => '',
            'id' => 0,
        ];
        if (!$data = Z::decrypt($token)) {
            return $info;
        }
        $data = explode('|', $data);
        return [
            'session_key' => Z::arrayGet($data, 0, ''),
            'id' => (int) Z::arrayGet($data, 1, 0),
        ];
    }
}
