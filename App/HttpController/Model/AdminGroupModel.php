<?php


    namespace App\HttpController\Model;


    use EasySwoole\ORM\Utility\Schema\Table;

    class AdminGroupModel extends \EasySwoole\ORM\AbstractModel
    {
        protected $tableName = 'admin_group';

        public function schemaInfo(bool $isCache = true): Table
        {
            $table = new Table($this->tableName);
            $table->colInt('group_id')->setIsPrimaryKey(true)->setIsAutoIncrement()
                ->setColumnComment('后台管理组ID');
            $table->colVarChar('group_code', 30)->setIsNotNull()->setIsUnique()
                ->setColumnComment('后台管理组编码');
            $table->colVarChar('group_name', 60)->setIsNotNull()
                ->setColumnComment('管理组备注用的名字');
            $table->colText('action')->setIsNotNull()
                ->setColumnComment('权限合集,填写code');
            $table->colText('menu')->setIsNotNull()
                ->setColumnComment('菜单合集,填写menu_id');
            return $table;
        }

    }