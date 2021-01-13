<?php

use Zls\Migration\AbstractMigration as M;
use Zls\Migration\MysqlAdapter;

class CreateGroupMenu extends M
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
        $table = $this->table('auth_group_menu');
        $table->comment('角色菜单对应表');
        $table->addColumn('groupid', self::TYPE_INTEGER, [self::OPTIONS_DEFAULT => 0, 'limit' => MysqlAdapter::INT_TINY, 'comment' => '角色Id']);
        $table->addColumn('menu', self::TYPE_STRING, [self::OPTIONS_DEFAULT => '', self::OPTIONS_COMMENT => '菜单']);
        $table->addColumn('create_time', self::TYPE_DATETIME, [self::OPTIONS_DEFAULT => null, self::OPTIONS_NULL => true, self::OPTIONS_COMMENT => '创建时间']);
        $table->addColumn('update_time', self::TYPE_DATETIME, [self::OPTIONS_DEFAULT => null, self::OPTIONS_NULL => true, self::OPTIONS_COMMENT => '更新时间']);
        $table->create();

        $rows = [
            [
                'groupid'        => '1',
                'menu'      => '1,2,3,4,5,6,7,8,9,10,11',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ],
            [
                'groupid'        => '2',
                'menu'      => '1',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ],
        ];
        $table->insert($rows);
        $table->saveData();
    }
}
