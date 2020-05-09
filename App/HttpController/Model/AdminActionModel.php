<?php


    namespace App\HttpController\Model;

    use EasySwoole\ORM\Utility\Schema\Table;

    class AdminActionModel extends \EasySwoole\ORM\AbstractModel
    {
        protected $tableName = 'admin_action';

        public function schemaInfo(bool $isCache = true): Table
        {
            $table = new Table($this->tableName);
            $table->colInt('action_id')->setIsPrimaryKey(true)->setIsAutoIncrement()->setIsUnique()
                ->setColumnComment('操作权限ID');
            $table->colInt('menu_id')->setIsNotNull()
                ->setColumnComment('所属菜单ID');
            $table->colVarChar('action_name',50)->setColumnComment('权限名称（备注用的,不调用）');
            $table->colVarChar('action_code', 40)->setIsNotNull()->setIsUnique()
                ->setColumnComment('权限代码,翻译用');
            return $table;
        }
    }