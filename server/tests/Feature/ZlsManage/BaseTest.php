<?php

namespace Feature\ZlsManage;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Z;
use Zls\Unit\Utils;

class BaseTest extends BaseTestCase
{
    use Utils;

    protected $username = 'admin';
    protected $password = 'admin';
    protected static $TOKEN = '';
    protected static $USER = [];

    public function setUp(): void
    {
        if (is_string(self::$USER)) {
            $this->fail(self::$USER);
        } elseif (!self::$USER) {
            // depends
            echo $this->command('migration r -t 0');
            echo $this->command('migration m');
            $this->command('manage passwd -u ' . $this->username . ' -p ' . $this->password);
            $this->postJSON('/ZlsManage/UserApi/GetToken.go', [
                'user' => $this->username,
                'pass' => $this->password,
            ]);
            $arr  = $this->jsonToArr();
            $code = Z::arrayGet($arr, 'code');
            if ($code !== 200) {
                self::$USER = '登录获取 TOKEN 失败: ' . Z::arrayGet($arr, 'msg', '登录失败');
                $this->fail(self::$USER);
            }
            $this->log('登录获取 TOKEN 成功');
            $this->assertEquals(200, $code);
            self::$USER  = Z::arrayGet($arr, 'data');
            self::$TOKEN = Z::arrayGet($arr, 'data.token');
        }
    }

    public function testToken(): void
    {
        $this->assertEquals(true, (bool)$this->header());
    }

    public function header($headers = []): array
    {
        return $headers + ['token:' . self::$TOKEN];
    }
}
