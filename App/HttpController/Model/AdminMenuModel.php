<?php


    namespace App\HttpController\Model;


    use EasySwoole\ORM\Utility\Schema\Table;

    class AdminMenuModel extends \EasySwoole\ORM\AbstractModel
    {
        protected $tableName = 'admin_menu';
        public function schemaInfo(bool $isCache = true): Table
        {
            $table = new Table($this->tableName);
            $table->colInt('menu_id')->setIsPrimaryKey(true)->setIsAutoIncrement()->setIsUnique()
                ->setColumnComment('菜单ID');
            $table->colInt('parent_id')->setIsNotNull()
                ->setColumnComment('所属菜单ID');
            $table->colVarChar('menu_name',50)->setColumnComment('菜单名称（备注用的,不调用）');
            $table->colVarChar('menu_code', 40)->setIsNotNull()->setIsUnique()
                ->setColumnComment('菜单代码,翻译用');
            $table->colVarChar('icon',50)->setDefaultValue('fa fa-bars')->setColumnComment('菜单图标');
            $table->colVarChar('href',100)->setColumnComment('菜单链接');
            $table->colVarChar('target',40)->setDefaultValue('_self')->setColumnComment('链接跳转方式');
            $table->colInt('level',10)->setDefaultValue(1)->setColumnComment('菜单层级');
            return $table;
        }
    }