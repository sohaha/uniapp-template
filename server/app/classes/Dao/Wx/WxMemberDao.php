<?php
declare(strict_types=1);

namespace Dao\Wx;

use Util\DaoUtil;
use Z;

class WxMemberDao extends \Zls_Dao
{
    use DaoUtil;
    const STATUS_BAN = 2;
    const STATUS_NORMAL = 1;

    public function getColumns(): array
    {
        return [
            'id',//id
            'raw_data',//原始数据
            'nickname',//用户昵称
            'openid',//用户openid
            'unionid',//用户unionid
            'session_key',//用户session_key
            'avatar',//头像
            'status',//状态:-1软删除,0待激活,1正常,2禁止
            'create_time',//创建时间
            'update_time',//更新时间
        ];
    }

    public function getHideColumns(): array
    {
        return [
            'raw_data',
            'session_key',
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }

    public function getTable(): string
    {
        return 'wx_member';
    }

    public function saveSessionKey(array $data): int
    {
        $data   = z::readData(['session_key', 'openid'], $data, false);
        $openid = $data['openid'] ?? '';
        $info   = $this->openidToInfo($openid);
        if ($info) {
            $id = (int)$info['id'];

            // 更新sessionKey
            return $this->update($data, ['id' => $id]) ? $id : 0;
        }

        // 添加新sessionKey
        return $this->insert($data) ? (int)$this->getDb()->lastId() : 0;
    }

    public function openidToInfo(string $sessionKey): array
    {
        return $this->find(['openid' => $sessionKey], false);
    }

    public function verificationSessionKey(int $id, string $sessionKey): array
    {
        return $this->find(['session_key' => $sessionKey], false);
    }

    public function saveUserInfo(int $id, array $info)
    {
        $dataKeys = [];
        foreach ($this->getReversalColumns() as $k) {
            switch ($k) {
                case 'avatar':
                    $dataKeys['avatarurl'] = $k;
                    break;
                default:
                    $dataKeys[Z::strSnake2Camel($k, false)] = $k;
            }
        }
        $data        = z::readData($dataKeys, $info, false);
        $dataKeysArr = array_keys($dataKeys);
        /** @noinspection PhpComposerExtensionStubsInspection */
        $rawData = @json_encode(Z::arrayFilter($info, function ($_, $k) use ($dataKeysArr) {

            return !in_array($k, $dataKeysArr, true);
        }), JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);

        return $this->update($data + ['raw_data' => $rawData], ['id' => $id]) ? Z::tap($data, function (&$v) {
            unset($v['openid']);
        }) : [];
    }
}
