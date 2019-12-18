<?php
declare(strict_types=1);

namespace Dao\ZlsManage;

use Z;

class LogsDao extends \Zls_Dao
{
    public const TYPE_NORMAL = 1;
    public const TYPE_WARN = 2;
    public const TYPE_ERROR = 3;
    public const STATUS_NOT = 1;
    public const STATUS_READ = 2;

    public function getColumns(): array
    {
        return [
            'id'//id
            ,
            'userid'//对应用户Id
            ,
            'operate_id'//操作人Id，游客为0
            ,
            'content'//信息
            ,
            'create_time'//创建时间
            ,
            'update_time'//更新时间
            ,
            'type'//状态:1正常，2警告，3错误
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }

    public function getTable(): string
    {
        return 'auth_user_logs';
    }

    public function create($userid, $title, $content, $operateid = 0, $type = self::TYPE_NORMAL, $status = self::STATUS_NOT): int
    {
        $data = [
            'userid'      => $userid,
            'operate_id'  => $operateid,
            'type'        => $type,
            'status'      => $status,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s'),
            'content'     => $content,
            'title'       => $title,
        ];

        return (int)$this->insert($data);
    }

    public function createOperationLog($userid, $title, $content, $operateid = 0, $type = self::TYPE_NORMAL, $status = self::STATUS_NOT): int
    {
        $ip = Z::clientIp();
        $ua = Z::server('HTTP_USER_AGENT');
        if (!$title) {
            /** @noinspection PhpComposerExtensionStubsInspection */
            $title = mb_substr($content, 0, 50);
        }
        $content = "{$content}\nOperate IP: {$ip}\nUser Agent: {$ua}";

        return $this->create($userid, $title, $content, $operateid, $type, $status);
    }
}
