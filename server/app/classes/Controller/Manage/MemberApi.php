<?php
declare(strict_types=1);

namespace Controller\Manage;

use Controller\ZlsManage;
use Dao\Wx\WxMemberDao;
use Z;

/**
 * 后台-会员接口
 * @author        影浅 seekwe@gmail.com
 * @desc          需要传递 header token（GetToken除外）
 */
class MemberApi extends ZlsManage
{
    /**
     * 获取会员列表
     */
    public function GETzlist()
    {
        $page     = (int)Z::get('page', 1);
        $pagesize = (int)Z::get('pagesize', 10);
        $dao      = new WxMemberDao();

        return [200, '会员列表', $dao->getPage($page, $pagesize)];
    }

    /**
     * 更改微信用户状态
     * @api-post int id 用户id ''
     * @api-post int status 用户状态码 0 Y 范围：-1,0,1,2
     */
    public function POSTzBanWxUser()
    {
        if (!$id = (int)Z::postJson('id')) {
            return '用户ID不能为空';
        }
        $dao    = new WxMemberDao();
        $status = (int)Z::postJson('status');
        if (!Z::checkValue($status, ['enum[-1,0,1,2]'])) {
            return '用户状态不在合法范围';
        }
        $res = $dao->update(['status' => $status], ['id' => $id]);

        return [200, '更改微信用户状态', $res];
    }
}
