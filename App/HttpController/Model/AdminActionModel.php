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
            $table->colInt('parent_id')->setIsNotNull()
                ->setColumnComment('所属模块ID,如果为0则为模块');
            $table->colVarChar('action_code', 40)->setIsNotNull()->setIsUnique()
                ->setColumnComment('权限代码');
            return $table;
        }
    }