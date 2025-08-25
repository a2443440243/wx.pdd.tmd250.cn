<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateIndexUserTable extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('index_user', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
            'comment' => '前端用户表'
        ]);
        
        $table->addColumn('phone', 'string', [
                'limit' => 11,
                'comment' => '手机号码'
            ])
            ->addColumn('password', 'string', [
                'limit' => 255,
                'comment' => '登录密码'
            ])
            ->addColumn('nickname', 'string', [
                'limit' => 50,
                'null' => true,
                'comment' => '用户昵称'
            ])
            ->addColumn('avatar', 'string', [
                'limit' => 500,
                'null' => true,
                'comment' => '用户头像'
            ])
            ->addColumn('gender', 'integer', [
                'limit' => 1,
                'default' => 0,
                'comment' => '性别(0:未知,1:男,2:女)'
            ])
            ->addColumn('birthday', 'date', [
                'null' => true,
                'comment' => '生日'
            ])
            ->addColumn('address', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => '地址'
            ])
            ->addColumn('login_time', 'datetime', [
                'null' => true,
                'comment' => '最后登录时间'
            ])
            ->addColumn('login_ip', 'string', [
                'limit' => 45,
                'null' => true,
                'comment' => '最后登录IP'
            ])
            ->addColumn('remember_token', 'string', [
                'limit' => 100,
                'null' => true,
                'comment' => '记住登录令牌'
            ])
            ->addColumn('status', 'integer', [
                'limit' => 1,
                'default' => 1,
                'comment' => '状态(0:禁用,1:启用)'
            ])
            ->addColumn('deleted', 'integer', [
                'limit' => 1,
                'default' => 0,
                'comment' => '删除状态(0:正常,1:删除)'
            ])
            ->addColumn('create_time', 'datetime', [
                'comment' => '创建时间'
            ])
            ->addColumn('update_time', 'datetime', [
                'comment' => '更新时间'
            ])
            ->addIndex(['phone'], ['unique' => true, 'name' => 'idx_phone'])
            ->addIndex(['status', 'deleted'], ['name' => 'idx_status_deleted'])
            ->addIndex(['create_time'], ['name' => 'idx_create_time'])
            ->addIndex(['remember_token'], ['name' => 'idx_remember_token'])
            ->create();
            
        // 插入测试用户数据
        $this->execute("INSERT INTO `index_user` (`phone`, `password`, `nickname`, `gender`, `status`, `deleted`, `create_time`, `update_time`) VALUES ('13800138000', '" . password_hash('123456', PASSWORD_DEFAULT) . "', '测试用户', 1, 1, 0, NOW(), NOW())");
    }
}