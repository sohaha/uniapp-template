<?php

declare(strict_types=1);

namespace Business\ZlsManage;

use Z;

/**
 * 权限业务
 * Class PermissionsBusiness
 * @package Business
 */
class PermissionsBusiness extends \Zls_Business
{
    /**
     * 验证角色是否有权限
     *
     * @param $permissions
     * @param $groupid
     *
     * @return bool
     */
    public function has(string $permissions, int $groupid): bool
    {
        $marks = (new GroupBusiness())->getMarks([$groupid]);

        return in_array($permissions, $marks, true);
    }
}
