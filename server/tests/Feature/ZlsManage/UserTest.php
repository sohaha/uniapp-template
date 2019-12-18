<?php

namespace Feature\ZlsManage;

use Z;
use Zls\Action\StrUtils;

class UserTest extends BaseTest
{
    /**
     * 获取用户信息
     */
    public function testUseriInfo(): void
    {
        $this->getJSON('/ZlsManage/UserApi/UseriInfo.go', [], $this->header());
        $data = $this->jsonToArr();
        $this->assertEquals(Z::arrayGet($data, 'data.username'), Z::arrayGet(parent::$USER, 'username'));
    }

    /**
     * 更新用户信息
     */
    public function testUseriUpdate(): void
    {
        $nickname = (new StrUtils())->randString(5);
        $data     = [
            'id'       => parent::$USER['id'],
            'status'   => 1,
            'nickname' => $nickname,
        ];
        $this->putJSON('/ZlsManage/UserApi/Update.go', $data, $this->header());
        $json = $this->jsonToArr();
        $this->assertEquals(200, Z::arrayGet($json, 'code', 0), Z::arrayGet($json, 'msg', '请求失败'));
        $this->getJSON('/ZlsManage/UserApi/UseriInfo.go', [], $this->header());
        $json = $this->jsonToArr();
        $this->assertEquals($nickname, Z::arrayGet($json, 'data.nickname'), '昵称不匹配');
    }
}
