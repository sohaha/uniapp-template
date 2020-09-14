<?php
/*
 * @Author: seekwe
 * @Date: 2020-07-29 16:54:03
 * @Last Modified by:: seekwe
 * @Last Modified time: 2020-07-30 17:04:51
 */
declare(strict_types=1);

namespace Business\Wx;

use Z;
use Zls\WeChat\Main;

class WeappBusiness extends WxBusiness
{
    public function __construct()
    {
        parent::__construct();
        $this->wx->init(z::config("weapp"));
    }

    /**
     * 获取用户 OPENID
     *
     * @param string $code
     *
     * @return array|string
     */
    public function jscode2session(string $code)
    {
        $appid = $this->wx->getAppid();
        $scope = $this->wx->getAppsecret();

        return z::tap($this->wx->getApp()->getSessionKey($code, $appid, $scope), function (&$v) {
            if (!$v) {
                $v = $this->error();
            }
        });
    }

    /**
     * @param string $iv
     * @param string $encryptedData
     * @param string $sessionKey
     *
     * @return array|bool|string
     */
    public function decrypt(string $iv, string $encryptedData,string $sessionKey)
    {
        $this->wx->getApp()->setSessionKey($sessionKey);
        return $this->wx->getApp()->decrypt($iv, $encryptedData);
    }
}
