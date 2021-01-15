<?php
declare(strict_types=1);

namespace Controller\ZlsManage;

use Business\ZlsManage\AssistBusiness;
use Business\ZlsManage\GroupBusiness;
use Business\ZlsManage\SysLogBusiness;
use Z;

/**
 * 后台-系统接口
 * @desc          需要传递 header token（GetToken除外）
 */
class SystemApi extends UserApi
{
    /**
     * 查看系统日志文件
     * @time 2019-08-06 15:58:24
     * @api-permission("systems")
     * @return array
     */
    public function GETzSystemLogs(): array
    {
        $name        = z::getPost('name');
        $type        = z::getPost('type');
        $currentLine = z::getPost('currentLine', 0);
        $data        = (new SysLogBusiness)->exportData($type, $name, $currentLine);

        return [200, '系统日志', $data];
    }

    /**
     * 删除系统日志文件
     * @time 2019-08-06 15:58:21
     * @api-permission("systems")
     * @return array
     */
    public function DELETEzSystemLogs(): array
    {
        $name = z::postJson('name');
        $type = z::postJson('type');
        $data = $name && $type ? (new SysLogBusiness)->delete($type, $name) : false;

        return [200, '删除系统日志', $data];
    }

    /**
     * 读取系统配置
     * @time 2019-08-06 15:58:21
     * @api-permission("systems")
     */
    public function GETzSystemConfig(): array
    {
        return [200, '读取系统配置', (new AssistBusiness)->getSystemConfig()];
    }

    /**
     * 更新系统配置
     * @time 2019-08-06 15:58:21
     * @api-permission("systems")
     */
    public function PUTzSystemConfig(): array
    {
        $userID  = (int)z::arrayGet($this->USER, 'id', 0);
        $tokenID = $this->getTokenId();
        return [200, '更新系统配置', (new AssistBusiness)->updateSystemConfig(z::postJson(), $userID, $tokenID)];
    }

    /**
     * 获取系统菜单列表
     * @todo 未完成
     * @time 2019-08-06 15:58:21
     * @api-permission("systems")
     * @return array
     */
    public function zMenu(): array
    {
        $userid = $this->getInfo('id');

        return [200, '获取系统菜单列表', (new GroupBusiness())->getMenu()];
    }
}
