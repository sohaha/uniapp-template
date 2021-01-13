<?php
declare (strict_types=1);

namespace Bean\ZlsManage;

use Business\ZlsManage\GroupBusiness;
use Business\ZlsManage\UserBusiness;
use Z;

class UserBean extends \Zls_Bean
{
    protected $id = 0;
    protected $group_id = 0;
    protected $status = 0;

    public function getGroups(): array
    {
        $groupids = Z::arrayMap(explode(',', $this->group_id), static function ($v) {
            return (int)$v;
        });
        $groups   = (new GroupBusiness())->all();
        $names    = [];
        foreach ($groups as $group) {
            if (in_array($group['id'], $groupids, true)) {
                $names[$group['id']] = $group['name'];
            }
        }
        /** @var UserBusiness $UserBusiness */
        $UserBusiness = Z::business('ZlsManage\UserBusiness');
        if ($UserBusiness->isSuperAdminById((int)$this->id)) {
            $names[0] = '超级管理员';
        }

        return $names;
    }

    public function getStatus(): int
    {
        return (int)$this->status;
    }

    public function getGroupId(): array
    {
        $group2Arr = function ($groupID) {
            $temp = explode(',', $groupID);
            $re   = [];

            foreach ($temp as $v) {
                $re[] = (int)$v;
            }

            return $re;
        };

        return $group2Arr($this->group_id);
    }
}
