<?php

namespace Dao\ZlsManage;

use Z;

class AccessLogDao extends \Zls_Dao
{
    public function getColumns()
    {
        return [
            'id',//id
            'userid',//管理员Id
            'route',//路由
            'param',//Get数据
            'input',//Input数据
            'content',//返回数据
            'create_time',//创建时间
        ];
    }

    public function getHideColumns()
    {
        return [];
    }

    public function getPrimaryKey()
    {
        return 'id';
    }

    public function getTable()
    {
        return 'auth_user_access_log';
    }

}
