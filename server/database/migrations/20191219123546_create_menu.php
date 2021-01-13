<?php

use Zls\Migration\AbstractMigration as M;
use Zls\Migration\MysqlAdapter;

class CreateMenu extends M
{
    /**
     * Change Method.
     * Write your reversible migrations using this method.
     * More information on writing migrations is available here:
     * https://docs.73zls.com/zls-php/#/packages-migration/migration
     * The following commands can be used in this method
     * automatically reverse them when rolling back:
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('menu');
        $table->comment('菜单表');
        $table->addColumn('title', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_COMMENT => '菜单名称']);
        $table->addColumn('index', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_COMMENT => '路由']);
        $table->addColumn('icon', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_COMMENT => '图标']);
        $table->addColumn('breadcrumb', self::TYPE_INTEGER, [self::OPTIONS_DEFAULT => 0, 'limit' => MysqlAdapter::INT_TINY, self::OPTIONS_COMMENT => '面包屑显示:0,1']);
        $table->addColumn('real', self::TYPE_INTEGER, [self::OPTIONS_DEFAULT => 0, 'limit' => MysqlAdapter::INT_TINY, self::OPTIONS_COMMENT => '面包屑可点击:0,1']);
        $table->addColumn('show', self::TYPE_INTEGER, [self::OPTIONS_DEFAULT => 0, 'limit' => MysqlAdapter::INT_TINY, self::OPTIONS_COMMENT => '导航栏显示:0,1']);
        $table->addColumn('pid', self::TYPE_INTEGER, [self::OPTIONS_DEFAULT => 0, 'limit' => MysqlAdapter::INT_SMALL, self::OPTIONS_COMMENT => '父id']);
        $table->addColumn('sort', self::TYPE_INTEGER, [self::OPTIONS_DEFAULT => 0, 'limit' => MysqlAdapter::INT_SMALL, self::OPTIONS_COMMENT => '排序id']);
        $table->addColumn('create_time', self::TYPE_DATETIME, [self::OPTIONS_DEFAULT => null, self::OPTIONS_NULL => true, self::OPTIONS_COMMENT => '创建时间']);
        $table->addColumn('update_time', self::TYPE_DATETIME, [self::OPTIONS_DEFAULT => null, self::OPTIONS_NULL => true, self::OPTIONS_COMMENT => '更新时间']);
        $table->create();
        $time     = date('Y-m-d H:i:s');
        $menu = [
            [
                // 'id'         => 1,
                'title'      => '后台中心',
                'index'      => 'main',
                'icon'       => 'icon-pie-chart-',
                'breadcrumb' => 1,
                'real'       => 1,
                'show'       => 1,
                'child'      => [
                    ['pid' => 1,'title' => '站内消息', 'index' => 'user/logs', 'icon' => 'icon-settings- ','breadcrumb' => 0,'real' => 0,'show' => 0],
                    ['pid' => 1,'title' => '多端登录', 'index' => 'user/client', 'icon' => 'icon-globe--outline ','breadcrumb' => 0,'real' => 0,'show' => 0]
                ],
            ],
            [
                // 'id'         => 4,
                'title'      => '日志查看',
                'index'      => 'system/logs',
                'icon'       => 'icon-alert-circle',
                'breadcrumb' => 0,
                'real'       => 0,
                'show'       => 1,
            ],
            [
                // 'id'         => 5,
                'title'      => '系统设置',
                'index'      => 'system',
                'icon'       => 'icon-options-',
                'breadcrumb' => 0,
                'real'       => 0,
                'show'       => 1,
                'child'      => [
                    ['pid' => 5,'title' => '程序设置', 'index' => 'system/config', 'icon' => 'icon-settings','breadcrumb' => 1,'real' => 0,'show' => 1],
                    ['pid' => 5,'title' => '用户设置', 'index' => 'user/lists', 'icon' => 'icon-person','breadcrumb' => 1,'real' => 0,'show' => 1],
                    ['pid' => 5,'title' => '角色设置', 'index' => 'user/group', 'icon' => 'icon-people','breadcrumb' => 1,'real' => 0,'show' => 1],
                    ['pid' => 5,'title' => '菜单设置', 'index' => 'user/menu', 'icon' => 'icon-pricetags','breadcrumb' => 1,'real' => 0,'show' => 1],
                    ['pid' => 5,'title' => '个人设置', 'index' => 'user/my', 'icon' => 'icon-person-done','breadcrumb' => 1,'real' => 0,'show' => 1],
                    ['pid' => 5,'title' => '权限设置', 'index' => 'user/rules', 'icon' => 'icon-pantone','breadcrumb' => 1,'real' => 0,'show' => 1]
                ],
            ]
        ];
        $count = 0;
        foreach ($menu as $key => $value){
            $count++;
            // $temp['id']      = $value['id'];
            $temp['title']      = $value['title'];
            $temp['index']      =$value['index'];
            $temp['icon']       =$value['icon'];
            $temp['breadcrumb'] =$value['breadcrumb'];
            $temp['real']       =$value['real'];
            $temp['show']       =$value['show'];
            $temp['sort']       =$count;
            $temp['create_time']=$time;
            $temp['update_time']=$time;
            $table->insert($temp);
            $table->saveData();
            if(isset($value['child'])){
                foreach ($value['child'] as $k => $v){
                    $count++;
                    // $cTemp['id']         = $v['id'];
                    $cTemp['title']      = $v['title'];
                    $cTemp['index']      =$v['index'];
                    $cTemp['icon']       =$v['icon'];
                    $cTemp['breadcrumb'] =$v['breadcrumb'];
                    $cTemp['real']       =$v['real'];
                    $cTemp['show']       =$v['show'];
                    $cTemp['pid']        =$v['pid'];
                    $cTemp['sort']       =$count;
                    $cTemp['create_time']=$time;
                    $cTemp['update_time']=$time;
                    $table->insert($cTemp);
                    $table->saveData();
                }
            }
        }
    }
}
