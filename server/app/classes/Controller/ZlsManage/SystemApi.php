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
     * 查看用户日志
     * @time       2018-12-13 19:15:42
     * @api-ignore 忽略权限控制
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
     * @api-ignore  忽略权限控制
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
        $name = z::postText('name');
        $type = z::postText('type');
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
        return [200, '更新系统配置', (new AssistBusiness)->updateSystemConfig(z::postText())];
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
